<?php

namespace App\Providers;

use App\Http\Controllers\AppearanceSettingController;
use App\Models\ApdInspection;
use App\Models\AppSetting;
use App\Models\BeneficiaryInstitution;
use App\Models\DailyMenu;
use App\Models\Delivery;
use App\Models\DeliveryConfirmation;
use App\Models\Ingredient;
use App\Models\ProductionRun;
use App\Models\QcInspection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Optimasi performa Eloquent di environment produksi
        Model::automaticallyEagerLoadRelationships();

        // View Composer: Menarik data operasional real-time langsung dari Database untuk Sidebar & Topbar
        View::composer('layouts.app', function ($view) {
            $activeMenu = DailyMenu::with(['productionRun.qcInspection'])
                ->whereDate('operational_date', today())
                ->first()
                ?? DailyMenu::with(['productionRun.qcInspection'])
                    ->whereIn('status', ['in_production', 'approved', 'completed'])
                    ->latest('operational_date')
                    ->first();

            $activeProduction = $activeMenu?->productionRun
                ?? ProductionRun::with('dailyMenu', 'qcInspection')->latest('id')->first();

            $latestQc = $activeProduction?->qcInspection
                ?? QcInspection::latest('inspected_at')->first();

            $latestApd = ApdInspection::latest('inspection_date')->first();

            $pendingMenusCount = DailyMenu::where('status', 'pending_approval')->count();
            $lowStockItems = Ingredient::whereColumn('current_stock', '<=', 'min_stock_threshold')->get();
            $activeInstitutionsCount = BeneficiaryInstitution::where('is_active', true)->count();

            $deliveriesToday = Delivery::with(['institution', 'confirmation'])
                ->when($activeProduction, fn ($q) => $q->where('production_run_id', $activeProduction->id))
                ->get();

            $confirmedDeliveriesCount = $deliveriesToday->whereIn('status', ['confirmed_clean', 'confirmed_with_issue'])->count();
            $totalDeliveriesCount = $deliveriesToday->count();

            // Hitung progres siklus harian dari database (0 - 100%)
            $cycleProgress = 20;
            if ($activeMenu) {
                $cycleProgress = 35;
            }
            if ($activeProduction && $activeProduction->actual_good_portions > 0) {
                $cycleProgress = 60;
            }
            if ($latestQc && $latestQc->qc_verdict === 'passed') {
                $cycleProgress = 80;
            }
            if ($totalDeliveriesCount > 0) {
                $cycleProgress = min(100, 80 + (int) round(($confirmedDeliveriesCount / $totalDeliveriesCount) * 20));
            }

            // Susun Notifikasi Real-Time dari baris Database aktual
            $liveNotifications = collect();

            if ($latestQc) {
                $liveNotifications->push([
                    'color' => $latestQc->qc_verdict === 'passed' ? 'emerald' : 'amber',
                    'title' => 'QC Suhu Inti Masak ' . $latestQc->cooking_core_temp_c . '°C (' . strtoupper($latestQc->qc_verdict) . ')',
                    'subtitle' => 'Kode Sampel: ' . $latestQc->food_sample_code . ' • Skor APD: ' . $latestQc->apd_compliance_score . '%',
                    'time' => $latestQc->inspected_at->diffForHumans(),
                    'url' => route('quality-control.index'),
                ]);
            }

            if ($lowStockItems->isNotEmpty()) {
                $names = $lowStockItems->pluck('name')->take(2)->implode(', ');
                $liveNotifications->push([
                    'color' => 'amber',
                    'title' => $lowStockItems->count() . ' Bahan Baku di Bawah Ambang Minimum',
                    'subtitle' => $names . ' memerlukan restock segera.',
                    'time' => 'Stok Gudang Real-Time',
                    'url' => route('production-inventory.index'),
                ]);
            }

            if ($pendingMenusCount > 0) {
                $liveNotifications->push([
                    'color' => 'indigo',
                    'title' => $pendingMenusCount . ' Jadwal Menu Menunggu Validasi Kepala Dapur',
                    'subtitle' => 'Perlu otorisasi sebelum masuk jadwal produksi.',
                    'time' => 'Perencanaan Menu',
                    'url' => route('menu-planning.index'),
                ]);
            }

            $latestConfirmation = DeliveryConfirmation::with('delivery.institution')
                ->latest('confirmed_at')
                ->first();

            if ($latestConfirmation) {
                $liveNotifications->push([
                    'color' => 'teal',
                    'title' => $latestConfirmation->delivery->institution->name . ' Mengonfirmasi Penerimaan',
                    'subtitle' => number_format($latestConfirmation->received_good_portions) . ' porsi baik • Suhu tiba ' . $latestConfirmation->arrival_temperature_c . '°C',
                    'time' => $latestConfirmation->confirmed_at->diffForHumans(),
                    'url' => route('distribution.index'),
                ]);
            }

            $view->with([
                'navActiveMenu' => $activeMenu,
                'navActiveProduction' => $activeProduction,
                'navLatestQc' => $latestQc,
                'navLatestApd' => $latestApd,
                'navPendingMenusCount' => $pendingMenusCount,
                'navLowStockCount' => $lowStockItems->count(),
                'navActiveInstitutionsCount' => $activeInstitutionsCount,
                'navConfirmedDeliveriesCount' => $confirmedDeliveriesCount,
                'navTotalDeliveriesCount' => $totalDeliveriesCount,
                'navCycleProgress' => $cycleProgress,
                'navLiveNotifications' => $liveNotifications,
                'brandLogoUrl' => AppSetting::fileUrl('login_logo'),
                'activeTheme' => AppSetting::get('theme_preset', 'sppg-navy'),
                'themeSettings' => AppSetting::allSettings(),
                'themePresets' => AppearanceSettingController::THEME_PRESETS,
            ]);
        });
    }
}
