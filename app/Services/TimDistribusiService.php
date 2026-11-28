<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Models\DeliveryDocument;
use App\Models\DeliveryRoute;
use App\Models\DeliveryStatusLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TimDistribusiService
{
    public function __construct(
        private readonly DeliveryMetrics $deliveryMetrics,
    ) {}

    /**
     * Data Dashboard Tim Distribusi dari database.
     *
     * @return array<string, mixed>
     */
    public function dashboardData(): array
    {
        $today = now('Asia/Jakarta');
        $todayStr = $today->toDateString();

        $routesToday = DeliveryRoute::query()
            ->with(['location', 'documents'])
            ->whereDate('operational_date', $todayStr)
            ->orderBy('waybill_number')
            ->get();

        $activeCount = $routesToday->filter(fn (DeliveryRoute $r) => in_array($r->status, [
            DeliveryStatus::DISIAPKAN,
            DeliveryStatus::BERANGKAT,
        ], true))->count();

        $handedOverRoutes = $routesToday->filter(fn (DeliveryRoute $r) => in_array($r->status, [
            DeliveryStatus::TIBA,
            DeliveryStatus::DITERIMA,
            DeliveryStatus::BERMASALAH,
        ], true));

        $portionsHandedOver = (int) $handedOverRoutes->sum(
            fn (DeliveryRoute $r) => $r->jumlah_diserahkan ?? $r->confirmed_portions ?? $r->dispatched_portions
        );
        $totalTarget = (int) $routesToday->sum('dispatched_portions');

        $aslap = User::where('username', 'bima.santoso')->first();

        return [
            'today_label' => $today->translatedFormat('l, j M Y'),
            'metrics' => $this->deliveryMetrics->forDate($today),
            'routes_today' => $routesToday,
            'active_count' => $activeCount,
            'handed_over_count' => $handedOverRoutes->count(),
            'total_routes' => $routesToday->count(),
            'portions_handed_over' => $portionsHandedOver,
            'total_target' => $totalTarget,
            'aslap_name' => $aslap?->name ?? 'Bima Santoso',
        ];
    }

    /**
     * Update status pengiriman (hanya Disiapkan -> Berangkat atau Berangkat -> Tiba) dan unggah bukti opsional.
     */
    public function updateDelivery(
        DeliveryRoute $route,
        User $actor,
        string $targetStatus,
        int $portions,
        ?string $recipientName,
        ?Carbon $eventTime,
        ?string $fieldNotes,
        ?UploadedFile $document = null,
    ): DeliveryRoute {
        $nextStatus = DeliveryStatus::from($targetStatus);

        if ($nextStatus === DeliveryStatus::DITERIMA) {
            abort(403, 'Status Diterima hanya dapat ditetapkan melalui konfirmasi Guru PIC.');
        }

        return DB::transaction(function () use ($route, $actor, $nextStatus, $portions, $recipientName, $eventTime, $fieldNotes, $document) {
            $locked = DeliveryRoute::query()->whereKey($route->id)->lockForUpdate()->firstOrFail();
            $current = $locked->status;

            $validTransition = ($current === DeliveryStatus::DISIAPKAN && $nextStatus === DeliveryStatus::BERANGKAT)
                || ($current === DeliveryStatus::BERANGKAT && $nextStatus === DeliveryStatus::TIBA);

            if (! $validTransition) {
                throw ValidationException::withMessages([
                    'status' => "Transisi dari {$current->label()} ke {$nextStatus->label()} tidak diperbolehkan.",
                ]);
            }

            $now = $eventTime ?? now('Asia/Jakarta');
            $oldValues = [
                'status' => $current->value,
                'dispatched_portions' => $locked->dispatched_portions,
                'jumlah_diserahkan' => $locked->jumlah_diserahkan,
            ];

            if ($nextStatus === DeliveryStatus::BERANGKAT) {
                $locked->forceFill([
                    'status' => DeliveryStatus::BERANGKAT,
                    'dispatched_portions' => $portions,
                    'departed_at' => $now,
                    'catatan_lapangan' => $fieldNotes ?: $locked->catatan_lapangan,
                ])->save();
            } else {
                $locked->forceFill([
                    'status' => DeliveryStatus::TIBA,
                    'arrived_at' => $now,
                    'nama_penerima_serah' => trim((string) $recipientName),
                    'jumlah_diserahkan' => $portions,
                    'catatan_lapangan' => $fieldNotes ?: $locked->catatan_lapangan,
                ])->save();
            }

            if ($document) {
                $path = $document->store('delivery-documents/'.$locked->id, 'local');
                DeliveryDocument::create([
                    'delivery_route_id' => $locked->id,
                    'path' => $path,
                    'original_name' => $document->getClientOriginalName(),
                    'mime' => $document->getMimeType() ?? 'application/octet-stream',
                    'size' => $document->getSize() ?? 0,
                    'uploaded_by' => $actor->id,
                ]);
            }

            DeliveryStatusLog::create([
                'delivery_route_id' => $locked->id,
                'status' => $nextStatus,
                'user_id' => $actor->id,
                'notes' => $fieldNotes ?: "Status diperbarui menjadi {$nextStatus->label()} ({$portions} porsi).",
                'created_at' => $now,
            ]);

            AuditLogger::record(
                action: "Memperbarui status pengiriman ke {$nextStatus->label()}",
                module: 'Tim Distribusi',
                target: $locked->waybill_number.' · '.$locked->location?->name,
                oldValues: $oldValues,
                newValues: [
                    'status' => $nextStatus->value,
                    'portions' => $portions,
                    'nama_penerima_serah' => $locked->nama_penerima_serah,
                ],
                actor: $actor,
            );

            return $locked->fresh(['documents', 'statusLogs']);
        });
    }
}
