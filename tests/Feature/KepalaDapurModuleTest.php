<?php

namespace Tests\Feature;

use App\Enums\MenuStatus;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\ComplaintTicket;
use App\Models\DailyMenu;
use App\Models\DataRevision;
use App\Models\FoodSafetyFinding;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KepalaDapurModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $kepalaDapur;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->kepalaDapur = User::where('username', 'hendra.wijaya')->firstOrFail();
    }

    public function test_role_access_and_guest_redirect(): void
    {
        $this->get(route('kepala-dapur.dashboard'))
            ->assertRedirect(route('login'));

        $akuntan = User::where('role', UserRole::AKUNTAN->value)->where('is_active', true)->firstOrFail();
        $this->actingAs($akuntan)
            ->get(route('kepala-dapur.dashboard'))
            ->assertForbidden();
    }

    public function test_dashboard_displays_canonical_seed_values(): void
    {
        $this->actingAs($this->kepalaDapur)
            ->get(route('kepala-dapur.dashboard'))
            ->assertOk()
            ->assertSee('Hendra Wijaya', false)
            ->assertSee('0 menu · 1 koreksi menunggu', false)
            ->assertSee('1 temuan terbuka', false)
            ->assertSee('2/7 terkonfirmasi diterima', false)
            ->assertSee('Tidak ada menu yang menunggu persetujuan.', false);
    }

    public function test_menu_decision_workflow_and_demo_advance(): void
    {
        // Sebelum demo:advance, tab menunggu kosong; menu 28 Nov masih menunggu_pemeriksaan_anggaran -> 409
        $menu28 = DailyMenu::whereDate('operational_date', '2026-11-28')->firstOrFail();
        $this->actingAs($this->kepalaDapur)
            ->post(route('kepala-dapur.persetujuan.menu.decide', $menu28), [
                'decision' => 'setujui',
            ])
            ->assertStatus(409);

        $this->artisan('demo:advance', ['scenario' => 'akuntan-lolos-28nov'])
            ->assertExitCode(0);

        $this->actingAs($this->kepalaDapur)
            ->get(route('kepala-dapur.persetujuan.index', ['tab' => 'menunggu']))
            ->assertOk()
            ->assertSee($menu28->name, false);

        // Minta revisi tanpa catatan -> 422
        $this->actingAs($this->kepalaDapur)
            ->post(route('kepala-dapur.persetujuan.menu.decide', $menu28), [
                'decision' => 'minta_revisi',
                'catatan' => '',
            ])
            ->assertSessionHasErrors('catatan');

        // Setujui menu lolos_pagu -> disetujui + locked_at + audit log
        $beforeAudit = AuditLog::count();
        $this->actingAs($this->kepalaDapur)
            ->post(route('kepala-dapur.persetujuan.menu.decide', $menu28), [
                'decision' => 'setujui',
                'catatan' => 'Siap produksi 28 Nov.',
            ])
            ->assertRedirect(route('kepala-dapur.persetujuan.index', ['tab' => 'menunggu']));

        $menu28->refresh();
        $this->assertSame(MenuStatus::DISETUJUI->value, $menu28->status);
        $this->assertNotNull($menu28->locked_at);
        $this->assertSame($beforeAudit + 1, AuditLog::count());
    }

    public function test_locked_data_revision_approve_and_reject(): void
    {
        $revision = DataRevision::where('status', 'menunggu')->firstOrFail();

        // Tolak tanpa alasan -> 422
        $this->actingAs($this->kepalaDapur)
            ->post(route('kepala-dapur.persetujuan.koreksi.reject', $revision), [
                'decision_notes' => 'abc',
            ])
            ->assertSessionHasErrors('decision_notes');

        // Setujui koreksi -> terapkan nilai baru + audit log old/new
        $this->actingAs($this->kepalaDapur)
            ->post(route('kepala-dapur.persetujuan.koreksi.approve', $revision))
            ->assertRedirect(route('kepala-dapur.persetujuan.index', ['tab' => 'koreksi']));

        $revision->refresh();
        $this->assertSame('disetujui', $revision->status);
        $this->assertNotNull($revision->approved_at);

        $updatedMenu = DailyMenu::findOrFail($revision->revisable_id);
        $this->assertSame('Buncis dan wortel', $updatedMenu->components_json['sayur'] ?? null);

        $audit = AuditLog::latest('id')->first();
        $this->assertSame(['Sayur' => 'Buncis'], $audit->old_values);
        $this->assertSame(['Sayur' => 'Buncis dan wortel'], $audit->new_values);
    }

    public function test_tindak_lanjut_findings_complaints_and_readonly_obstacles(): void
    {
        $finding = FoodSafetyFinding::where('status', 'terbuka')->firstOrFail();

        // Tanpa tindakan -> 422
        $this->actingAs($this->kepalaDapur)
            ->post(route('kepala-dapur.tindak-lanjut.temuan.resolve', $finding), [
                'handler_note' => 'kurang',
            ])
            ->assertSessionHasErrors('handler_note');

        // Valid -> ditindaklanjuti
        $this->actingAs($this->kepalaDapur)
            ->post(route('kepala-dapur.tindak-lanjut.temuan.resolve', $finding), [
                'handler_note' => 'Memanaskan ulang makanan siap saji hingga >= 65 derajat C.',
            ])
            ->assertRedirect(route('kepala-dapur.tindak-lanjut.index', ['tab' => 'temuan']));

        $this->assertSame('ditindaklanjuti', $finding->fresh()->status);

        // Keluhan non-serius tidak bisa ditindaklanjuti Kepala Dapur (403)
        $normalTicket = ComplaintTicket::whereNotIn('severity', ['kritis', 'serius'])->firstOrFail();
        $this->actingAs($this->kepalaDapur)
            ->post(route('kepala-dapur.tindak-lanjut.keluhan.handle', $normalTicket), [
                'status' => 'resolved',
                'resolution_notes' => 'Coba tangani keluhan normal.',
            ])
            ->assertForbidden();

        // Kendala produksi tidak punya route tulis untuk Kepala Dapur
        $this->assertFalse(Route::has('kepala-dapur.tindak-lanjut.kendala.store'));
    }

    public function test_laporan_preview_and_exports_pdf_excel_csv(): void
    {
        $this->actingAs($this->kepalaDapur)
            ->get(route('kepala-dapur.laporan.index'))
            ->assertOk()
            ->assertSee('Laporan Operasional Harian', false);

        $this->actingAs($this->kepalaDapur)
            ->get(route('kepala-dapur.laporan.export.pdf'))
            ->assertOk();

        $this->actingAs($this->kepalaDapur)
            ->get(route('kepala-dapur.laporan.export.excel'))
            ->assertOk();

        $this->actingAs($this->kepalaDapur)
            ->get(route('kepala-dapur.laporan.export.csv'))
            ->assertOk();
    }
}
