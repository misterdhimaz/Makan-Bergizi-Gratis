<?php

namespace App\Http\Controllers\AhliGizi;

use App\Http\Controllers\Controller;
use App\Models\ComplaintTicket;
use App\Models\DailyMenu;
use App\Models\DeliveryRoute;
use App\Models\Ingredient;
use App\Models\Location;
use App\Models\PortionStandard;
use App\Models\RecipientCategory;
use App\Models\SopChecklist;
use App\Models\Supplier;
use App\Services\DeliveryMetrics;
use Illuminate\View\View;

class ReadOnlyController extends Controller
{
    public function distribusi(DeliveryMetrics $deliveryMetrics): View
    {
        $now = now('Asia/Jakarta');
        $today = $now->toDateString();
        $routes = DeliveryRoute::with('location')
            ->whereDate('operational_date', $today)
            ->orderBy('waybill_number')
            ->get();

        $metrics = $deliveryMetrics->forDate($now);

        return view('ahli-gizi.readonly.distribusi', [
            'routes' => $routes,
            'stats' => [
                'dikirim' => $metrics['dikirim'],
                'diterima' => $metrics['diterima'],
                'belum_dikonfirmasi' => $metrics['belum_dikonfirmasi'],
                'selisih' => $metrics['selisih_terkonfirmasi'],
            ],
        ]);
    }

    public function keluhan(): View
    {
        $complaints = ComplaintTicket::with(['location', 'institution', 'reporter', 'resolver'])
            ->orderByDesc('reported_at')
            ->orderByDesc('id')
            ->get();

        return view('ahli-gizi.readonly.keluhan', [
            'complaints' => $complaints,
        ]);
    }

    public function laporan(): View
    {
        $menus = DailyMenu::with('productionRun')
            ->orderByDesc('operational_date')
            ->limit(14)
            ->get();

        return view('ahli-gizi.readonly.laporan', [
            'menus' => $menus,
        ]);
    }

    public function dataMaster(): View
    {
        return view('ahli-gizi.readonly.data-master', [
            'locations' => Location::query()->where('is_active', true)->orderBy('code')->get(),
            'categories' => RecipientCategory::query()->where('is_active', true)->orderBy('code')->get(),
            'portions' => PortionStandard::with('recipientCategory')->where('is_active', true)->get(),
            'ingredients' => Ingredient::with('supplier')->where('is_active', true)->orderBy('name')->get(),
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('code')->get(),
            'checklists' => SopChecklist::query()->where('is_active', true)->orderBy('code')->get(),
        ]);
    }
}
