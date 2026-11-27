<?php

namespace Tests\Feature;

use App\Enums\IngredientCategory;
use App\Enums\SopChecklistType;
use App\Enums\UserRole;
use App\Models\PaguParameter;
use App\Models\RecipientCategory;
use App\Models\Supplier;
use App\Models\User;
use App\Services\DataMasterCompletenessService;
use Database\Seeders\RekagiziAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class StageFourDataMasterTest extends TestCase
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

    public function test_data_master_has_no_destroy_routes(): void
    {
        $hasDelete = collect(Route::getRoutes())->contains(
            fn ($route): bool => str_starts_with($route->uri(), 'admin/data-master')
                && in_array('DELETE', $route->methods(), true)
        );

        $this->assertFalse($hasDelete);
    }

    public function test_data_master_overview_and_all_seven_pages_render(): void
    {
        $this->actingAs($this->admin)->get(route('admin.data-master.index'))
            ->assertOk()
            ->assertSee('5 dari 7', false);

        foreach ([
            'admin.data-master.lokasi.index',
            'admin.data-master.kategori.index',
            'admin.data-master.standar-porsi.index',
            'admin.data-master.bahan.index',
            'admin.data-master.pemasok.index',
            'admin.data-master.pagu.index',
            'admin.data-master.checklist-sop.index',
        ] as $routeName) {
            $this->actingAs($this->admin)->get(route($routeName))->assertOk();
        }
    }

    public function test_completing_suppliers_and_sop_kebersihan_makes_completeness_seven_of_seven(): void
    {
        $suppliers = Supplier::whereNull('contact_person')->orWhereNull('phone')->get();
        foreach ($suppliers as $idx => $supplier) {
            $this->actingAs($this->admin)->put(route('admin.data-master.pemasok.update', $supplier), [
                'code' => $supplier->code,
                'name' => $supplier->name,
                'contact_person' => "Kontak {$idx}",
                'phone' => "0812000000{$idx}",
                'address' => $supplier->address,
                'category_focus' => $supplier->category_focus,
                'reason' => 'Melengkapi kontak pemasok',
            ])->assertRedirect(route('admin.data-master.pemasok.index'));
        }

        $this->actingAs($this->admin)->post(route('admin.data-master.checklist-sop.store'), [
            'code' => 'SOP-KBR-01',
            'type' => SopChecklistType::KEBERSIHAN->value,
            'title' => 'Sanitasi Meja Persiapan',
            'threshold_value' => 'Bersih & kering sebelum produksi',
            'frequency' => 'Harian',
        ])->assertRedirect(route('admin.data-master.checklist-sop.index'));

        $eval = app(DataMasterCompletenessService::class)->evaluate();
        $this->assertSame(7, $eval['siap_count']);
    }

    public function test_location_quotas_must_match_target_portions(): void
    {
        $category = RecipientCategory::firstOrFail();

        $this->actingAs($this->admin)->post(route('admin.data-master.lokasi.store'), [
            'code' => 'LOC-TEST-01',
            'name' => 'SD Uji',
            'type' => 'Sekolah',
            'target_portions' => 100,
            'quotas' => [$category->id => 60],
        ])->assertSessionHasErrors('quotas');
    }

    public function test_pagu_update_with_reason_records_data_revision_and_deactivate_activate_work(): void
    {
        $pagu = PaguParameter::firstOrFail();

        $this->actingAs($this->admin)->put(route('admin.data-master.pagu.update', $pagu), [
            'recipient_category_id' => $pagu->recipient_category_id,
            'max_budget_per_portion' => 16000,
            'effective_date' => now()->toDateString(),
            'notes' => 'Penyesuaian indeks pangan',
            'reason' => 'Revisi pagu triwulan',
        ])->assertRedirect(route('admin.data-master.pagu.index'));

        $this->assertDatabaseHas('data_revisions', [
            'revisable_type' => PaguParameter::class,
            'revisable_id' => $pagu->id,
            'reason' => 'Revisi pagu triwulan',
        ]);

        $this->actingAs($this->admin)->patch(route('admin.data-master.pagu.deactivate', $pagu))
            ->assertRedirect(route('admin.data-master.pagu.index'));
        $this->assertFalse($pagu->fresh()->is_active);

        $this->actingAs($this->admin)->patch(route('admin.data-master.pagu.activate', $pagu))
            ->assertRedirect(route('admin.data-master.pagu.index'));
        $this->assertTrue($pagu->fresh()->is_active);
    }

    public function test_portion_standard_and_ingredient_store_follow_enum_rules(): void
    {
        $category = RecipientCategory::firstOrFail();

        $this->actingAs($this->admin)->post(route('admin.data-master.standar-porsi.store'), [
            'recipient_category_id' => $category->id,
            'meal_component' => IngredientCategory::MINUMAN->value,
            'quantity' => 200,
            'unit' => 'ml',
        ])->assertRedirect(route('admin.data-master.standar-porsi.index'));

        $this->actingAs($this->admin)->post(route('admin.data-master.bahan.store'), [
            'name' => 'Susu UHT Putih',
            'category' => IngredientCategory::MINUMAN->value,
            'unit' => 'ml',
            'current_stock' => 1000,
            'unit_cost' => 3500,
        ])->assertRedirect(route('admin.data-master.bahan.index'));

        $this->assertDatabaseHas('portion_standards', ['meal_component' => 'Minuman', 'unit' => 'ml']);
        $this->assertDatabaseHas('ingredients', ['name' => 'Susu UHT Putih', 'category' => 'Minuman']);
    }

    public function test_non_admin_cannot_access_data_master(): void
    {
        $nonAdmin = User::where('role', UserRole::KEPALA_DAPUR->value)->firstOrFail();
        $nonAdmin->forceFill(['must_change_password' => false, 'password_expires_at' => null])->saveQuietly();

        $this->actingAs($nonAdmin)->get(route('admin.data-master.index'))->assertForbidden();
    }
}
