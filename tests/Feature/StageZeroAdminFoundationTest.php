<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Location;
use App\Models\User;
use App\Services\AdminDashboardService;
use App\Services\AuditLogService;
use App\Services\DataMasterCompletenessService;
use Database\Seeders\RekagiziAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class StageZeroAdminFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_schema_revisions_remove_excluded_columns_and_enforce_constraints(): void
    {
        $this->assertFalse(Schema::hasColumn('users', 'phone'));
        $this->assertTrue(Schema::hasColumn('users', 'username'));
        $this->assertTrue(Schema::hasColumn('users', 'location_id'));

        $this->assertFalse(Schema::hasColumn('locations', 'pic_name'));
        $this->assertFalse(Schema::hasColumn('locations', 'pic_phone'));

        $this->assertFalse(Schema::hasColumn('ingredients', 'sku'));
        $this->assertFalse(Schema::hasColumn('ingredients', 'min_stock_threshold'));
        $this->assertTrue(Schema::hasColumn('ingredients', 'supplier_id'));

        $this->assertFalse(Schema::hasColumn('audit_logs', 'updated_at'));
    }

    public function test_seeder_produces_exact_admin_dashboard_metrics_and_5_of_7_data_master(): void
    {
        $this->seed(RekagiziAdminSeeder::class);

        $dashboard = app(AdminDashboardService::class)->metrics();

        $this->assertSame(1170, $dashboard['dikirim']);
        $this->assertSame(318, $dashboard['diterima']);
        $this->assertSame(850, $dashboard['belum_dikonfirmasi']);
        $this->assertSame(-2, $dashboard['selisih_terkonfirmasi']);
        $this->assertSame(4, $dashboard['jumlah_titik_belum_dikonfirmasi']);

        $this->assertSame(15, $dashboard['akun_aktif']);
        $this->assertSame(2, $dashboard['akun_nonaktif']);
        $this->assertSame(17, $dashboard['akun_total']);
        $this->assertSame(3, $dashboard['login_gagal_24_jam']);

        $completeness = app(DataMasterCompletenessService::class)->evaluate();

        $this->assertSame(5, $completeness['siap_count']);
        $this->assertSame(7, $completeness['total']);
        $this->assertTrue($completeness['items']['lokasi']['complete']);
        $this->assertTrue($completeness['items']['kategori']['complete']);
        $this->assertTrue($completeness['items']['standar_porsi']['complete']);
        $this->assertTrue($completeness['items']['bahan']['complete']);
        $this->assertFalse($completeness['items']['pemasok']['complete']);
        $this->assertTrue($completeness['items']['pagu']['complete']);
        $this->assertFalse($completeness['items']['checklist_sop']['complete']);

        $this->assertSame(187, AuditLog::count());

        $sd130 = Location::where('name', 'SD Negeri 130 Palembang')->firstOrFail();
        $this->assertSame('Agus Prasetyo', $sd130->activeGuruPic?->name);
        $this->assertSame(1, User::where('location_id', $sd130->id)->where('role', UserRole::GURU_PIC->value)->where('is_active', true)->count());
    }

    public function test_audit_log_is_immutable_and_never_stores_password_or_hash(): void
    {
        $log = AuditLogService::record(
            action: 'tambah',
            module: 'Pengguna & Peran',
            target: 'uji.audit',
            oldValues: ['password' => 'secret123', 'name' => 'Lama'],
            newValues: ['password' => '$2y$12$hash', 'remember_token' => 'tok', 'name' => 'Baru'],
        );

        $this->assertNotNull($log);
        $this->assertArrayNotHasKey('password', $log->old_values ?? []);
        $this->assertArrayNotHasKey('password', $log->new_values ?? []);
        $this->assertArrayNotHasKey('remember_token', $log->new_values ?? []);

        $this->expectException(RuntimeException::class);
        $log->update(['action' => 'diubah']);
    }

    public function test_audit_log_rejects_delete(): void
    {
        $log = AuditLogService::record(
            action: 'login',
            module: 'Autentikasi',
            target: 'uji.delete',
        );

        $this->expectException(RuntimeException::class);
        $log->delete();
    }

    public function test_base_blade_components_render_cleanly(): void
    {
        $statHtml = view('components.stat-card', [
            'label' => 'Porsi Dikirim',
            'value' => '1.170',
            'badge' => 'Hari ini',
            'highlight' => true,
        ])->render();
        $this->assertStringContainsString('1.170', $statHtml);

        $badgeHtml = view('components.badge-status', [
            'label' => 'Diterima',
            'variant' => 'success',
            'dot' => true,
        ])->render();
        $this->assertStringContainsString('Diterima', $badgeHtml);

        $emptyHtml = view('components.empty-state', [
            'title' => 'Tidak ada data',
            'description' => 'Kosong',
        ])->render();
        $this->assertStringContainsString('Tidak ada data', $emptyHtml);
    }
}
