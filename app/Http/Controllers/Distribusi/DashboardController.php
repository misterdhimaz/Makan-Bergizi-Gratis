<?php

namespace App\Http\Controllers\Distribusi;

use App\Http\Controllers\Controller;
use App\Services\TimDistribusiService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(TimDistribusiService $service): View
    {
        return view('distribusi.dashboard', [
            'data' => $service->dashboardData(),
        ]);
    }
}
