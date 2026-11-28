<?php

namespace App\Http\Controllers\Aslap;

use App\Http\Controllers\Controller;
use App\Services\AslapService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(AslapService $service): View
    {
        return view('aslap.dashboard', [
            'data' => $service->dashboardData(),
            'complaintBadge' => $service->activeComplaintsBadge(),
        ]);
    }
}
