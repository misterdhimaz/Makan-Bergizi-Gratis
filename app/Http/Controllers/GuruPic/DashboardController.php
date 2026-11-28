<?php

namespace App\Http\Controllers\GuruPic;

use App\Enums\MenuStatus;
use App\Http\Controllers\Controller;
use App\Models\ComplaintTicket;
use App\Models\DailyMenu;
use App\Models\DeliveryRoute;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $location = $user?->location_id
            ? Location::find($user->location_id)
            : Location::where('is_active', true)->orderBy('id')->first();

        $now = now('Asia/Jakarta');
        $today = $now->toDateString();

        $todayRoute = $location
            ? (DeliveryRoute::with(['courier', 'confirmer'])
                ->where('location_id', $location->id)
                ->whereDate('operational_date', $today)
                ->latest('id')
                ->first()
                ?? DeliveryRoute::with(['courier', 'confirmer'])
                    ->where('location_id', $location->id)
                    ->orderByDesc('operational_date')
                    ->first())
            : null;

        $historyRoutes = $location
            ? DeliveryRoute::query()
                ->where('location_id', $location->id)
                ->orderByDesc('operational_date')
                ->orderByDesc('id')
                ->limit(5)
                ->get()
            : collect();

        $todayMenu = DailyMenu::query()
            ->whereDate('operational_date', $today)
            ->where('status', MenuStatus::DISETUJUI->value)
            ->first()
            ?? DailyMenu::query()->where('status', MenuStatus::DISETUJUI->value)->orderByDesc('operational_date')->first()
            ?? DailyMenu::query()->orderByDesc('operational_date')->first();

        $complaints = $location
            ? ComplaintTicket::with(['resolver'])
                ->where('location_id', $location->id)
                ->orderByDesc('reported_at')
                ->orderByDesc('id')
                ->limit(3)
                ->get()
            : collect();

        $activeComplaintsCount = $location
            ? ComplaintTicket::query()
                ->where('location_id', $location->id)
                ->whereIn('status', ['open', 'in_progress'])
                ->count()
            : 0;

        $targetPortions = (int) ($location?->target_portions ?? $todayRoute?->dispatched_portions ?? 250);
        $dispatchedPortions = (int) ($todayRoute?->dispatched_portions ?? $targetPortions);
        $confirmedPortions = $todayRoute?->confirmed_portions !== null
            ? (int) $todayRoute->confirmed_portions
            : $dispatchedPortions;
        $variance = $confirmedPortions - $dispatchedPortions;
        $delayMinutes = $todayRoute?->delayMinutes() ?? 0;

        return view('guru-pic.dashboard', [
            'location' => $location,
            'todayRoute' => $todayRoute,
            'historyRoutes' => $historyRoutes,
            'todayMenu' => $todayMenu,
            'complaints' => $complaints,
            'activeComplaintsCount' => $activeComplaintsCount,
            'targetPortions' => $targetPortions,
            'dispatchedPortions' => $dispatchedPortions,
            'confirmedPortions' => $confirmedPortions,
            'variance' => $variance,
            'delayMinutes' => $delayMinutes,
        ]);
    }
}
