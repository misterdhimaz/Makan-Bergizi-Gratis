<?php

namespace App\Http\Controllers\Akuntan;

use App\Http\Controllers\Controller;
use App\Services\AkuntanDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AkuntanDashboardService $dashboard,
    ) {}

    public function index(Request $request): View
    {
        $data = $this->dashboard->build($request->user());

        return view('akuntan.dashboard', $data);
    }
}
