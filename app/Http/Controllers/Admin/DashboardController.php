<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AdminDashboardService $dashboardService,
    ) {}

    /**
     * Menampilkan Dashboard Administrator Sistem.
     */
    public function __invoke(): View
    {
        $metrics = $this->dashboardService->metrics();
        $showOperasional = (bool) config('mbg.admin_lihat_operasional', true);

        return view('admin.dashboard', [
            'metrics' => $metrics,
            'showOperasional' => $showOperasional,
        ]);
    }
}
