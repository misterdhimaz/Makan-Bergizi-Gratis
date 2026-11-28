<?php

namespace Tests\Feature;

use App\Enums\DeliveryStatus;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\ComplaintTicket;
use App\Models\DeliveryRoute;
use App\Models\InventoryTransaction;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AslapModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $aslap;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->aslap = User::where('username', 'bima.santoso')->firstOrFail();
    }

    public function test_role_access_and_boundaries(): void
    {
        $this->get(route('aslap.dashboard'))
            ->assertRedirect(route('login'));

        $akuntan = User::where('role', UserRole::AKUNTAN->value)->where('is_active', true)->firstOrFail();
        $this->actingAs($akuntan)
            ->get(route('aslap.dashboard'))
            ->assertForbidden();

        // Aslap tidak bisa membuka rute menu atau keamanan pangan atau keuangan
        $this->actingAs($this->aslap)
            ->get(route('akuntan.dashboard'))
            ->assertForbidden();
        $this->actingAs($this->aslap)
            ->get(route('kepala-dapur.dashboard'))
            ->assertForbidden();
    }

    public function test_dashboard_displays_canonical_seed_values(): void
    {
        $this->actingAs($this->aslap)
            ->get(route('aslap.dashboard'))
            ->assertOk()
            ->assertSee('Bima Santoso', false)
            ->assertSee('0 / 6', false)
            ->assertSee('7 rute', false)
            ->assertSee('2 / 7', false);
    }

    public function test_procurement_recording_validation_and_lock(): void
    {
        $tx = InventoryTransaction::where('procurement_status', 'menunggu_terima')
            ->where('verification_status', 'menunggu')
            ->firstOrFail();

        // Jumlah negatif ditolak
        $this->actingAs($this->aslap)
            ->post(route('aslap.pengadaan.update', $tx), [
                'quantity' => -5,
                'supplier_name' => 'CV Pangan Nusantara',
                'received_at' => '2026-11-26 08:30',
            ])
            ->assertSessionHasErrors('quantity');

        // Waktu masa depan ditolak
        $this->actingAs($this->aslap)
            ->post(route('aslap.pengadaan.update', $tx), [
                'quantity' => 50,
                'supplier_name' => 'CV Pangan Nusantara',
                'received_at' => '2026-11-27 10:00',
            ])
            ->assertSessionHasErrors('received_at');

        // Catat penerimaan sebagian
        $beforeAudit = AuditLog::count();
        $this->actingAs($this->aslap)
            ->post(route('aslap.pengadaan.update', $tx), [
                'quantity' => 50,
                'supplier_name' => 'CV Pangan Nusantara',
                'received_at' => '2026-11-26 08:30',
            ])
            ->assertRedirect();

        $tx->refresh();
        $this->assertSame(50.0, (float) $tx->quantity);
        $this->assertSame('diterima_sebagian', $tx->procurement_status);
        $this->assertSame($beforeAudit + 1, AuditLog::count());

        // Baris yang sudah diverifikasi Ahli Gizi terkunci (409)
        $verified = InventoryTransaction::where('verification_status', '!=', 'menunggu')->firstOrFail();
        $this->actingAs($this->aslap)
            ->post(route('aslap.pengadaan.update', $verified), [
                'quantity' => 100,
                'supplier_name' => 'CV Pangan Nusantara',
                'received_at' => '2026-11-26 08:30',
            ])
            ->assertStatus(409);
    }

    public function test_schedule_collision_validation_and_departed_lock(): void
    {
        $editableRoute = DeliveryRoute::whereDate('operational_date', '2026-11-26')
            ->where('status', DeliveryStatus::DISIAPKAN)
            ->firstOrFail();

        // Tabrakan petugas/kendaraan dengan DST-20261126-01 (Pak Andi, 07:35 - 08:00)
        $this->actingAs($this->aslap)
            ->patch(route('aslap.distribusi.update', $editableRoute), [
                'courier_name' => 'Pak Andi',
                'vehicle_plate' => 'BG 8119 KL',
                'planned_departure_time' => '07:40',
                'scheduled_arrival_time' => '08:10',
            ])
            ->assertSessionHasErrors('planned_departure_time');

        // Jadwal valid tanpa tabrakan
        $this->actingAs($this->aslap)
            ->patch(route('aslap.distribusi.update', $editableRoute), [
                'courier_name' => 'Pak Fajar',
                'vehicle_plate' => 'BG 8119 KL',
                'planned_departure_time' => '09:15',
                'scheduled_arrival_time' => '09:45',
            ])
            ->assertRedirect();

        $this->assertSame('09:15', $editableRoute->fresh()->planned_departure_time);

        // Rute yang sudah berangkat tidak bisa diubah (409)
        $departedRoute = DeliveryRoute::whereDate('operational_date', '2026-11-26')
            ->where('status', DeliveryStatus::BERANGKAT)
            ->firstOrFail();

        $this->actingAs($this->aslap)
            ->patch(route('aslap.distribusi.update', $departedRoute), [
                'courier_name' => 'Pak Dedi',
                'vehicle_plate' => 'BG 8112 KL',
                'planned_departure_time' => '10:00',
                'scheduled_arrival_time' => '10:30',
            ])
            ->assertStatus(409);
    }

    public function test_complaint_handling_and_exports(): void
    {
        $ticket = ComplaintTicket::whereNotIn('status', ['resolved', 'selesai'])->firstOrFail();

        // Tindakan tanpa teks memadai -> 422
        $this->actingAs($this->aslap)
            ->post(route('aslap.keluhan.handle', $ticket), [
                'status' => 'resolved',
                'resolution_notes' => 'pendek',
            ])
            ->assertSessionHasErrors('resolution_notes');

        // Konfirmasi pesantren dulu lalu selesaikan keluhan -> status pengiriman menjadi Diterima
        $this->artisan('demo:advance', ['scenario' => 'pesantren-konfirmasi'])->assertExitCode(0);

        $this->actingAs($this->aslap)
            ->post(route('aslap.keluhan.handle', $ticket), [
                'status' => 'resolved',
                'resolution_notes' => 'Mengirim 4 porsi pengganti ke Pesantren Al-Falah.',
            ])
            ->assertRedirect(route('aslap.keluhan.index'));

        $this->assertSame('resolved', $ticket->fresh()->status);

        $this->actingAs($this->aslap)->get(route('aslap.laporan.export.pdf'))->assertOk();
        $this->actingAs($this->aslap)->get(route('aslap.laporan.export.excel'))->assertOk();
        $this->actingAs($this->aslap)->get(route('aslap.laporan.export.csv'))->assertOk();
    }
}
