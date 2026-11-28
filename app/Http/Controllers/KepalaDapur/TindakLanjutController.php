<?php

namespace App\Http\Controllers\KepalaDapur;

use App\Http\Controllers\Controller;
use App\Http\Requests\KepalaDapur\HandleComplaintRequest;
use App\Http\Requests\KepalaDapur\ResolveFindingRequest;
use App\Models\ComplaintTicket;
use App\Models\FoodSafetyCheck;
use App\Models\FoodSafetyFinding;
use App\Models\ProductionObstacle;
use App\Services\KepalaDapurService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TindakLanjutController extends Controller
{
    public function index(Request $request, KepalaDapurService $service): View
    {
        $tab = $request->string('tab')->toString();
        if (! in_array($tab, ['temuan', 'keluhan', 'kendala'], true)) {
            $tab = 'temuan';
        }

        $findings = FoodSafetyFinding::query()
            ->with(['handler', 'foodSafetyCheck'])
            ->orderByRaw("CASE WHEN status = 'terbuka' THEN 0 ELSE 1 END")
            ->orderByDesc('finding_date')
            ->get();

        $latestSafetyCheck = FoodSafetyCheck::query()
            ->orderByDesc('check_date')
            ->orderByDesc('checked_at')
            ->first();

        $complaints = ComplaintTicket::query()
            ->with(['location', 'reporter', 'resolver', 'histories.user'])
            ->orderByRaw("CASE WHEN status IN ('open','in_progress','baru','dalam_tindak_lanjut') THEN 0 ELSE 1 END")
            ->orderByDesc('reported_at')
            ->get();

        $obstacles = ProductionObstacle::query()
            ->with('recorder')
            ->orderByDesc('obstacle_date')
            ->get();

        return view('kepala-dapur.tindak-lanjut', [
            'tab' => $tab,
            'findings' => $findings,
            'latestSafetyCheck' => $latestSafetyCheck,
            'complaints' => $complaints,
            'obstacles' => $obstacles,
            'badges' => $service->sidebarBadges(),
        ]);
    }

    public function resolveFinding(
        ResolveFindingRequest $request,
        FoodSafetyFinding $finding,
        KepalaDapurService $service,
    ): RedirectResponse {
        $service->resolveFinding($finding, $request->user(), $request->string('handler_note')->toString());

        return redirect()
            ->route('kepala-dapur.tindak-lanjut.index', ['tab' => 'temuan'])
            ->with('success', 'Temuan keamanan pangan berhasil ditandai Ditindaklanjuti.');
    }

    public function handleComplaint(
        HandleComplaintRequest $request,
        ComplaintTicket $ticket,
        KepalaDapurService $service,
    ): RedirectResponse {
        $service->handleSeriousComplaint(
            ticket: $ticket,
            actor: $request->user(),
            status: $request->string('status')->toString(),
            resolutionNotes: $request->string('resolution_notes')->toString(),
        );

        return redirect()
            ->route('kepala-dapur.tindak-lanjut.index', ['tab' => 'keluhan'])
            ->with('success', 'Tindak lanjut keluhan berhasil disimpan.');
    }
}
