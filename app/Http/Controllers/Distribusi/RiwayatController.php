<?php

namespace App\Http\Controllers\Distribusi;

use App\Http\Controllers\Controller;
use App\Models\ComplaintTicket;
use App\Models\DeliveryRoute;
use Illuminate\View\View;

class RiwayatController extends Controller
{
    public function __invoke(): View
    {
        $end = now('Asia/Jakarta');
        $start = $end->copy()->subDays(4);

        $logs = DeliveryRoute::query()
            ->with(['location', 'confirmer'])
            ->whereDate('operational_date', '>=', $start->toDateString())
            ->whereDate('operational_date', '<=', $end->toDateString())
            ->orderByDesc('operational_date')
            ->orderBy('waybill_number')
            ->paginate(10);

        $obstacles = ComplaintTicket::query()
            ->with(['location', 'resolver'])
            ->whereIn('category', ['kemasan_tumpah', 'keterlambatan', 'selisih_porsi', 'kelengkapan_alat'])
            ->orderByDesc('reported_at')
            ->get();

        return view('distribusi.riwayat', [
            'logs' => $logs,
            'obstacles' => $obstacles,
        ]);
    }
}
