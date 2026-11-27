<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\SystemSetting;
use App\Models\User;
use Database\Seeders\RekagiziAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StageOneAuthAndRbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RekagiziAdminSeeder::class);
    }

    public function test_login_page_reads_session_and_lock_settings_from_system_settings(): void
    {
        SystemSetting::where('key', 'session_idle_timeout_minutes')->update(['value' => '45']);
        SystemSetting::where('key', 'max_failed_login_attempts')->update(['value' => '7']);

        $response = $this->get(route('login.show'));

        $response->assertOk();
        $response->assertSee('Batas sesi otomatis 45 menit');
        $response->assertSee('setelah 7 kali gagal masuk');
        $response->assertDontSee('Pertahankan sesi');
    }

    public function test_inactive_user_cannot_login_and_writes_audit_log(): void
    {
        $inactive = User::where('username', 'rina.safitri')->firstOrFail();
        $inactive->forceFill(['password' => Hash::make('Rahasia123')])->saveQuietly();

        $response = $this->post(route('login.post'), [
            'username' => 'rina.safitri',
            'password' => 'Rahasia123',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'login_gagal',
            'target' => 'rina.safitri',
        ]);
    }

    public function test_account_locks_after_configured_failed_attempts(): void
    {
        SystemSetting::where('key', 'max_failed_login_attempts')->update(['value' => '3']);
        SystemSetting::where('key', 'account_lock_duration_minutes')->update(['value' => '20']);

        $user = User::where('username', 'arief.mahendra')->firstOrFail();
        $user->forceFill([
            'password' => Hash::make('Benar1234'),
            'must_change_password' => false,
        ])->saveQuietly();

        for ($i = 0; $i < 3; $i++) {
            $this->post(route('login.post'), [
                'username' => 'arief.mahendra',
                'password' => 'SalahTerus',
            ]);
        }

        $user->refresh();
        $this->assertNotNull($user->locked_until);
        $this->assertTrue($user->locked_until->isFuture());

        // Meski sandi benar, tetap ditolak selama terkunci
        $lockedResponse = $this->post(route('login.post'), [
            'username' => 'arief.mahendra',
            'password' => 'Benar1234',
        ]);
        $lockedResponse->assertSessionHas('auth_status_type', 'locked');
        $this->assertGuest();
    }

    public function test_session_auto_logout_respects_system_settings_timeout(): void
    {
        SystemSetting::where('key', 'session_idle_timeout_minutes')->update(['value' => '15']);

        $admin = User::where('username', 'arief.mahendra')->firstOrFail();
        $admin->forceFill(['must_change_password' => false])->saveQuietly();

        $this->actingAs($admin)
            ->withSession(['last_activity_timestamp' => now()->subMinutes(16)->timestamp])
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('login.show'))
            ->assertSessionHas('auth_status_type', 'session_expired');

        $this->assertGuest();
    }

    public function test_forgot_password_saves_request_and_records_audit_log(): void
    {
        $response = $this->post(route('password.forgot'), [
            'identifier' => 'arief.mahendra',
            'reason' => 'Lupa kata sandi',
        ]);

        $response->assertRedirect(route('login.show'));
        $this->assertDatabaseHas('password_reset_requests', [
            'identifier' => 'arief.mahendra',
            'reason' => 'Lupa kata sandi',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'lupa_sandi',
            'target' => 'arief.mahendra',
        ]);
    }

    public function test_first_login_requires_password_change_and_enforces_policy(): void
    {
        $admin = User::where('username', 'arief.mahendra')->firstOrFail();
        $admin->forceFill([
            'password' => Hash::make('Sementara1'),
            'must_change_password' => true,
            'password_expires_at' => now()->addHours(24),
        ])->saveQuietly();

        $loginResp = $this->post(route('login.post'), [
            'username' => 'arief.mahendra',
            'password' => 'Sementara1',
        ]);

        $loginResp->assertRedirect(route('password.change'));

        // Akses /admin/dashboard dipaksa ke /ganti-sandi sebelum mengganti sandi
        $this->get(route('admin.dashboard'))->assertRedirect(route('password.change'));

        // Sandi baru lemah ditolak
        $this->post(route('password.change.store'), [
            'current_password' => 'Sementara1',
            'password' => 'lemah',
            'password_confirmation' => 'lemah',
        ])->assertSessionHasErrors('password');

        // Sandi baru memenuhi kebijakan
        $this->post(route('password.change.store'), [
            'current_password' => 'Sementara1',
            'password' => 'AmanBaru2026',
            'password_confirmation' => 'AmanBaru2026',
        ])->assertRedirect(route('admin.dashboard'));

        $admin->refresh();
        $this->assertFalse($admin->must_change_password);
        $this->assertNull($admin->password_expires_at);

        $log = AuditLog::where('action', 'ganti_sandi')->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertArrayNotHasKey('password', $log->new_values ?? []);
    }

    public function test_admin_cannot_write_to_operational_modules_and_non_admin_cannot_open_admin_routes(): void
    {
        $admin = User::where('username', 'arief.mahendra')->firstOrFail();
        $admin->forceFill(['must_change_password' => false])->saveQuietly();

        $this->actingAs($admin)
            ->post(route('menu-planning.store'), [])
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('distribution.store'), [])
            ->assertForbidden();

        $ahliGizi = User::where('role', UserRole::AHLI_GIZI->value)->where('is_active', true)->firstOrFail();
        $ahliGizi->forceFill(['must_change_password' => false])->saveQuietly();

        $this->actingAs($ahliGizi)
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        // /dashboard mengarahkan Admin ke admin.dashboard, sedangkan non-Admin ke placeholder
        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->get('/')
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($ahliGizi)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard untuk peran ini belum tersedia', false);
    }
}
