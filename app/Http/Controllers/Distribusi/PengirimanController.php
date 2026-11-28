<?php

namespace App\Http\Controllers\Distribusi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Distribusi\UpdateDeliveryStatusRequest;
use App\Models\DeliveryDocument;
use App\Models\DeliveryRoute;
use App\Policies\TimDistribusiPolicy;
use App\Services\TimDistribusiService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PengirimanController extends Controller
{
    public function index(): View
    {
        $todayStr = now('Asia/Jakarta')->toDateString();
        $routes = DeliveryRoute::query()
            ->with(['location', 'documents', 'statusLogs.user'])
            ->whereDate('operational_date', $todayStr)
            ->orderBy('waybill_number')
            ->get();

        return view('distribusi.pengiriman', [
            'routes' => $routes,
        ]);
    }

    public function update(
        UpdateDeliveryStatusRequest $request,
        DeliveryRoute $route,
        TimDistribusiService $service,
    ): RedirectResponse {
        $eventTime = $request->filled('event_time')
            ? Carbon::parse((string) $request->input('event_time'), 'Asia/Jakarta')
            : now('Asia/Jakarta');

        $service->updateDelivery(
            route: $route,
            actor: $request->user(),
            targetStatus: $request->string('status')->toString(),
            portions: (int) $request->input('portions'),
            recipientName: $request->input('nama_penerima_serah'),
            eventTime: $eventTime,
            fieldNotes: $request->input('catatan_lapangan'),
            document: $request->file('document'),
        );

        return redirect()
            ->route('distribusi.pengiriman.index')
            ->with('success', 'Status pengiriman & serah terima berhasil diperbarui.');
    }

    public function downloadDocument(DeliveryDocument $document, TimDistribusiPolicy $policy): StreamedResponse
    {
        if (! $policy->downloadDocument(auth()->user(), $document)) {
            abort(403, 'Anda tidak memiliki izin mengunduh berkas ini.');
        }

        if (! Storage::disk('local')->exists($document->path)) {
            abort(404, 'Berkas bukti tidak ditemukan.');
        }

        return Storage::disk('local')->download($document->path, $document->original_name ?? basename($document->path));
    }
}
