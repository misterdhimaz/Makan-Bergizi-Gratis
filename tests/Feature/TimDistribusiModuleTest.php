<?php

namespace Tests\Feature;

use App\Enums\DeliveryStatus;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\DeliveryDocument;
use App\Models\DeliveryRoute;
use App\Models\DeliveryStatusLog;
use App\Models\User;
use App\Services\DeliveryMetrics;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TimDistribusiModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $distribusi;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->distribusi = User::where('username', 'fajar.nugroho')->firstOrFail();
    }

    public function test_role_access_and_boundaries(): void
    {
        $this->get(route('distribusi.dashboard'))
            ->assertRedirect(route('login'));

        $akuntan = User::where('role', UserRole::AKUNTAN->value)->where('is_active', true)->firstOrFail();
        $this->actingAs($akuntan)
            ->get(route('distribusi.dashboard'))
            ->assertForbidden();

        // Tim Distribusi tidak boleh membuka rute menu, stok, keuangan, atau konfirmasi penerimaan
        $this->actingAs($this->distribusi)->get(route('akuntan.dashboard'))->assertForbidden();
        $this->actingAs($this->distribusi)->get(route('kepala-dapur.dashboard'))->assertForbidden();
        $this->actingAs($this->distribusi)->get(route('aslap.dashboard'))->assertForbidden();
        $this->actingAs($this->distribusi)->get(route('guru-pic.confirmation.index'))->assertRedirect(route('dashboard'));
    }

    public function test_valid_transitions_update_delivery_metrics_and_logs(): void
    {
        Storage::fake('local');

        $sd132 = DeliveryRoute::where('waybill_number', 'DST-20261126-07')->firstOrFail();
        $this->assertSame(DeliveryStatus::DISIAPKAN, $sd132->status);

        $beforeLogs = DeliveryStatusLog::count();
        $beforeAudit = AuditLog::count();

        // Transisi Disiapkan -> Berangkat untuk SD Negeri 132 (244 porsi)
        $this->actingAs($this->distribusi)
            ->post(route('distribusi.pengiriman.update', $sd132), [
                'status' => DeliveryStatus::BERANGKAT->value,
                'portions' => 244,
                'event_time' => '2026-11-26 08:45',
                'catatan_lapangan' => 'Berangkat tepat waktu.',
            ])
            ->assertRedirect(route('distribusi.pengiriman.index'));

        $sd132->refresh();
        $this->assertSame(DeliveryStatus::BERANGKAT, $sd132->status);
        $this->assertSame($beforeLogs + 1, DeliveryStatusLog::count());
        $this->assertSame($beforeAudit + 1, AuditLog::count());

        // Verifikasi DeliveryMetrics: dikirim 1.170 -> 1.414, belum_dikonfirmasi 850 -> 1.094
        $metrics = app(DeliveryMetrics::class)->forDate();
        $this->assertSame(1414, $metrics['dikirim']);
        $this->assertSame(1094, $metrics['belum_dikonfirmasi']);
        $this->assertSame(318, $metrics['diterima']);
        $this->assertSame(2, $metrics['titik_terkonfirmasi']);
        $this->assertSame(-2, $metrics['selisih_terkonfirmasi']);

        // Transisi Berangkat -> Tiba dengan bukti JPG valid
        $file = UploadedFile::fake()->image('bukti-serah.jpg', 400, 400);
        $this->actingAs($this->distribusi)
            ->post(route('distribusi.pengiriman.update', $sd132), [
                'status' => DeliveryStatus::TIBA->value,
                'portions' => 244,
                'nama_penerima_serah' => 'Siti Aminah',
                'event_time' => '2026-11-26 08:50',
                'document' => $file,
            ])
            ->assertRedirect(route('distribusi.pengiriman.index'));

        $sd132->refresh();
        $this->assertSame(DeliveryStatus::TIBA, $sd132->status);
        $this->assertSame('Siti Aminah', $sd132->nama_penerima_serah);

        $doc = DeliveryDocument::where('delivery_route_id', $sd132->id)->firstOrFail();
        Storage::disk('local')->assertExists($doc->path);

        // Unduh oleh role berhak berhasil; role tidak berhak ditolak 403
        $this->actingAs($this->distribusi)->get(route('distribusi.dokumen.download', $doc))->assertOk();
        $akuntan = User::where('role', UserRole::AKUNTAN->value)->where('is_active', true)->firstOrFail();
        $this->actingAs($akuntan)->get(route('distribusi.dokumen.download', $doc))->assertForbidden();
    }

    public function test_invalid_transitions_and_validation_rules(): void
    {
        Storage::fake('local');

        $sd132 = DeliveryRoute::where('waybill_number', 'DST-20261126-07')->firstOrFail();

        // Lompat Disiapkan -> Tiba ditolak
        $this->actingAs($this->distribusi)
            ->post(route('distribusi.pengiriman.update', $sd132), [
                'status' => DeliveryStatus::TIBA->value,
                'portions' => 244,
                'nama_penerima_serah' => 'Siti Aminah',
            ])
            ->assertSessionHasErrors('status');

        // Set Diterima ditolak
        $this->actingAs($this->distribusi)
            ->post(route('distribusi.pengiriman.update', $sd132), [
                'status' => DeliveryStatus::DITERIMA->value,
                'portions' => 244,
            ])
            ->assertSessionHasErrors('status');

        // Jumlah di luar rentang -> 422
        $this->actingAs($this->distribusi)
            ->post(route('distribusi.pengiriman.update', $sd132), [
                'status' => DeliveryStatus::BERANGKAT->value,
                'portions' => 999,
            ])
            ->assertSessionHasErrors('portions');

        // File > 2 MB atau MIME salah ditolak
        $bigFile = UploadedFile::fake()->create('besar.pdf', 2500, 'application/pdf');
        $this->actingAs($this->distribusi)
            ->post(route('distribusi.pengiriman.update', $sd132), [
                'status' => DeliveryStatus::BERANGKAT->value,
                'portions' => 244,
                'document' => $bigFile,
            ])
            ->assertSessionHasErrors('document');

        // Mundur Tiba -> Berangkat ditolak
        $melati = DeliveryRoute::where('waybill_number', 'DST-20261126-03')->firstOrFail();
        $this->actingAs($this->distribusi)
            ->post(route('distribusi.pengiriman.update', $melati), [
                'status' => DeliveryStatus::BERANGKAT->value,
                'portions' => 70,
            ])
            ->assertSessionHasErrors('status');

        // Nama penerima wajib saat Tiba
        $sd130 = DeliveryRoute::where('waybill_number', 'DST-20261126-05')->firstOrFail();
        $this->actingAs($this->distribusi)
            ->post(route('distribusi.pengiriman.update', $sd130), [
                'status' => DeliveryStatus::TIBA->value,
                'portions' => 220,
                'nama_penerima_serah' => '',
            ])
            ->assertSessionHasErrors('nama_penerima_serah');
    }

    public function test_riwayat_and_kendala_page(): void
    {
        $this->actingAs($this->distribusi)
            ->get(route('distribusi.riwayat.index'))
            ->assertOk()
            ->assertSee('KLP-20261126-04', false)
            ->assertSee('Menyiapkan 4 porsi pengganti', false);
    }
}
