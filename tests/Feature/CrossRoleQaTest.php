<?php

namespace Tests\Feature;

use App\Enums\MenuStatus;
use App\Models\DailyMenu;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossRoleQaTest extends TestCase
{
    use RefreshDatabase;

    public function test_end_to_end_demo_advance_scenario_and_cross_role_isolation(): void
    {
        $this->seed(DatabaseSeeder::class);

        $kepalaDapur = User::where('username', 'hendra.wijaya')->firstOrFail();
        $aslap = User::where('username', 'bima.santoso')->firstOrFail();
        $distribusi = User::where('username', 'fajar.nugroho')->firstOrFail();
        $guruPic = User::where('username', 'rina.marlina')->firstOrFail();

        // 1. Buka dashboard ketiga role pada seed awal
        $this->actingAs($kepalaDapur)->get(route('kepala-dapur.dashboard'))->assertOk()->assertSee('318', false);
        $this->actingAs($aslap)->get(route('aslap.dashboard'))->assertOk()->assertSee('318', false);
        $this->actingAs($distribusi)->get(route('distribusi.dashboard'))->assertOk()->assertSee('DST-20261126-01', false);

        // 2. Majukan menu 28 Nov oleh Akuntan, lalu setujui sebagai Kepala Dapur
        $this->artisan('demo:advance', ['scenario' => 'akuntan-lolos-28nov'])->assertExitCode(0);
        $menu28 = DailyMenu::whereDate('operational_date', '2026-11-28')->firstOrFail();

        $this->actingAs($kepalaDapur)
            ->post(route('kepala-dapur.persetujuan.menu.decide', $menu28), [
                'decision' => 'setujui',
                'catatan' => 'Disetujui saat QA lintas role.',
            ])
            ->assertRedirect();

        $this->assertSame(MenuStatus::DISETUJUI->value, $menu28->fresh()->status);

        // 3. Majukan konfirmasi Pesantren Al-Falah (346/350), cek 664 / 500 / -6 di Kepala Dapur & Aslap
        $this->artisan('demo:advance', ['scenario' => 'pesantren-konfirmasi'])->assertExitCode(0);

        $this->actingAs($kepalaDapur)
            ->get(route('kepala-dapur.dashboard'))
            ->assertOk()
            ->assertSee('664', false)
            ->assertSee('3/7 terkonfirmasi diterima', false);

        $this->actingAs($aslap)
            ->get(route('aslap.dashboard'))
            ->assertOk()
            ->assertSee('664', false)
            ->assertSee('500', false)
            ->assertSee('-6', false);

        // 4. Isolasi akses lintas role
        $this->actingAs($guruPic)->get(route('kepala-dapur.dashboard'))->assertForbidden();
        $this->actingAs($guruPic)->get(route('aslap.dashboard'))->assertForbidden();
        $this->actingAs($guruPic)->get(route('distribusi.dashboard'))->assertForbidden();
    }
}
