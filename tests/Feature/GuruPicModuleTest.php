<?php

namespace Tests\Feature;

use App\Enums\DeliveryStatus;
use App\Enums\UserRole;
use App\Models\BeneficiaryInstitution;
use App\Models\ComplaintTicket;
use App\Models\DeliveryRoute;
use App\Models\Location;
use App\Models\User;
use Database\Seeders\AhliGiziSeeder;
use Database\Seeders\CanonicalAlignmentSeeder;
use Database\Seeders\GuruPicSeeder;
use Database\Seeders\RekagiziAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GuruPicModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $guruPic;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RekagiziAdminSeeder::class, AhliGiziSeeder::class, GuruPicSeeder::class, CanonicalAlignmentSeeder::class]);

        $this->guruPic = User::where('username', 'rina.marlina')->firstOrFail();
        $this->guruPic->forceFill(['must_change_password' => false])->saveQuietly();
    }

    public function test_guru_pic_dashboard_redirect_and_single_school_scope(): void
    {
        $ahliGizi = User::where('role', UserRole::AHLI_GIZI->value)->where('is_active', true)->firstOrFail();
        $ahliGizi->forceFill(['must_change_password' => false])->saveQuietly();

        $this->actingAs($ahliGizi)
            ->get(route('guru-pic.dashboard'))
            ->assertForbidden();

        $this->actingAs($this->guruPic)
            ->get(route('dashboard'))
            ->assertRedirect(route('guru-pic.dashboard'));

        // Buat tiket keluhan untuk sekolah lain untuk memastikan isolasi data
        $otherLocation = Location::where('name', '!=', 'Pesantren Al-Falah Palembang')->firstOrFail();
        $institution = BeneficiaryInstitution::firstOrFail();
        ComplaintTicket::create([
            'ticket_number' => '#KLP-OTHER-99',
            'beneficiary_institution_id' => $institution->id,
            'location_id' => $otherLocation->id,
            'reported_by' => $this->guruPic->id,
            'category' => 'keterlambatan',
            'severity' => 'rendah',
            'description' => 'Keluhan rahasia sekolah lain tidak boleh tampil.',
            'status' => 'open',
            'reported_at' => now('Asia/Jakarta'),
        ]);

        $this->actingAs($this->guruPic)
            ->get(route('guru-pic.dashboard'))
            ->assertOk()
            ->assertSee('Pesantren Al-Falah', false)
            ->assertSee('350', false)
            ->assertSee('Telat 10 mnt', false)
            ->assertSee('1 Aktif', false)
            ->assertSee('Menu Hari Ini', false)
            ->assertSee('Jejak Pengiriman Hari Ini', false)
            ->assertDontSee('Keluhan rahasia sekolah lain tidak boleh tampil.', false)
            ->assertDontSee('Koneksi stabil', false)
            ->assertDontSee('notifications', false);
    }

    public function test_confirmation_and_organoleptic_validation_and_save(): void
    {
        $this->actingAs($this->guruPic)
            ->get(route('guru-pic.confirmation.index'))
            ->assertOk()
            ->assertSee('Konfirmasi Jumlah', false)
            ->assertSee('Formulir Uji Organoleptik Digital', false);

        // Melebihi porsi dikirim (350) harus ditolak
        $this->actingAs($this->guruPic)
            ->post(route('guru-pic.confirmation.store'), [
                'confirmed_portions' => 360,
                'arrival_time' => '08:00',
                'packaging_condition' => 'baik',
                'organoleptic_taste' => 'normal',
                'organoleptic_aroma' => 'normal',
                'organoleptic_texture' => 'normal',
                'organoleptic_color' => 'normal',
            ])
            ->assertSessionHasErrors('confirmed_portions');

        // Organoleptik wajib diisi
        $this->actingAs($this->guruPic)
            ->post(route('guru-pic.confirmation.store'), [
                'confirmed_portions' => 350,
                'arrival_time' => '08:00',
                'packaging_condition' => 'baik',
            ])
            ->assertSessionHasErrors(['organoleptic_taste', 'organoleptic_aroma', 'organoleptic_texture', 'organoleptic_color']);

        // Simpan valid (350 porsi, 08:00, baik, semua normal)
        $this->actingAs($this->guruPic)
            ->post(route('guru-pic.confirmation.store'), [
                'confirmed_portions' => 350,
                'arrival_time' => '08:00',
                'packaging_condition' => 'baik',
                'notes' => 'Diterima tepat waktu dan lengkap.',
                'organoleptic_taste' => 'normal',
                'organoleptic_aroma' => 'normal',
                'organoleptic_texture' => 'normal',
                'organoleptic_color' => 'normal',
            ])
            ->assertRedirect(route('guru-pic.confirmation.index'))
            ->assertSessionHas('success');

        $route = DeliveryRoute::where('location_id', $this->guruPic->location_id)
            ->orderByDesc('operational_date')
            ->firstOrFail();

        $this->assertSame(350, $route->confirmed_portions);
        $this->assertSame('Layak', $route->organoleptic_status);
        $this->assertSame(DeliveryStatus::DITERIMA, $route->status);
    }

    public function test_complaint_creation_validation_file_limit_and_priority_notification(): void
    {
        Storage::fake('local');

        $this->actingAs($this->guruPic)
            ->get(route('guru-pic.complaints.index'))
            ->assertOk()
            ->assertSee('Daftar Tiket Keluhan Pesantren Al-Falah', false)
            ->assertSee('1 foto bukti terlampir', false);

        // File > 2 MB harus ditolak
        $oversizedFile = UploadedFile::fake()->create('bukti-besar.jpg', 2500, 'image/jpeg');
        $this->actingAs($this->guruPic)
            ->post(route('guru-pic.complaints.store'), [
                'category' => 'kemasan_tumpah',
                'priority' => 'serius',
                'description' => 'Lampiran terlalu besar untuk diuji.',
                'photo' => $oversizedFile,
            ])
            ->assertSessionHasErrors('photo');

        // Simpan valid dengan prioritas Serius dan foto <= 2 MB
        $validPhoto = UploadedFile::fake()->image('bukti-ompreng.jpg', 400, 300);
        $this->actingAs($this->guruPic)
            ->post(route('guru-pic.complaints.store'), [
                'category' => 'kemasan_tumpah',
                'priority' => 'serius',
                'description' => 'Tutup ompreng bergeser di 3 wadah saat diterima.',
                'photo' => $validPhoto,
            ])
            ->assertRedirect(route('guru-pic.complaints.index'))
            ->assertSessionHas('success');

        $created = ComplaintTicket::where('description', 'Tutup ompreng bergeser di 3 wadah saat diterima.')->firstOrFail();
        $this->assertStringStartsWith('#KLP-', $created->ticket_number);
        $this->assertSame('kritis', $created->severity);
        $this->assertNotNull($created->priority_notified_at);
        $this->assertStringContainsString('Asisten Lapangan', (string) $created->priority_recipients);
        $this->assertStringContainsString('Kepala Dapur', (string) $created->priority_recipients);
    }
}
