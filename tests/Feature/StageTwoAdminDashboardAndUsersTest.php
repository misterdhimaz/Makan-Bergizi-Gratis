<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Location;
use App\Models\User;
use App\Services\AdminDashboardService;
use Database\Seeders\RekagiziAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class StageTwoAdminDashboardAndUsersTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RekagiziAdminSeeder::class);

        $this->admin = User::where('username', 'arief.mahendra')->firstOrFail();
        $this->admin->forceFill([
            'password' => Hash::make('AmanAdmin2026'),
            'must_change_password' => false,
            'password_expires_at' => null,
        ])->saveQuietly();
    }

    public function test_dashboard_metrics_match_seeder_formula(): void
    {
        $metrics = app(AdminDashboardService::class)->metrics();

        $this->assertSame(1170, $metrics['dikirim']);
        $this->assertSame(318, $metrics['diterima']);
        $this->assertSame(850, $metrics['belum_dikonfirmasi']);
        $this->assertSame(-2, $metrics['selisih_terkonfirmasi']);
        $this->assertSame(4, $metrics['jumlah_titik_belum_dikonfirmasi']);
    }

    public function test_admin_dashboard_page_renders_from_database_and_under_three_seconds(): void
    {
        $this->travelTo(now()->setTime(8, 0));

        $started = microtime(true);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $elapsedMs = (microtime(true) - $started) * 1000;

        $response->assertOk();
        $response->assertSee('Selamat pagi, Arief', false);
        $response->assertSee('1.170', false);
        $response->assertSee('318', false);
        $response->assertSee('850', false);
        $response->assertSee('-2', false);
        $response->assertSee('4 titik', false);
        $response->assertSee('5 / 7', false);
        $response->assertSee('15', false);
        $response->assertSee('2 dinonaktifkan', false);
        $response->assertSee('Akun aktif', false);
        $response->assertSee('Ringkasan operasional hari ini', false);
        $response->assertDontSee('password', false);

        $this->assertLessThan(3000, $elapsedMs, "Dashboard merespons dalam {$elapsedMs} ms (batas 3000 ms).");
    }

    public function test_admin_lihat_operasional_gate_hides_ops_summary_when_disabled(): void
    {
        config(['mbg.admin_lihat_operasional' => false]);

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Ringkasan operasional hari ini', false);
    }

    public function test_users_index_renders_matrix_and_has_no_destroy_route(): void
    {
        $this->assertFalse(Route::has('admin.users.destroy'));
        $this->assertFalse(
            collect(Route::getRoutes())->contains(
                fn ($route) => str_contains($route->uri(), 'admin/pengguna') && in_array('DELETE', $route->methods(), true)
            )
        );

        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('Matriks hak akses', false);
        $response->assertSee('Arief Mahendra', false);
        $response->assertSee('Agus Prasetyo', false);
        $response->assertDontSee('>Hapus</', false);
        $response->assertDontSee('route(\'admin.users.destroy\')', false);
    }

    public function test_store_user_creates_temporary_password_audit_and_forces_first_login_change(): void
    {
        $before = AuditLog::count();

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Dewi Baru',
            'username' => 'dewi.baru',
            'email' => 'dewi.baru@mbg-selayur1.id',
            'role' => UserRole::AHLI_GIZI->value,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('temporary_password');
        $response->assertSessionHas('temporary_password_user', 'dewi.baru');

        $user = User::where('username', 'dewi.baru')->firstOrFail();
        $this->assertTrue($user->must_change_password);
        $this->assertNotNull($user->password_expires_at);
        $this->assertTrue($user->password_expires_at->greaterThan(now()->addHours(23)));

        $this->assertSame($before + 1, AuditLog::count());
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'tambah',
            'module' => 'Pengguna & Peran',
            'target' => 'dewi.baru',
        ]);

        $log = AuditLog::where('target', 'dewi.baru')->where('action', 'tambah')->first();
        $this->assertArrayNotHasKey('password', $log->new_values ?? []);
    }

    public function test_cannot_assign_second_active_guru_pic_to_same_location(): void
    {
        $sd130 = Location::where('name', 'SD Negeri 130 Palembang')->firstOrFail();

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'PIC Ganda',
            'username' => 'pic.ganda',
            'email' => 'pic.ganda@mbg-selayur1.id',
            'role' => UserRole::GURU_PIC->value,
            'location_id' => $sd130->id,
        ]);

        $response->assertSessionHasErrors('location_id');
        $this->assertDatabaseMissing('users', ['username' => 'pic.ganda']);
    }

    public function test_deactivate_and_activate_write_specific_audit_actions(): void
    {
        $target = User::where('username', 'nadia.putri')->firstOrFail();

        $this->actingAs($this->admin)
            ->patch(route('admin.users.deactivate', $target), ['reason' => 'Mutasi sementara'])
            ->assertRedirect(route('admin.users.index'));

        $target->refresh();
        $this->assertFalse($target->is_active);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'nonaktifkan',
            'target' => 'nadia.putri',
        ]);

        $this->actingAs($this->admin)
            ->patch(route('admin.users.activate', $target))
            ->assertRedirect(route('admin.users.index'));

        $target->refresh();
        $this->assertTrue($target->is_active);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'aktifkan',
            'target' => 'nadia.putri',
        ]);
    }

    public function test_admin_cannot_deactivate_own_account(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('admin.users.deactivate', $this->admin))
            ->assertSessionHasErrors('user');

        $this->admin->refresh();
        $this->assertTrue($this->admin->is_active);
    }

    public function test_reset_password_records_audit_without_storing_secret(): void
    {
        $target = User::where('username', 'rizky.pratama')->firstOrFail();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.reset-password', $target), [
                'reason' => 'Permintaan lupa sandi',
            ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('temporary_password');

        $target->refresh();
        $this->assertTrue($target->must_change_password);

        $log = AuditLog::where('action', 'reset_sandi')->where('target', 'rizky.pratama')->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertArrayNotHasKey('password', $log->new_values ?? []);
        $this->assertArrayNotHasKey('password', $log->old_values ?? []);
    }

    public function test_non_admin_cannot_open_admin_pages(): void
    {
        $ahli = User::where('role', UserRole::AHLI_GIZI->value)->where('is_active', true)->firstOrFail();
        $ahli->forceFill(['must_change_password' => false])->saveQuietly();

        $this->actingAs($ahli)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($ahli)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($ahli)->post(route('admin.users.store'), [])->assertForbidden();
    }

    public function test_update_user_writes_audit_log(): void
    {
        $target = User::where('username', 'bima.santoso')->firstOrFail();
        $before = AuditLog::count();

        $this->actingAs($this->admin)
            ->put(route('admin.users.update', $target), [
                'name' => 'Bima Santoso Updated',
                'username' => 'bima.santoso',
                'email' => $target->email,
                'role' => UserRole::ASISTEN_LAPANGAN->value,
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertSame('Bima Santoso Updated', $target->fresh()->name);
        $this->assertGreaterThan($before, AuditLog::count());
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'ubah',
            'target' => 'bima.santoso',
            'module' => 'Pengguna & Peran',
        ]);
    }
}
