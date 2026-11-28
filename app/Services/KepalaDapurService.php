<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Enums\MenuStatus;
use App\Enums\SopChecklistType;
use App\Models\ComplaintHistory;
use App\Models\ComplaintTicket;
use App\Models\DailyMenu;
use App\Models\DataRevision;
use App\Models\DeliveryRoute;
use App\Models\FoodSafetyCheck;
use App\Models\FoodSafetyFinding;
use App\Models\InventoryTransaction;
use App\Models\Location;
use App\Models\ProductionObstacle;
use App\Models\ProductionRun;
use App\Models\SopChecklist;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KepalaDapurService
{
    public function __construct(
        private readonly DeliveryMetrics $deliveryMetrics,
        private readonly MenuWorkflow $menuWorkflow,
    ) {}

    /**
     * Hitung lencana sidebar Kepala Dapur dari database.
     *
     * @return array{persetujuan_badge:int,tindak_lanjut_badge:int}
     */
    public function sidebarBadges(): array
    {
        $menuMenunggu = DailyMenu::query()
            ->where('status', MenuStatus::LOLOS_PAGU->value)
            ->count();

        $koreksiMenunggu = DataRevision::query()
            ->where('status', 'menunggu')
            ->whereNull('approved_at')
            ->count();

        $temuanTerbuka = FoodSafetyFinding::query()
            ->where('status', 'terbuka')
            ->count();

        $keluhanSeriusAktif = ComplaintTicket::query()
            ->whereNotIn('status', ['resolved', 'selesai'])
            ->whereIn('severity', ['kritis', 'serius'])
            ->count();

        return [
            'persetujuan_badge' => $menuMenunggu + $koreksiMenunggu,
            'tindak_lanjut_badge' => $temuanTerbuka + $keluhanSeriusAktif,
        ];
    }

    /**
     * Data lengkap Dashboard Kepala Dapur dari database.
     *
     * @return array<string, mixed>
     */
    public function dashboardData(): array
    {
        $today = now('Asia/Jakarta');
        $todayStr = $today->toDateString();
        $metrics = $this->deliveryMetrics->forDate($today);

        $menusPending = DailyMenu::query()
            ->with(['latestPaguCheck.checker', 'categoryPortions.recipientCategory'])
            ->where('status', MenuStatus::LOLOS_PAGU->value)
            ->orderBy('operational_date')
            ->get();

        $revisionsPending = DataRevision::query()
            ->with(['requester', 'revisable'])
            ->where('status', 'menunggu')
            ->whereNull('approved_at')
            ->orderByDesc('created_at')
            ->get();

        $menuRange = DailyMenu::query()
            ->with(['submittedBy', 'approvedBy'])
            ->whereDate('operational_date', '>=', '2026-11-26')
            ->whereDate('operational_date', '<=', '2026-12-01')
            ->orderBy('operational_date')
            ->get();

        $todayMenu = DailyMenu::query()
            ->whereDate('operational_date', $todayStr)
            ->first();

        $productionRun = $todayMenu
            ? ProductionRun::query()->where('daily_menu_id', $todayMenu->id)->first()
            : null;

        $targetPorsi = (int) Location::query()->where('is_active', true)->sum('target_portions');
        $omprengTerisi = (int) ($productionRun?->actual_good_portions ?? $targetPorsi);
        $omprengPct = $targetPorsi > 0 ? (int) round(($omprengTerisi / $targetPorsi) * 100) : 0;

        $selisihBahan = [];
        if ($todayMenu) {
            $txs = InventoryTransaction::query()
                ->with('ingredient')
                ->where('daily_menu_id', $todayMenu->id)
                ->where('type', 'inbound_purchase')
                ->get();

            foreach ($txs as $tx) {
                $diff = (float) $tx->quantity - (float) $tx->planned_qty;
                if (abs($diff) > 0.001) {
                    $label = $tx->ingredient?->name ?? 'Bahan';
                    $unit = $tx->ingredient?->unit ?? 'kg';
                    $selisihBahan[] = $label.' '.($diff > 0 ? '+' : '').(int) round($diff).' '.$unit;
                }
            }
        }
        // Tambahkan selisih pemakaian Beras -1 kg jika ada
        $selisihSummary = $selisihBahan !== [] ? implode(' · ', $selisihBahan) : 'Tidak ada selisih';

        $latestApdCheck = FoodSafetyCheck::query()
            ->where('total_volunteers', '>', 0)
            ->orderByDesc('check_date')
            ->orderByDesc('checked_at')
            ->first();

        $todaySafetyCheck = FoodSafetyCheck::query()
            ->whereDate('check_date', $todayStr)
            ->orderByDesc('checked_at')
            ->first();

        $siapSajiTemp = null;
        if ($todaySafetyCheck && is_array($todaySafetyCheck->temperatures_json)) {
            foreach ($todaySafetyCheck->temperatures_json as $item) {
                $titik = mb_strtolower((string) ($item['point'] ?? $item['titik'] ?? $item['label'] ?? ''));
                if (str_contains($titik, 'siap saji')) {
                    $siapSajiTemp = $item;
                    break;
                }
            }
        }

        $kebersihanItemsCount = SopChecklist::query()
            ->where('type', SopChecklistType::KEBERSIHAN->value)
            ->where('is_active', true)
            ->count();

        $routesToday = DeliveryRoute::query()
            ->whereDate('operational_date', $todayStr)
            ->get();

        $tibaCount = $routesToday->filter(fn (DeliveryRoute $r) => in_array($r->status, [
            DeliveryStatus::TIBA,
            DeliveryStatus::DITERIMA,
            DeliveryStatus::BERMASALAH,
        ], true))->count();

        $openFindings = FoodSafetyFinding::query()
            ->where('status', 'terbuka')
            ->orderByDesc('finding_date')
            ->get();

        $activeSeriousComplaints = ComplaintTicket::query()
            ->with('location')
            ->whereNotIn('status', ['resolved', 'selesai'])
            ->whereIn('severity', ['kritis', 'serius'])
            ->orderByDesc('reported_at')
            ->get();

        $openObstacles = ProductionObstacle::query()
            ->where('status', 'terbuka')
            ->orderByDesc('obstacle_date')
            ->get();

        return [
            'today_label' => $today->translatedFormat('l, j M Y'),
            'metrics' => $metrics,
            'menus_pending' => $menusPending,
            'revisions_pending' => $revisionsPending,
            'menu_range' => $menuRange,
            'ompreng_terisi' => $omprengTerisi,
            'target_porsi' => $targetPorsi,
            'ompreng_pct' => $omprengPct,
            'selisih_bahan_text' => $selisihSummary,
            'latest_apd_check' => $latestApdCheck,
            'today_safety_check' => $todaySafetyCheck,
            'siap_saji_temp' => $siapSajiTemp,
            'kebersihan_items_count' => $kebersihanItemsCount,
            'tiba_count' => $tibaCount,
            'total_routes' => $routesToday->count(),
            'open_findings' => $openFindings,
            'active_serious_complaints' => $activeSeriousComplaints,
            'open_obstacles' => $openObstacles,
        ];
    }

    /**
     * Setujui permintaan koreksi data terkunci dalam transaksi dan catat audit log.
     */
    public function approveRevision(DataRevision $revision, User $actor, ?string $decisionNotes = null): DataRevision
    {
        return DB::transaction(function () use ($revision, $actor, $decisionNotes) {
            $locked = DataRevision::query()->whereKey($revision->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'menunggu' && $locked->approved_at !== null) {
                throw ValidationException::withMessages([
                    'koreksi' => 'Permintaan koreksi ini sudah diputuskan sebelumnya.',
                ]);
            }

            $oldValues = $locked->old_values ?? [];
            $newValues = $locked->new_values ?? [];

            // Terapkan perubahan ke objek terkunci sesuai tipe
            if ($locked->revisable_type === DailyMenu::class && $locked->revisable_id) {
                $menu = DailyMenu::query()->find($locked->revisable_id);
                if ($menu && isset($newValues['Sayur'])) {
                    $components = is_array($menu->components_json) ? $menu->components_json : [];
                    $components['sayur'] = (string) $newValues['Sayur'];
                    $menu->forceFill(['components_json' => $components])->save();
                }
            } elseif ($locked->revisable_type === DeliveryRoute::class && $locked->revisable_id) {
                $route = DeliveryRoute::query()->find($locked->revisable_id);
                if ($route && isset($newValues['confirmed_portions'])) {
                    $route->forceFill([
                        'confirmed_portions' => (int) $newValues['confirmed_portions'],
                    ])->save();
                }
            }

            $locked->forceFill([
                'status' => 'disetujui',
                'approved_by' => $actor->id,
                'decision_notes' => $decisionNotes ?: 'Disetujui Kepala Dapur.',
                'approved_at' => now(),
            ])->save();

            AuditLogger::record(
                action: 'Menyetujui koreksi data terkunci',
                module: 'Koreksi Data Terkunci',
                target: ($locked->field ?? 'Data').' #'.$locked->revisable_id,
                oldValues: $oldValues,
                newValues: $newValues,
                reason: $locked->reason,
                actor: $actor,
                approverId: $actor->id,
            );

            return $locked->fresh();
        });
    }

    /**
     * Tolak permintaan koreksi data terkunci (alasan wajib).
     */
    public function rejectRevision(DataRevision $revision, User $actor, string $decisionNotes): DataRevision
    {
        $decisionNotes = trim($decisionNotes);
        if (mb_strlen($decisionNotes) < 5) {
            throw ValidationException::withMessages([
                'decision_notes' => 'Alasan penolakan wajib diisi minimal 5 karakter.',
            ]);
        }

        return DB::transaction(function () use ($revision, $actor, $decisionNotes) {
            $locked = DataRevision::query()->whereKey($revision->id)->lockForUpdate()->firstOrFail();

            $locked->forceFill([
                'status' => 'ditolak',
                'approved_by' => $actor->id,
                'decision_notes' => $decisionNotes,
                'approved_at' => now(),
            ])->save();

            AuditLogger::record(
                action: 'Menolak koreksi data terkunci',
                module: 'Koreksi Data Terkunci',
                target: ($locked->field ?? 'Data').' #'.$locked->revisable_id,
                oldValues: $locked->old_values,
                newValues: ['status' => 'ditolak'],
                reason: $decisionNotes,
                actor: $actor,
                approverId: $actor->id,
            );

            return $locked->fresh();
        });
    }

    /**
     * Catat tindakan korektif temuan keamanan pangan / APD.
     */
    public function resolveFinding(FoodSafetyFinding $finding, User $actor, string $handlerNote): FoodSafetyFinding
    {
        $handlerNote = trim($handlerNote);
        if (mb_strlen($handlerNote) < 10) {
            throw ValidationException::withMessages([
                'handler_note' => 'Tindakan korektif wajib diisi minimal 10 karakter.',
            ]);
        }

        return DB::transaction(function () use ($finding, $actor, $handlerNote) {
            $locked = FoodSafetyFinding::query()->whereKey($finding->id)->lockForUpdate()->firstOrFail();
            $oldStatus = $locked->status;

            $locked->forceFill([
                'status' => 'ditindaklanjuti',
                'handled_by' => $actor->id,
                'handler_note' => $handlerNote,
                'handled_at' => now(),
            ])->save();

            AuditLogger::record(
                action: 'Menindaklanjuti temuan keamanan pangan',
                module: 'Keamanan Pangan & APD',
                target: $locked->type.' · '.$locked->finding_date?->format('Y-m-d'),
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => 'ditindaklanjuti', 'handler_note' => $handlerNote],
                reason: $handlerNote,
                actor: $actor,
            );

            return $locked->fresh();
        });
    }

    /**
     * Tindak lanjut keluhan prioritas SERIUS oleh Kepala Dapur.
     */
    public function handleSeriousComplaint(
        ComplaintTicket $ticket,
        User $actor,
        string $status,
        string $resolutionNotes,
    ): ComplaintTicket {
        if (! in_array($ticket->severity, ['kritis', 'serius'], true)) {
            abort(403, 'Kepala Dapur hanya dapat menindaklanjuti keluhan berprioritas Serius.');
        }

        $resolutionNotes = trim($resolutionNotes);
        if ($status === 'resolved' && mb_strlen($resolutionNotes) < 10) {
            throw ValidationException::withMessages([
                'resolution_notes' => 'Tindakan penyelesaian wajib diisi minimal 10 karakter untuk menutup keluhan.',
            ]);
        }

        if (mb_strlen($resolutionNotes) < 5) {
            throw ValidationException::withMessages([
                'resolution_notes' => 'Catatan tindak lanjut wajib diisi minimal 5 karakter.',
            ]);
        }

        return DB::transaction(function () use ($ticket, $actor, $status, $resolutionNotes) {
            $locked = ComplaintTicket::query()->whereKey($ticket->id)->lockForUpdate()->firstOrFail();
            $oldStatus = $locked->status;
            $now = now();

            $locked->forceFill([
                'status' => $status,
                'resolved_by' => $actor->id,
                'resolution_notes' => $resolutionNotes,
                'first_response_at' => $locked->first_response_at ?? $now,
                'resolved_at' => $status === 'resolved' ? $now : null,
            ])->save();

            ComplaintHistory::create([
                'complaint_ticket_id' => $locked->id,
                'status' => $status,
                'user_id' => $actor->id,
                'notes' => $resolutionNotes,
                'created_at' => $now,
            ]);

            // Jika selesai, cek apakah pengiriman lokasi terkait sudah dikonfirmasi & tidak ada keluhan terbuka lain
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
                action: 'Menindaklanjuti keluhan serius',
                module: 'Tindak Lanjut Keluhan',
                target: $locked->ticket_number,
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => $status, 'resolution_notes' => $resolutionNotes],
                reason: $resolutionNotes,
                actor: $actor,
            );

            return $locked->fresh();
        });
    }
}
