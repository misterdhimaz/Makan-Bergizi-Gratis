<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Enums\MenuStatus;
use App\Models\ComplaintHistory;
use App\Models\ComplaintTicket;
use App\Models\DailyMenu;
use App\Models\DeliveryRoute;
use App\Models\InventoryTransaction;
use App\Models\Location;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AslapService
{
    public function __construct(
        private readonly DeliveryMetrics $deliveryMetrics,
    ) {}

    public function activeComplaintsBadge(): int
    {
        return ComplaintTicket::query()
            ->whereNotIn('status', ['resolved', 'selesai'])
            ->count();
    }

    /**
     * Data lengkap Dashboard Asisten Lapangan dari database.
     *
     * @return array<string, mixed>
     */
    public function dashboardData(): array
    {
        $today = now('Asia/Jakarta');
        $todayStr = $today->toDateString();
        $metrics = $this->deliveryMetrics->forDate($today);

        $nextMenu = DailyMenu::query()
            ->whereDate('operational_date', '>', $todayStr)
            ->whereHas('inventoryTransactions', fn ($q) => $q->where('type', 'inbound_purchase'))
            ->orderBy('operational_date')
            ->first()
            ?? DailyMenu::query()->whereDate('operational_date', $todayStr)->first();

        $nextProcurements = $nextMenu
            ? InventoryTransaction::query()
                ->with('ingredient')
                ->where('daily_menu_id', $nextMenu->id)
                ->where('type', 'inbound_purchase')
                ->get()
            : collect();

        $procReceivedCount = $nextProcurements->where('procurement_status', 'diterima_lengkap')->count();
        $procTotalCount = $nextProcurements->count();

        $routesToday = DeliveryRoute::query()
            ->with(['location', 'confirmer'])
            ->whereDate('operational_date', $todayStr)
            ->orderBy('waybill_number')
            ->get();

        $activeComplaints = ComplaintTicket::query()
            ->with('location')
            ->whereNotIn('status', ['resolved', 'selesai'])
            ->orderByDesc('reported_at')
            ->get();

        $editableRoutes = $routesToday->where('status', DeliveryStatus::DISIAPKAN);
        $varianceLocations = $routesToday->filter(
            fn (DeliveryRoute $r): bool => $r->confirmed_portions !== null && $r->confirmed_portions !== $r->dispatched_portions
        );

        return [
            'today_label' => $today->translatedFormat('l, j M Y'),
            'metrics' => $metrics,
            'next_menu' => $nextMenu,
            'next_procurements' => $nextProcurements,
            'proc_received_count' => $procReceivedCount,
            'proc_total_count' => $procTotalCount,
            'routes_today' => $routesToday,
            'total_scheduled_portions' => (int) $routesToday->sum('dispatched_portions'),
            'active_complaints' => $activeComplaints,
            'editable_routes' => $editableRoutes,
            'variance_locations_count' => $varianceLocations->count(),
        ];
    }

    /**
     * Catat penerimaan bahan baku oleh Asisten Lapangan.
     */
    public function recordProcurement(
        InventoryTransaction $transaction,
        User $actor,
        float $quantity,
        ?string $supplierName,
        Carbon $receivedAt,
        ?string $notes = null,
    ): InventoryTransaction {
        if ($transaction->verification_status !== 'menunggu') {
            abort(409, 'Baris pengadaan sudah diverifikasi Ahli Gizi dan terkunci.');
        }

        return DB::transaction(function () use ($transaction, $actor, $quantity, $supplierName, $receivedAt, $notes) {
            $locked = InventoryTransaction::query()->whereKey($transaction->id)->lockForUpdate()->firstOrFail();

            if ($locked->verification_status !== 'menunggu') {
                abort(409, 'Baris pengadaan sudah diverifikasi Ahli Gizi dan terkunci.');
            }

            $planned = (float) $locked->planned_qty;
            $status = 'menunggu_terima';
            if ($quantity > 0 && $quantity < $planned) {
                $status = 'diterima_sebagian';
            } elseif ($quantity >= $planned && $planned > 0) {
                $status = 'diterima_lengkap';
            }

            $oldValues = [
                'quantity' => $locked->quantity,
                'procurement_status' => $locked->procurement_status,
            ];

            $locked->forceFill([
                'quantity' => $quantity,
                'supplier_name' => $supplierName ?: null,
                'procurement_status' => $status,
                'received_at' => $receivedAt,
                'notes' => $notes,
                'recorded_by' => $actor->id,
            ])->save();

            AuditLogger::record(
                action: 'Mencatat penerimaan bahan baku',
                module: 'Pengadaan & Bahan Baku',
                target: ($locked->ingredient?->name ?? 'Bahan').' ('.$locked->reference_no.')',
                oldValues: $oldValues,
                newValues: [
                    'quantity' => $quantity,
                    'procurement_status' => $status,
                    'supplier_name' => $supplierName,
                ],
                actor: $actor,
            );

            return $locked->fresh();
        });
    }

    /**
     * Ubah jadwal & armada rute berstatus Disiapkan dengan validasi tabrakan jam.
     */
    public function updateSchedule(DeliveryRoute $route, User $actor, array $data): DeliveryRoute
    {
        if ($route->status !== DeliveryStatus::DISIAPKAN) {
            abort(409, 'Rute yang sudah Berangkat atau lebih tidak dapat diubah.');
        }

        $dateStr = $route->operational_date?->toDateString() ?? now('Asia/Jakarta')->toDateString();
        $newStart = $data['planned_departure_time'];
        $newEnd = $data['scheduled_arrival_time'];

        $conflict = DeliveryRoute::query()
            ->whereDate('operational_date', $dateStr)
            ->where('id', '!=', $route->id)
            ->where(function ($q) use ($data) {
                $q->where('courier_name', $data['courier_name'])
                    ->orWhere('vehicle_plate', $data['vehicle_plate']);
            })
            ->get()
            ->first(function (DeliveryRoute $other) use ($newStart, $newEnd): bool {
                $otherStart = $other->planned_departure_time
                    ?? $other->departed_at?->timezone('Asia/Jakarta')->format('H:i');
                $otherEnd = $other->scheduled_arrival_time;

                if (! $otherStart || ! $otherEnd) {
                    return false;
                }

                // Overlap jika startA < endB && startB < endA
                return $newStart < $otherEnd && $otherStart < $newEnd;
            });

        if ($conflict) {
            throw ValidationException::withMessages([
                'planned_departure_time' => 'Petugas/kendaraan sudah dijadwalkan pada jam yang bertabrakan.',
            ]);
        }

        return DB::transaction(function () use ($route, $actor, $data) {
            $locked = DeliveryRoute::query()->whereKey($route->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== DeliveryStatus::DISIAPKAN) {
                abort(409, 'Rute yang sudah Berangkat atau lebih tidak dapat diubah.');
            }

            $oldValues = [
                'courier_name' => $locked->courier_name,
                'vehicle_plate' => $locked->vehicle_plate,
                'planned_departure_time' => $locked->planned_departure_time,
                'scheduled_arrival_time' => $locked->scheduled_arrival_time,
            ];

            $locked->forceFill([
                'kelompok_rute' => $data['kelompok_rute'] ?? $locked->kelompok_rute,
                'courier_name' => $data['courier_name'],
                'vehicle_plate' => $data['vehicle_plate'],
                'planned_departure_time' => $data['planned_departure_time'],
                'scheduled_arrival_time' => $data['scheduled_arrival_time'],
            ])->save();

            AuditLogger::record(
                action: 'Memperbarui jadwal distribusi',
                module: 'Jadwal Distribusi',
                target: $locked->waybill_number.' · '.$locked->location?->name,
                oldValues: $oldValues,
                newValues: [
                    'courier_name' => $locked->courier_name,
                    'vehicle_plate' => $locked->vehicle_plate,
                    'planned_departure_time' => $locked->planned_departure_time,
                    'scheduled_arrival_time' => $locked->scheduled_arrival_time,
                ],
                actor: $actor,
            );

            return $locked->fresh();
        });
    }

    /**
     * Buat jadwal pengiriman baru dari menu berstatus Disetujui.
     */
    public function createScheduleFromApprovedMenu(Carbon $date, User $actor): int
    {
        $menu = DailyMenu::query()
            ->whereDate('operational_date', $date->toDateString())
            ->where('status', MenuStatus::DISETUJUI->value)
            ->first();

        if (! $menu) {
            throw ValidationException::withMessages([
                'tanggal' => 'Tidak ada menu berstatus Disetujui untuk tanggal tersebut.',
            ]);
        }

        $locations = Location::query()->where('is_active', true)->orderBy('code')->get();
        $created = 0;
        $couriers = ['Pak Andi', 'Pak Budi', 'Pak Candra', 'Pak Gunawan', 'Pak Dedi', 'Pak Eko', 'Pak Fajar'];
        $plates = ['BG 8101 KL', 'BG 8104 KL', 'BG 8107 KL', 'BG 8123 KL', 'BG 8112 KL', 'BG 8115 KL', 'BG 8119 KL'];

        DB::transaction(function () use ($date, $locations, &$created, $couriers, $plates, $actor) {
            foreach ($locations as $idx => $loc) {
                $exists = DeliveryRoute::query()
                    ->whereDate('operational_date', $date->toDateString())
                    ->where('location_id', $loc->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $seq = str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT);
                DeliveryRoute::create([
                    'waybill_number' => 'DST-'.$date->format('Ymd').'-'.$seq,
                    'operational_date' => $date->toDateString(),
                    'kelompok_rute' => 'Rute '.($idx + 1),
                    'location_id' => $loc->id,
                    'status' => DeliveryStatus::DISIAPKAN,
                    'dispatched_portions' => $loc->target_portions,
                    'courier_name' => $couriers[$idx % count($couriers)],
                    'vehicle_plate' => $plates[$idx % count($plates)],
                    'planned_departure_time' => '07:35',
                    'scheduled_arrival_time' => '08:05',
                ]);
                $created++;
            }

            if ($created > 0) {
                AuditLogger::record(
                    action: 'Membuat jadwal distribusi dari menu disetujui',
                    module: 'Jadwal Distribusi',
                    target: $date->toDateString()." ({$created} rute)",
                    actor: $actor,
                );
            }
        });

        return $created;
    }

    /**
     * Catat tindakan penyelesaian keluhan oleh Asisten Lapangan.
     */
    public function handleComplaint(
        ComplaintTicket $ticket,
        User $actor,
        string $status,
        string $resolutionNotes,
        ?Carbon $handledAt = null,
    ): ComplaintTicket {
        return DB::transaction(function () use ($ticket, $actor, $status, $resolutionNotes, $handledAt) {
            $locked = ComplaintTicket::query()->whereKey($ticket->id)->lockForUpdate()->firstOrFail();
            $oldStatus = $locked->status;
            $now = $handledAt ?? now('Asia/Jakarta');

            $locked->forceFill([
                'status' => $status,
                'resolved_by' => $actor->id,
                'resolution_notes' => trim($resolutionNotes),
                'first_response_at' => $locked->first_response_at ?? $now,
                'resolved_at' => $status === 'resolved' ? $now : null,
            ])->save();

            ComplaintHistory::create([
                'complaint_ticket_id' => $locked->id,
                'status' => $status,
                'user_id' => $actor->id,
                'notes' => trim($resolutionNotes),
                'created_at' => $now,
            ]);

            if ($status === 'resolved' && $locked->location_id) {
                $hasOtherOpen = ComplaintTicket::query()
                    ->where('location_id', $locked->location_id)
                    ->where('id', '!=', $locked->id)
                    ->whereNotIn('status', ['resolved', 'selesai'])
                    ->exists();

                if (! $hasOtherOpen) {
                    $route = DeliveryRoute::query()
                        ->where('location_id', $locked->location_id)
                        ->whereDate('operational_date', ($locked->reported_at ?? $now)->toDateString())
                        ->first();

                    if ($route && $route->confirmed_portions !== null && $route->status === DeliveryStatus::BERMASALAH) {
                        $route->forceFill(['status' => DeliveryStatus::DITERIMA])->save();
                    }
                }
            }

            AuditLogger::record(
                action: 'Mencatat tindak lanjut keluhan',
                module: 'Keluhan & Laporan',
                target: $locked->ticket_number,
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => $status, 'resolution_notes' => trim($resolutionNotes)],
                reason: trim($resolutionNotes),
                actor: $actor,
            );

            return $locked->fresh();
        });
    }
}
