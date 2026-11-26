<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogService;
use Database\Seeders\RekagiziAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class StageThreeAuditLogsTest extends TestCase
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

    public function test_audit_logs_routes_are_read_only_without_store_update_or_destroy(): void
    {
        $this->assertTrue(Route::has('admin.audit-logs.index'));
        $this->assertTrue(Route::has('admin.audit-logs.show'));
        $this->assertFalse(Route::has('admin.audit-logs.store'));
        $this->assertFalse(Route::has('admin.audit-logs.update'));
        $this->assertFalse(Route::has('admin.audit-logs.destroy'));

        $writeMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];
        $hasWriteRoute = collect(Route::getRoutes())->contains(function ($route) use ($writeMethods): bool {
            return str_starts_with($route->uri(), 'admin/log-aktivitas')
                && count(array_intersect($writeMethods, $route->methods())) > 0;
        });

        $this->assertFalse($hasWriteRoute);
    }

    public function test_audit_logs_index_paginates_ten_rows_and_filters_accurately(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            AuditLogService::record(
                action: 'ubah',
                module: 'Data Master',
                target: "Item Uji #{$i}",
                oldValues: ['nama' => "Lama {$i}"],
                newValues: ['nama' => "Baru {$i}"],
                reason: 'Pengujian paginasi',
                actor: $this->admin,
                occurredAt: now()->subMinutes($i),
            );
        }

        $response = $this->actingAs($this->admin)->get(route('admin.audit-logs.index'));
        $response->assertOk();
        $response->assertSee('Riwayat Aktivitas (Append-Only)', false);
        $response->assertViewHas('logs', fn ($logs) => $logs->perPage() === 10 && $logs->count() === 10);

        // Filter q & rentang hari-ini
        $filtered = $this->actingAs($this->admin)->get(route('admin.audit-logs.index', [
            'q' => 'Item Uji #12',
            'modul' => 'Data Master',
            'aksi' => 'ubah',
            'rentang' => 'hari-ini',
        ]));

        $filtered->assertOk();
        $filtered->assertSee('Item Uji #12', false);
        $filtered->assertDontSee('Item Uji #11', false);
    }

    public function test_audit_log_detail_endpoint_returns_json_for_drawer_without_sensitive_keys(): void
    {
        $log = AuditLog::create([
            'occurred_at' => now(),
            'user_id' => $this->admin->id,
            'user_name_snapshot' => $this->admin->name,
            'role' => $this->admin->role->label(),
            'action' => 'reset_sandi',
            'module' => 'Manajemen Pengguna',
            'target' => 'siti.rahma',
            'ip_address' => '10.10.1.5',
            'old_values' => ['password' => 'rahasia', 'status' => 'aktif'],
            'new_values' => ['must_change_password' => true],
            'reason' => 'Permintaan reset sandi',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson(route('admin.audit-logs.show', $log));

        $response->assertOk()
            ->assertJsonPath('id', $log->id)
            ->assertJsonPath('action', 'reset_sandi')
            ->assertJsonPath('target', 'siti.rahma')
            ->assertJsonPath('reason', 'Permintaan reset sandi')
            ->assertJsonPath('old_values.status', 'aktif')
            ->assertJsonMissingPath('old_values.password');
    }

    public function test_audit_log_model_is_immutable(): void
    {
        $log = AuditLog::firstOrFail();

        $this->expectException(RuntimeException::class);
        $log->update(['reason' => 'Diubah']);
    }

    public function test_non_admin_cannot_access_audit_logs(): void
    {
        $nonAdmin = User::where('role', UserRole::AHLI_GIZI->value)->firstOrFail();
        $nonAdmin->forceFill(['must_change_password' => false, 'password_expires_at' => null])->saveQuietly();
        $log = AuditLog::firstOrFail();

        $this->actingAs($nonAdmin)->get(route('admin.audit-logs.index'))->assertForbidden();
        $this->actingAs($nonAdmin)->getJson(route('admin.audit-logs.show', $log))->assertForbidden();
    }

    public function test_seeder_creates_187_audit_logs_and_null_user_id_renders_snapshot(): void
    {
        $this->assertSame(187, AuditLog::count());

        $nullUserLog = AuditLog::whereNull('user_id')->firstOrFail();

        $this->actingAs($this->admin)
            ->get(route('admin.audit-logs.index', ['aksi' => 'login_gagal']))
            ->assertOk()
            ->assertSee($nullUserLog->user_name_snapshot, false);

        $this->actingAs($this->admin)
            ->getJson(route('admin.audit-logs.show', $nullUserLog))
            ->assertOk()
            ->assertJsonPath('user_name_snapshot', $nullUserLog->user_name_snapshot);
    }
}
