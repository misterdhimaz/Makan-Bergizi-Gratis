<?php

namespace App\Http\Controllers\KepalaDapur;

use App\Http\Controllers\Controller;
use App\Services\KepalaDapurService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(KepalaDapurService $service): View
    {
        return view('kepala-dapur.dashboard', [
            'data' => $service->dashboardData(),
            'badges' => $service->sidebarBadges(),
        ]);
    }
}
