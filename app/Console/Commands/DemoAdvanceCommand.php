<?php

namespace App\Console\Commands;

use App\Enums\DeliveryStatus;
use App\Enums\MenuStatus;
use App\Enums\PaguCheckResult;
use App\Models\DailyMenu;
use App\Models\DeliveryRoute;
use App\Models\DeliveryStatusLog;
use App\Models\Location;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\MenuWorkflow;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DemoAdvanceCommand extends Command
{
    protected $signature = 'demo:advance {scenario : akuntan-lolos-28nov atau pesantren-konfirmasi}';

    protected $description = 'Memajukan state demo secara idempoten (hanya environment local/testing).';

    public function handle(MenuWorkflow $menuWorkflow): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->error('Perintah demo:advance hanya boleh dijalankan di environment local atau testing.');

            return self::FAILURE;
        }

        return match ($this->argument('scenario')) {
            'akuntan-lolos-28nov' => $this->advanceAkuntanLolos28Nov($menuWorkflow),
            'pesantren-konfirmasi' => $this->advancePesantrenKonfirmasi(),
            default => $this->invalidScenario(),
        };
    }

    private function advanceAkuntanLolos28Nov(MenuWorkflow $menuWorkflow): int
    {
        $menu = DailyMenu::query()
            ->whereDate('operational_date', '2026-11-28')
            ->first();

        if (! $menu) {
            $this->error('Menu tanggal 2026-11-28 tidak ditemukan.');

            return self::FAILURE;
        }

        $status = MenuStatus::fromLegacy((string) $menu->status);
        if (in_array($status, [MenuStatus::LOLOS_PAGU, MenuStatus::DISETUJUI], true)) {
            $this->info('Menu 28 Nov 2026 sudah berstatus '.$status->label().' (idempoten).');

            return self::SUCCESS;
        }

        $rizky = User::where('username', 'rizky.pratama')->firstOrFail();
        $checkTime = Carbon::parse('2026-11-26 09:10:00', 'Asia/Jakarta');

        $check = $menuWorkflow->recordPaguCheck(
            menu: $menu,
            actor: $rizky,
            hasil: PaguCheckResult::LOLOS,
            catatan: 'Sesuai batas PAGU.',
        );

        $check->forceFill(['checked_at' => $checkTime])->saveQuietly();
        $menu->fresh()->forceFill(['pagu_verified_at' => $checkTime])->saveQuietly();

        $this->info('Menu 28 Nov 2026 berhasil dicatat Lolos PAGU oleh Rizky Pratama.');

        return self::SUCCESS;
    }

    private function advancePesantrenKonfirmasi(): int
    {
        $pesantren = Location::where('name', 'Pesantren Al-Falah Palembang')->first();
        if (! $pesantren) {
            $this->error('Lokasi Pesantren Al-Falah Palembang tidak ditemukan.');

            return self::FAILURE;
        }

        $route = DeliveryRoute::query()
            ->where('location_id', $pesantren->id)
            ->whereDate('operational_date', '2026-11-26')
            ->first();

        if (! $route) {
            $this->error('Pengiriman Pesantren Al-Falah tanggal 2026-11-26 tidak ditemukan.');

            return self::FAILURE;
        }

        if ($route->confirmed_portions === 346 && $route->status === DeliveryStatus::DITERIMA) {
            $this->info('Konfirmasi Pesantren Al-Falah sudah tercatat (idempoten).');

            return self::SUCCESS;
        }

        $rina = User::where('username', 'rina.marlina')->firstOrFail();
        $arrivedAt = Carbon::parse('2026-11-26 08:40:00', 'Asia/Jakarta');
        $confirmedAt = Carbon::parse('2026-11-26 08:45:00', 'Asia/Jakarta');

        $route->forceFill([
            'status' => DeliveryStatus::DITERIMA,
            'confirmed_portions' => 346,
            'confirmed_by' => $rina->id,
            'arrived_at' => $arrivedAt,
            'confirmed_at' => $confirmedAt,
            'packaging_condition' => 'Sebagian ompreng tumpah / rusak',
            'organoleptic_taste' => 'normal',
            'organoleptic_aroma' => 'normal',
            'organoleptic_texture' => 'normal',
            'organoleptic_color' => 'normal',
            'organoleptic_status' => 'Layak',
            'notes' => 'Diterima 346 dari 350 porsi (4 ompreng tumpah).',
        ])->save();

        DeliveryStatusLog::create([
            'delivery_route_id' => $route->id,
            'status' => DeliveryStatus::DITERIMA,
            'user_id' => $rina->id,
            'notes' => 'Konfirmasi penerimaan 346/350 porsi oleh Rina Marlina.',
            'created_at' => $confirmedAt,
        ]);

        AuditLogger::record(
            action: 'Mengonfirmasi penerimaan pengiriman',
            module: 'Konfirmasi Penerimaan',
            target: $route->waybill_number.' · Pesantren Al-Falah Palembang',
            oldValues: ['confirmed_portions' => null, 'status' => DeliveryStatus::BERMASALAH->value],
            newValues: ['confirmed_portions' => 346, 'status' => DeliveryStatus::DITERIMA->value],
            reason: 'Konfirmasi penerimaan Pesantren Al-Falah (346/350 porsi)',
            actor: $rina,
            occurredAt: $confirmedAt,
        );

        $this->info('Konfirmasi Pesantren Al-Falah (346/350 porsi) berhasil dicatat.');

        return self::SUCCESS;
    }

    private function invalidScenario(): int
    {
        $this->error('Skenario tidak dikenal. Gunakan: akuntan-lolos-28nov atau pesantren-konfirmasi.');

        return self::FAILURE;
    }
}
