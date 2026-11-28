<?php

namespace Tests\Feature;

use App\Enums\MenuStatus;
use App\Enums\PaguCheckResult;
use App\Enums\UserRole;
use App\Exceptions\MenuStatusConflictException;
use App\Models\DailyMenu;
use App\Models\PaguCheck;
use App\Models\PaguCheckEstimate;
use App\Models\PaguParameter;
use App\Models\PaguParameterHistory;
use App\Models\RecipientCategory;
use App\Models\User;
use App\Services\MenuWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class AkuntanModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $akuntan;

    private User $admin;

    private User $ahliGizi;

    private User $kepalaDapur;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->akuntan = User::where('role', UserRole::AKUNTAN->value)->firstOrFail();
        $this->akuntan->forceFill(['must_change_password' => false])->saveQuietly();
        $this->admin = User::where('role', UserRole::ADMIN->value)->firstOrFail();
        $this->admin->forceFill(['must_change_password' => false])->saveQuietly();
        $this->ahliGizi = User::where('role', UserRole::AHLI_GIZI->value)->firstOrFail();
        $this->ahliGizi->forceFill(['must_change_password' => false])->saveQuietly();
        $this->kepalaDapur = User::where('role', UserRole::KEPALA_DAPUR->value)->firstOrFail();
        $this->kepalaDapur->forceFill(['must_change_password' => false])->saveQuietly();
    }

    public function test_guest_is_redirected_to_login_from_akuntan_routes(): void
    {
        $this->get('/akuntan/dashboard')->assertRedirect('/masuk');
        $this->get('/akuntan/validasi')->assertRedirect('/masuk');
        $this->get('/akuntan/parameter-pagu')->assertRedirect('/masuk');
        $this->get('/akuntan/laporan')->assertRedirect('/masuk');
    }

    public function test_non_akuntan_roles_receive_403_on_akuntan_routes(): void
    {
        $menu = DailyMenu::where('status', MenuStatus::MENUNGGU_PEMERIKSAAN_ANGGARAN->value)->firstOrFail();
        $category = RecipientCategory::firstOrFail();

        foreach ([$this->admin, $this->ahliGizi, $this->kepalaDapur] as $user) {
            $this->actingAs($user)->get('/akuntan/dashboard')->assertForbidden();
            $this->actingAs($user)->get('/akuntan/validasi')->assertForbidden();
            $this->actingAs($user)->get('/akuntan/parameter-pagu')->assertForbidden();
            $this->actingAs($user)->get('/akuntan/laporan')->assertForbidden();
            $this->actingAs($user)->post('/akuntan/validasi/'.$menu->id, [
                'hasil' => 'lolos',
                'catatan' => 'Sesuai batas PAGU.',
            ])->assertForbidden();
            $this->actingAs($user)->put('/akuntan/parameter-pagu/'.$category->id, [
                'nominal_per_porsi' => 16000,
            ])->assertForbidden();
        }
    }

    public function test_akuntan_receives_403_on_other_role_routes(): void
    {
        $this->actingAs($this->akuntan)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($this->akuntan)->get('/ahli-gizi/dashboard')->assertRedirect('/dashboard');
        $this->actingAs($this->akuntan)->get('/guru-pic/dashboard')->assertRedirect('/dashboard');
    }

    public function test_akuntan_redirects_to_akuntan_dashboard_from_root_and_dashboard(): void
    {
        $this->actingAs($this->akuntan)->get('/')->assertRedirect('/akuntan/dashboard');
        $this->actingAs($this->akuntan)->get('/dashboard')->assertRedirect('/akuntan/dashboard');
    }

    public function test_dashboard_and_laporan_render_seeded_counts_without_export_buttons(): void
    {
        $dashboard = $this->actingAs($this->akuntan)->get('/akuntan/dashboard');
        $dashboard->assertOk()
            ->assertSee('Selamat pagi')
            ->assertSee('SPPG Sungai Selayur 1')
            ->assertSee('1.414');

        $laporan = $this->actingAs($this->akuntan)->get('/akuntan/laporan?periode=semua');
        $laporan->assertOk()
            ->assertSee('Laporan Pemeriksaan PAGU')
            ->assertSee('Mode Hanya Lihat')
            ->assertDontSee('Export')
            ->assertDontSee('Unduh');
    }

    public function test_akuntan_can_record_lolos_pagu_with_optional_estimates(): void
    {
        $menu = DailyMenu::where('status', MenuStatus::MENUNGGU_PEMERIKSAAN_ANGGARAN->value)->firstOrFail();
        $siswa = RecipientCategory::orderBy('id')->firstOrFail();

        $response = $this->actingAs($this->akuntan)->post('/akuntan/validasi/'.$menu->id, [
            'hasil' => PaguCheckResult::LOLOS->value,
            'catatan' => 'Sesuai batas PAGU.',
            'estimates' => [
                [
                    'recipient_category_id' => $siswa->id,
                    'estimasi_per_porsi' => 14200,
                ],
            ],
        ]);

        $response->assertRedirect();
        $menu->refresh();
        $this->assertSame(MenuStatus::LOLOS_PAGU, $menu->menuStatus());
        $this->assertDatabaseHas('pagu_checks', [
            'daily_menu_id' => $menu->id,
            'hasil' => PaguCheckResult::LOLOS->value,
            'catatan' => 'Sesuai batas PAGU.',
        ]);
        $this->assertDatabaseHas('pagu_check_estimates', [
            'recipient_category_id' => $siswa->id,
            'estimasi_per_porsi' => 14200,
        ]);
    }

    public function test_perlu_revisi_requires_catatan_minimum_5_characters(): void
    {
        $menu = DailyMenu::where('status', MenuStatus::MENUNGGU_PEMERIKSAAN_ANGGARAN->value)->firstOrFail();

        $response = $this->actingAs($this->akuntan)->post('/akuntan/validasi/'.$menu->id, [
            'hasil' => PaguCheckResult::PERLU_REVISI->value,
            'catatan' => 'mah',
        ]);

        $response->assertSessionHasErrors('catatan');
        $menu->refresh();
        $this->assertSame(MenuStatus::MENUNGGU_PEMERIKSAAN_ANGGARAN, $menu->menuStatus());

        $validResponse = $this->actingAs($this->akuntan)->post('/akuntan/validasi/'.$menu->id, [
            'hasil' => PaguCheckResult::PERLU_REVISI->value,
            'catatan' => 'Estimasi bahan melebihi batas PAGU harian.',
        ]);

        $validResponse->assertRedirect();
        $menu->refresh();
        $this->assertSame(MenuStatus::PERLU_REVISI_ANGGARAN, $menu->menuStatus());
    }

    public function test_cannot_double_check_already_checked_menu_and_pagu_check_is_immutable(): void
    {
        $menu = DailyMenu::where('status', MenuStatus::MENUNGGU_PEMERIKSAAN_ANGGARAN->value)->firstOrFail();

        $this->actingAs($this->akuntan)->post('/akuntan/validasi/'.$menu->id, [
            'hasil' => PaguCheckResult::LOLOS->value,
            'catatan' => 'Sesuai batas PAGU.',
        ])->assertRedirect();

        $secondAttempt = $this->actingAs($this->akuntan)
            ->postJson('/akuntan/validasi/'.$menu->id, [
                'hasil' => PaguCheckResult::PERLU_REVISI->value,
                'catatan' => 'Coba ubah hasil yang sudah dicatat.',
            ]);
        $secondAttempt->assertStatus(409);

        $check = PaguCheck::where('daily_menu_id', $menu->id)->firstOrFail();
        $this->expectException(RuntimeException::class);
        $check->update(['catatan' => 'Diubah']);
    }

    public function test_workflow_service_rejects_invalid_transitions(): void
    {
        $service = app(MenuWorkflowService::class);

        $this->expectException(MenuStatusConflictException::class);
        $service->assertTransitionAllowed(
            MenuStatus::DRAF,
            MenuStatus::DISETUJUI,
            UserRole::KEPALA_DAPUR,
        );
    }

    public function test_parameter_pagu_update_validates_positive_integer_and_preserves_old_snapshots(): void
    {
        $siswa = RecipientCategory::orderBy('id')->firstOrFail();
        $menu = DailyMenu::where('status', MenuStatus::MENUNGGU_PEMERIKSAAN_ANGGARAN->value)->firstOrFail();

        $this->actingAs($this->akuntan)->post('/akuntan/validasi/'.$menu->id, [
            'hasil' => PaguCheckResult::LOLOS->value,
            'catatan' => 'Sesuai batas PAGU.',
            'estimates' => [
                [
                    'recipient_category_id' => $siswa->id,
                    'estimasi_per_porsi' => 14000,
                ],
            ],
        ])->assertRedirect();

        $snapshotBefore = PaguCheckEstimate::where('recipient_category_id', $siswa->id)
            ->latest('id')
            ->firstOrFail();
        $oldPagu = $snapshotBefore->pagu_saat_itu;

        $this->actingAs($this->akuntan)->put('/akuntan/parameter-pagu/'.$siswa->id, [
            'nominal_per_porsi' => 0,
        ])->assertSessionHasErrors('nominal_per_porsi');

        $this->actingAs($this->akuntan)->put('/akuntan/parameter-pagu/'.$siswa->id, [
            'nominal_per_porsi' => 16500,
            'catatan' => 'Penyesuaian harga beras.',
        ])->assertRedirect();

        $activeParam = PaguParameter::where('recipient_category_id', $siswa->id)
            ->where('is_active', true)
            ->latest('id')
            ->firstOrFail();
        $this->assertSame(16500, (int) $activeParam->max_budget_per_portion);

        $this->assertDatabaseHas(PaguParameterHistory::class, [
            'recipient_category_id' => $siswa->id,
            'nilai_lama' => $oldPagu,
            'nilai_baru' => 16500,
        ]);

        $snapshotBefore->refresh();
        $this->assertSame($oldPagu, $snapshotBefore->pagu_saat_itu);
    }
}
