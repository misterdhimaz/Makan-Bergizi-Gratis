<?php

namespace Tests\Feature;

use App\Enums\MenuStatus;
use App\Exceptions\MenuStatusConflictException;
use App\Models\AuditLog;
use App\Models\ComplaintTicket;
use App\Models\DailyMenu;
use App\Models\DataRevision;
use App\Models\DeliveryRoute;
use App\Models\FoodSafetyFinding;
use App\Models\Location;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\DeliveryMetrics;
use App\Services\MenuWorkflow;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class PondasiCanonicalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_seed_canonical_counts_match_specification(): void
    {
        $this->assertSame(17, User::count());
        $this->assertSame(15, User::where('is_active', true)->count());
        $this->assertSame(2, User::where('is_active', false)->count());

        $this->assertSame(7, Location::where('is_active', true)->count());
        $this->assertSame(1414, (int) Location::where('is_active', true)->sum('target_portions'));

        $this->assertSame(6, DailyMenu::count());
        $this->assertSame(7, DeliveryRoute::whereDate('operational_date', '2026-11-26')->count());
        $this->assertSame(3, ComplaintTicket::count());
        $this->assertSame(2, DataRevision::count());
        $this->assertSame(1, DataRevision::where('status', 'menunggu')->count());
        $this->assertSame(1, FoodSafetyFinding::where('status', 'terbuka')->count());
    }

    public function test_delivery_metrics_before_and_after_demo_advance_pesantren_konfirmasi(): void
    {
        $metricsService = app(DeliveryMetrics::class);
        $before = $metricsService->forDate();

        $this->assertSame(1170, $before['dikirim']);
        $this->assertSame(318, $before['diterima']);
        $this->assertSame(2, $before['titik_terkonfirmasi']);
        $this->assertSame(850, $before['belum_dikonfirmasi']);
        $this->assertSame(-2, $before['selisih_terkonfirmasi']);
        $this->assertSame(1, $before['keluhan_aktif']);
        $this->assertSame(1, $before['temuan_keamanan_terbuka']);
        $this->assertSame(1, $before['koreksi_menunggu']);
        $this->assertSame(0, $before['menu_menunggu_kepala_dapur']);

        $this->artisan('demo:advance', ['scenario' => 'pesantren-konfirmasi'])
            ->assertExitCode(0);

        // Jalankan ulang untuk verifikasi idempotensi
        $this->artisan('demo:advance', ['scenario' => 'pesantren-konfirmasi'])
            ->assertExitCode(0);

        $after = $metricsService->forDate();
        $this->assertSame(1170, $after['dikirim']);
        $this->assertSame(664, $after['diterima']);
        $this->assertSame(3, $after['titik_terkonfirmasi']);
        $this->assertSame(500, $after['belum_dikonfirmasi']);
        $this->assertSame(-6, $after['selisih_terkonfirmasi']);
    }

    public function test_menu_workflow_valid_and_invalid_transitions(): void
    {
        $workflow = app(MenuWorkflow::class);
        $hendra = User::where('username', 'hendra.wijaya')->firstOrFail();
        $menu28 = DailyMenu::whereDate('operational_date', '2026-11-28')->firstOrFail();

        // Tidak boleh disetujui sebelum lolos_pagu
        try {
            $workflow->approveByKepalaDapur($menu28, $hendra, 'Coba setujui langsung');
            $this->fail('Seharusnya melempar MenuStatusConflictException.');
        } catch (MenuStatusConflictException) {
            $this->assertTrue(true);
        }

        // Majukan via demo:advance akuntan-lolos-28nov
        $this->artisan('demo:advance', ['scenario' => 'akuntan-lolos-28nov'])
            ->assertExitCode(0);

        $menu28->refresh();
        $this->assertSame(MenuStatus::LOLOS_PAGU->value, $menu28->status);

        // Sekarang Kepala Dapur boleh menyetujui
        $approved = $workflow->approveByKepalaDapur($menu28, $hendra, 'Siap produksi 28 Nov');
        $this->assertSame(MenuStatus::DISETUJUI->value, $approved->status);
        $this->assertNotNull($approved->locked_at);
    }

    public function test_audit_log_is_append_only(): void
    {
        $log = AuditLogger::record(
            action: 'Uji append-only',
            module: 'Pondasi',
            target: 'AuditLog',
        );

        $this->assertNotNull($log);

        $this->expectException(RuntimeException::class);
        $log->update(['action' => 'Diubah']);
    }

    public function test_audit_log_cannot_be_deleted(): void
    {
        $log = AuditLog::firstOrFail();

        $this->expectException(RuntimeException::class);
        $log->delete();
    }
}
