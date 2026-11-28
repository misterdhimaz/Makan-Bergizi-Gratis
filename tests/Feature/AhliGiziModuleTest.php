<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\DailyMenu;
use App\Models\DailyMenuItem;
use App\Models\InventoryTransaction;
use App\Models\User;
use Database\Seeders\AhliGiziSeeder;
use Database\Seeders\RekagiziAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AhliGiziModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $ahliGizi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RekagiziAdminSeeder::class, AhliGiziSeeder::class]);

        $this->ahliGizi = User::where('role', UserRole::AHLI_GIZI->value)
            ->where('is_active', true)
            ->firstOrFail();
        $this->ahliGizi->forceFill(['must_change_password' => false])->saveQuietly();
    }

    public function test_ahli_gizi_can_open_dashboard_and_non_ahli_gizi_is_forbidden(): void
    {
        $admin = User::where('role', UserRole::ADMIN->value)->where('is_active', true)->firstOrFail();
        $admin->forceFill(['must_change_password' => false])->saveQuietly();

        $this->actingAs($admin)
            ->get(route('ahli-gizi.dashboard'))
            ->assertRedirect(route('dashboard'));

        $this->actingAs($this->ahliGizi)
            ->get(route('ahli-gizi.dashboard'))
            ->assertOk()
            ->assertSee('Selamat', false)
            ->assertSee('Nadia', false)
            ->assertSee('Beras -1 kg · Tahu -2 potong', false)
            ->assertSee('tumis buncis tumpah pada 4 ompreng saat pengantaran pagi', false)
            ->assertDontSee('HACCP', false)
            ->assertDontSee('kuah', false);
    }

    public function test_dashboard_request_revision_requires_reason_and_updates_status(): void
    {
        $approvedMenu = DailyMenu::where('status', 'disetujui')->firstOrFail();

        $this->actingAs($this->ahliGizi)
            ->post(route('ahli-gizi.menu.request-revision', $approvedMenu), [
                'reason' => '',
            ])
            ->assertSessionHasErrors('reason');

        $this->actingAs($this->ahliGizi)
            ->post(route('ahli-gizi.menu.request-revision', $approvedMenu), [
                'reason' => 'Stok buncis di pasar berkurang, perlu penyesuaian menu.',
            ])
            ->assertRedirect();

        $approvedMenu->refresh();
        $this->assertSame('Stok buncis di pasar berkurang, perlu penyesuaian menu.', $approvedMenu->revision_reason);
        $this->assertDatabaseHas('data_revisions', [
            'revisable_id' => $approvedMenu->id,
            'reason' => 'Stok buncis di pasar berkurang, perlu penyesuaian menu.',
        ]);
    }

    public function test_menu_planning_draft_edit_validation_and_approved_lock(): void
    {
        $this->actingAs($this->ahliGizi)
            ->get(route('ahli-gizi.menu.index'))
            ->assertOk()
            ->assertSee('Perencanaan Menu', false)
            ->assertSee('Kepala Dapur:', false);

        $draftMenu = DailyMenu::where('status', 'draf')->firstOrFail();
        $approvedMenu = DailyMenu::where('status', 'disetujui')->firstOrFail();

        // Target porsi minimal 1
        $this->actingAs($this->ahliGizi)
            ->put(route('ahli-gizi.menu.update', $draftMenu), [
                'operational_date' => $draftMenu->operational_date->toDateString(),
                'menu_name' => 'Menu Draf Uji',
                'target_portions' => 0,
            ])
            ->assertSessionHasErrors('target_portions');

        // Berhasil ubah dan ajukan draf menu
        $this->actingAs($this->ahliGizi)
            ->put(route('ahli-gizi.menu.update', $draftMenu), [
                'operational_date' => $draftMenu->operational_date->toDateString(),
                'menu_name' => 'Nasi + Ayam Panggang + Tumis Wortel',
                'target_portions' => 1450,
                'categories' => ['Siswa'],
                'submit_action' => 'submit',
            ])
            ->assertRedirect(route('ahli-gizi.menu.index'));

        $draftMenu->refresh();
        $this->assertSame('menunggu_pemeriksaan_anggaran', $draftMenu->status);
        $this->assertSame(1450, (int) $draftMenu->target_portions);

        // Menu Disetujui terkunci dan tidak bisa diedit langsung
        $this->actingAs($this->ahliGizi)
            ->put(route('ahli-gizi.menu.update', $approvedMenu), [
                'operational_date' => $approvedMenu->operational_date->toDateString(),
                'menu_name' => 'Coba Ubah Paksa',
                'target_portions' => 500,
            ])
            ->assertSessionHas('error');

        $approvedMenu->refresh();
        $this->assertNotSame('Coba Ubah Paksa', $approvedMenu->menu_name);
    }

    public function test_inventory_portion_verification_realization_ompreng_and_obstacles(): void
    {
        $this->actingAs($this->ahliGizi)
            ->get(route('ahli-gizi.inventory.index'))
            ->assertOk()
            ->assertSee('Persediaan', false)
            ->assertSee('Asisten Lapangan', false);

        $inbound = InventoryTransaction::where('type', 'inbound_purchase')->firstOrFail();

        $this->actingAs($this->ahliGizi)
            ->post(route('ahli-gizi.inventory.verify', $inbound), [
                'verification_status' => 'sesuai',
                'verification_notes' => 'Jumlah dan mutu bahan sesuai standar.',
            ])
            ->assertRedirect();

        $inbound->refresh();
        $this->assertSame('sesuai', $inbound->verification_status);

        $menuItem = DailyMenuItem::firstOrFail();

        $this->actingAs($this->ahliGizi)
            ->post(route('ahli-gizi.inventory.realization.store'), [
                'items' => [
                    [
                        'id' => $menuItem->id,
                        'dispatched_qty' => 145,
                        'actual_used_qty' => 144,
                    ],
                ],
            ])
            ->assertRedirect();

        $menuItem->refresh();
        $this->assertSame(144.0, (float) $menuItem->actual_used_qty);

        // Validasi ompreng tidak boleh melebihi target porsi
        $menu = DailyMenu::findOrFail($menuItem->daily_menu_id);
        $this->actingAs($this->ahliGizi)
            ->post(route('ahli-gizi.inventory.ompreng.store'), [
                'daily_menu_id' => $menu->id,
                'actual_good_portions' => (int) $menu->target_portions + 50,
            ])
            ->assertSessionHasErrors('actual_good_portions');

        $this->actingAs($this->ahliGizi)
            ->post(route('ahli-gizi.inventory.ompreng.store'), [
                'daily_menu_id' => $menu->id,
                'actual_good_portions' => (int) $menu->target_portions,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('production_runs', [
            'daily_menu_id' => $menu->id,
            'actual_good_portions' => (int) $menu->target_portions,
        ]);

        // Tambah kendala
        $this->actingAs($this->ahliGizi)
            ->post(route('ahli-gizi.inventory.obstacles.store'), [
                'operational_date' => '2026-11-26',
                'daily_menu_id' => $menu->id,
                'description' => 'Air bersih sempat melambat di area cuci bahan.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('production_obstacles', [
            'description' => 'Air bersih sempat melambat di area cuci bahan.',
        ]);
    }

    public function test_food_safety_check_validation_and_save(): void
    {
        $this->actingAs($this->ahliGizi)
            ->get(route('ahli-gizi.safety.index'))
            ->assertOk()
            ->assertSee('Keamanan Pangan', false)
            ->assertSee('13:00 WIB', false)
            ->assertSee('Hendra Wijaya', false)
            ->assertDontSee('HACCP', false);

        $today = now('Asia/Jakarta')->toDateString();

        $this->actingAs($this->ahliGizi)
            ->post(route('ahli-gizi.safety.store'), [
                'check_date' => $today,
                'shift' => 'Siang',
                'compliant_volunteers' => 40,
                'temps' => [
                    ['label' => 'Bahan baku dingin / kulkas', 'standard' => '0 – 4 °C', 'actual' => 3.1, 'compliant' => true],
                ],
            ])
            ->assertSessionHasErrors('compliant_volunteers');

        $this->actingAs($this->ahliGizi)
            ->post(route('ahli-gizi.safety.store'), [
                'check_date' => $today,
                'shift' => 'Siang',
                'compliant_volunteers' => 34,
                'notes' => 'Pemeriksaan shift siang lengkap.',
                'temps' => [
                    ['label' => 'Bahan baku dingin / kulkas', 'standard' => '0 – 4 °C', 'actual' => 3.1, 'compliant' => true],
                ],
                'apds' => [
                    ['label' => 'Celemek bersih', 'compliant' => true],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('food_safety_checks', [
            'check_date' => $today.' 00:00:00',
            'shift' => 'Siang',
            'compliant_volunteers' => 34,
        ]);
    }

    public function test_readonly_pages_render_for_ahli_gizi(): void
    {
        foreach ([
            'ahli-gizi.readonly.distribusi',
            'ahli-gizi.readonly.keluhan',
            'ahli-gizi.readonly.laporan',
            'ahli-gizi.readonly.data-master',
        ] as $routeName) {
            $this->actingAs($this->ahliGizi)
                ->get(route($routeName))
                ->assertOk()
                ->assertSee('Hanya lihat', false)
                ->assertDontSee('kuah', false)
                ->assertDontSee('HACCP', false);
        }
    }
}
