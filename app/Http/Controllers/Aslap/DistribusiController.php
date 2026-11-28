<?php

namespace App\Http\Controllers\Aslap;

use App\Http\Controllers\Controller;
use App\Http\Requests\Aslap\UpdateScheduleRequest;
use App\Models\DeliveryRoute;
use App\Services\AslapService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DistribusiController extends Controller
{
    public function index(Request $request, AslapService $service): View
    {
        $selectedDate = $request->string('tanggal')->toString() ?: now('Asia/Jakarta')->toDateString();

        $routes = DeliveryRoute::query()
            ->with('location')
            ->whereDate('operational_date', $selectedDate)
            ->orderBy('waybill_number')
            ->get();

        return view('aslap.distribusi', [
            'selectedDate' => $selectedDate,
            'routes' => $routes,
            'complaintBadge' => $service->activeComplaintsBadge(),
        ]);
    }

    public function store(Request $request, AslapService $service): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
        ]);

        $date = Carbon::parse($validated['tanggal'], 'Asia/Jakarta');
        $created = $service->createScheduleFromApprovedMenu($date, $request->user());

        return redirect()
            ->route('aslap.distribusi.index', ['tanggal' => $date->toDateString()])
            ->with('success', $created > 0 ? "{$created} rute pengiriman berhasil dibuat." : 'Semua rute untuk tanggal tersebut sudah tersedia.');
    }

    public function update(
        UpdateScheduleRequest $request,
        DeliveryRoute $route,
        AslapService $service,
    ): RedirectResponse {
        $service->updateSchedule($route, $request->user(), $request->validated());

        return redirect()
            ->route('aslap.distribusi.index', ['tanggal' => $route->operational_date?->toDateString()])
            ->with('success', 'Jadwal & armada pengiriman berhasil diperbarui.');
    }
}
