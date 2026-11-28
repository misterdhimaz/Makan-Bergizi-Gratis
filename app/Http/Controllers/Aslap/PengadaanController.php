<?php

namespace App\Http\Controllers\Aslap;

use App\Http\Controllers\Controller;
use App\Http\Requests\Aslap\UpdateProcurementRequest;
use App\Models\DailyMenu;
use App\Models\InventoryTransaction;
use App\Models\Supplier;
use App\Services\AslapService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengadaanController extends Controller
{
    public function index(Request $request, AslapService $service): View
    {
        $todayStr = now('Asia/Jakarta')->toDateString();
        $filter = $request->string('status')->toString();
        if (! in_array($filter, ['semua', 'menunggu', 'lengkap'], true)) {
            $filter = 'semua';
        }

        $menusWithNeeds = DailyMenu::query()
            ->with('submittedBy')
            ->whereHas('inventoryTransactions', fn ($q) => $q->where('type', 'inbound_purchase'))
            ->orderBy('operational_date')
            ->get();

        $selectedDate = $request->string('tanggal')->toString();
        $selectedMenu = $selectedDate !== ''
            ? $menusWithNeeds->first(fn (DailyMenu $m) => $m->operational_date?->toDateString() === $selectedDate)
            : $menusWithNeeds->first(fn (DailyMenu $m) => ($m->operational_date?->toDateString() ?? '') > $todayStr)
                ?? $menusWithNeeds->first();

        $transactions = $selectedMenu
            ? InventoryTransaction::query()
                ->with(['ingredient.supplier', 'recorder'])
                ->where('daily_menu_id', $selectedMenu->id)
                ->where('type', 'inbound_purchase')
                ->orderBy('id')
                ->get()
            : collect();

        $filteredTransactions = match ($filter) {
            'menunggu' => $transactions->where('procurement_status', 'menunggu_terima'),
            'lengkap' => $transactions->where('procurement_status', 'diterima_lengkap'),
            default => $transactions,
        };

        $suppliers = Supplier::query()->where('is_active', true)->orderBy('name')->get();

        // Selisih dapur dari realisasi hari ini (hanya baca)
        $todayMenu = DailyMenu::query()->whereDate('operational_date', $todayStr)->first();
        $varianceTexts = [];
        if ($todayMenu) {
            $todayTxs = InventoryTransaction::query()
                ->with('ingredient')
                ->where('daily_menu_id', $todayMenu->id)
                ->where('type', 'inbound_purchase')
                ->get();
            foreach ($todayTxs as $tx) {
                $diff = (float) $tx->quantity - (float) $tx->planned_qty;
                if (abs($diff) > 0.001) {
                    $varianceTexts[] = ($tx->ingredient?->name ?? 'Bahan').' '.($diff > 0 ? '+' : '').(int) round($diff).' '.($tx->ingredient?->unit ?? 'kg');
                }
            }
        }

        return view('aslap.pengadaan', [
            'menusWithNeeds' => $menusWithNeeds,
            'selectedMenu' => $selectedMenu,
            'transactions' => $transactions,
            'filteredTransactions' => $filteredTransactions,
            'filter' => $filter,
            'suppliers' => $suppliers,
            'varianceSummary' => $varianceTexts !== [] ? implode(' · ', $varianceTexts) : 'Tidak ada selisih',
            'complaintBadge' => $service->activeComplaintsBadge(),
        ]);
    }

    public function update(
        UpdateProcurementRequest $request,
        InventoryTransaction $transaction,
        AslapService $service,
    ): RedirectResponse {
        $supplier = $request->string('supplier_name')->toString();
        if ($supplier === 'Pemasok belum ditautkan') {
            $supplier = '';
        }

        $service->recordProcurement(
            transaction: $transaction,
            actor: $request->user(),
            quantity: (float) $request->input('quantity'),
            supplierName: $supplier !== '' ? $supplier : null,
            receivedAt: Carbon::parse((string) $request->input('received_at'), 'Asia/Jakarta'),
            notes: $request->input('notes'),
        );

        return redirect()
            ->route('aslap.pengadaan.index', ['tanggal' => $transaction->dailyMenu?->operational_date?->toDateString()])
            ->with('success', 'Penerimaan bahan baku berhasil disimpan.');
    }
}
