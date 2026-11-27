<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\SystemBackup;
use App\Models\User;
use App\Services\DatabaseBackupService;
use Database\Seeders\RekagiziAdminSeeder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StageFiveSystemSettingsAndBackupTest extends TestCase
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

    public function test_security_settings_page_renders_and_updates_settings_with_audit_log(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.settings.keamanan-sesi'))
            ->assertOk()
            ->assertSee('Kebijakan Sesi', false);

        $this->actingAs($this->admin)->put(route('admin.settings.keamanan-sesi.update'), [
            'session_idle_timeout_minutes' => 45,
            'max_failed_login_attempts' => 4,
            'account_lock_duration_minutes' => 20,
            'password_min_length' => 10,
            'password_require_mixed_case_number' => '1',
            'force_password_change_first_login' => '1',
            'logout_on_role_change' => '1',
        ])->assertRedirect(route('admin.settings.keamanan-sesi'));

        $this->assertDatabaseHas('system_settings', ['key' => 'session_idle_timeout_minutes', 'value' => '45']);
        $this->assertDatabaseHas('system_settings', ['key' => 'password_min_length', 'value' => '10']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'ubah_pengaturan', 'module' => 'Pengaturan Sistem · Keamanan Sesi']);
    }

    public function test_mysqldump_command_uses_single_transaction_and_excludes_protected_tables(): void
    {
        $command = app(DatabaseBackupService::class)->buildDumpCommand('sim_mbg_selayur', '127.0.0.1', '3306', 'root');

        $this->assertContains('--single-transaction', $command);

        foreach ([
            'audit_logs',
            'system_backups',
            'sessions',
            'cache',
            'cache_locks',
            'jobs',
            'job_batches',
            'failed_jobs',
        ] as $table) {
            $this->assertContains("--ignore-table=sim_mbg_selayur.{$table}", $command);
        }
    }

    public function test_manual_backup_download_and_restore_require_password_create_pre_restore_and_audit(): void
    {
        Storage::fake('local');

        $this->actingAs($this->admin)->get(route('admin.settings.backup.index'))->assertOk();

        $this->actingAs($this->admin)->post(route('admin.settings.backup.store'))
            ->assertRedirect(route('admin.settings.backup.index'));

        $backup = SystemBackup::where('trigger_type', 'manual')->latest('id')->firstOrFail();
        $this->assertSame('success', $backup->status);
        $this->assertSame('local', $backup->disk);
        Storage::disk('local')->assertExists($backup->path);
        $this->assertDatabaseHas('audit_logs', ['action' => 'backup_manual', 'target' => $backup->filename]);

        $this->actingAs($this->admin)->get(route('admin.settings.backup.download', $backup))
            ->assertOk();
        $this->assertDatabaseHas('audit_logs', ['action' => 'unduh_backup', 'target' => $backup->filename]);

        // Restore tanpa password ditolak
        $this->actingAs($this->admin)->post(route('admin.settings.backup.restore', $backup), [])
            ->assertSessionHasErrors('password');

        // Restore dengan password salah ditolak
        $this->actingAs($this->admin)->post(route('admin.settings.backup.restore', $backup), [
            'password' => 'Salah123',
        ])->assertSessionHasErrors('password');

        $auditCountBefore = AuditLog::count();

        // Restore dengan password benar berhasil dan membuat backup pre_restore
        $this->actingAs($this->admin)->post(route('admin.settings.backup.restore', $backup), [
            'password' => 'AmanAdmin2026',
        ])->assertRedirect(route('admin.settings.backup.index'));

        $this->assertNotNull($backup->fresh()->restored_at);
        $this->assertDatabaseHas('system_backups', ['trigger_type' => 'pre_restore', 'status' => 'success']);
        $this->assertGreaterThan($auditCountBefore, AuditLog::count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'restore', 'target' => $backup->filename]);
    }

    public function test_download_rejects_path_traversal_outside_private_backups_folder(): void
    {
        Storage::fake('local');

        $malicious = SystemBackup::create([
            'filename' => 'evil.sql',
            'disk' => 'local',
            'path' => '../.env',
            'size_bytes' => 10,
            'trigger_type' => 'manual',
            'status' => 'success',
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.settings.backup.download', $malicious))
            ->assertRedirect(route('admin.settings.backup.index'))
            ->assertSessionHas('error');
    }

    public function test_daily_backup_is_scheduled_at_0200_asia_jakarta(): void
    {
        $events = collect(app(Schedule::class)->events());
        $matched = $events->first(fn ($event): bool => str_contains((string) $event->command, 'mbg:backup-daily'));

        $this->assertNotNull($matched);
        $this->assertSame('0 2 * * *', $matched->expression);
        $this->assertSame('Asia/Jakarta', $matched->timezone);
    }

    public function test_non_admin_cannot_access_security_settings_or_backup(): void
    {
        $nonAdmin = User::where('role', UserRole::AKUNTAN->value)->firstOrFail();
        $nonAdmin->forceFill(['must_change_password' => false, 'password_expires_at' => null])->saveQuietly();

        $this->actingAs($nonAdmin)->get(route('admin.settings.keamanan-sesi'))->assertForbidden();
        $this->actingAs($nonAdmin)->get(route('admin.settings.backup.index'))->assertForbidden();
    }

    public function test_retensi_arsip_card_is_hidden_when_feature_flag_is_false(): void
    {
        config(['mbg.fitur_opsional.retensi_arsip' => false]);

        $this->actingAs($this->admin)
            ->get(route('admin.settings.backup.index'))
            ->assertOk()
            ->assertDontSee('Retensi Arsip');

        config(['mbg.fitur_opsional.retensi_arsip' => true]);

        $this->actingAs($this->admin)
            ->get(route('admin.settings.backup.index'))
            ->assertOk()
            ->assertSee('Retensi Arsip');
    }
}
