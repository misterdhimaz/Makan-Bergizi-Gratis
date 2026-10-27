<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BeneficiaryInstitution;
use App\Models\ComplaintTicket;
use App\Models\Delivery;
use App\Models\DeliveryConfirmation;
use App\Models\ProductionRun;
use App\Models\SopConfiguration;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DistributionController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $isPic = $user->role->value === 'guru_pic';
        $picInstitutionId = $user->beneficiary_institution_id;

        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status', 'all');
        $institutionId = $request->query('institution_id', 'all');
        $severity = $request->query('severity', 'all');

        $query = Delivery::with([
            'productionRun.dailyMenu',
            'institution',
            'courier',
            'confirmation.picUser',
            'complaintTickets.reporter',
        ]);

        // Pembatasan hak akses: Guru PIC hanya dapat melihat data sekolahnya sendiri (Dokumen Hal 10)
        if ($isPic && $picInstitutionId) {
            $query->where('beneficiary_institution_id', $picInstitutionId);
        } elseif ($institutionId !== 'all') {
            $query->where('beneficiary_institution_id', $institutionId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('waybill_number', 'like', "%{$search}%")
                  ->orWhere('vehicle_plate_number', 'like', "%{$search}%")
                  ->orWhere('courier_notes', 'like', "%{$search}%")
                  ->orWhereHas('institution', fn ($iq) => $iq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
            });
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($severity !== 'all') {
            $query->whereHas('confirmation', fn ($cq) => $cq->where('complaint_severity', $severity));
        }

        $deliveries = $query->orderByDesc('id')->get();
        $productionRuns = ProductionRun::with('dailyMenu')->latest('id')->get();

        $instQuery = BeneficiaryInstitution::where('is_active', true);
        if ($isPic && $picInstitutionId) {
            $instQuery->where('id', $picInstitutionId);
        }
        $institutions = $instQuery->orderBy('code')->get();

        // Ambil tiket keluhan digital untuk tab keluhan
        $complaintQuery = ComplaintTicket::with(['institution', 'reporter', 'resolver', 'delivery']);
        if ($isPic && $picInstitutionId) {
            $complaintQuery->where('beneficiary_institution_id', $picInstitutionId);
        }
        $complaintTickets = $complaintQuery->latest('id')->get();

        // Cek keluhan kritis aktif yang belum selesai (untuk trigger banner notifikasi merah)
        $criticalTickets = $complaintTickets->where('severity', 'kritis')->where('status', '!=', 'resolved');

        $statusDistribution = [
            'preparing' => Delivery::where('status', 'preparing')->count(),
            'in_transit' => Delivery::where('status', 'in_transit')->count(),
            'arrived' => Delivery::where('status', 'arrived')->count(),
            'confirmed_clean' => Delivery::where('status', 'confirmed_clean')->count(),
            'confirmed_with_issue' => Delivery::where('status', 'confirmed_with_issue')->count(),
        ];

        $chartDelivLabels = $deliveries->take(8)->map(fn ($d) => $d->institution->name);
        $chartDispatched = $deliveries->take(8)->pluck('dispatched_portions');
        $chartReceivedGood = $deliveries->take(8)->map(fn ($d) => $d->confirmation?->received_good_portions ?? 0);

        $maxDeliveryTimeStr = SopConfiguration::getValue('batas_waktu_tiba_maksimal', '10:30');

        return view('distribution.index', compact(
            'deliveries',
            'productionRuns',
            'institutions',
            'complaintTickets',
            'criticalTickets',
            'search',
            'status',
            'institutionId',
            'severity',
            'statusDistribution',
            'chartDelivLabels',
            'chartDispatched',
            'chartReceivedGood',
            'isPic',
            'maxDeliveryTimeStr'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'waybill_number' => ['required', 'string', 'max:40', 'unique:deliveries,waybill_number'],
            'production_run_id' => ['required', 'exists:production_runs,id'],
            'beneficiary_institution_id' => ['required', 'exists:beneficiary_institutions,id'],
            'vehicle_plate_number' => ['required', 'string', 'max:20'],
            'allocated_portions' => ['required', 'integer', 'min:1'],
            'dispatched_portions' => ['required', 'integer', 'min:1'],
            'courier_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $inst = BeneficiaryInstitution::findOrFail($validated['beneficiary_institution_id']);

        $delivery = Delivery::create([
            'waybill_number' => strtoupper($validated['waybill_number']),
            'production_run_id' => $validated['production_run_id'],
            'beneficiary_institution_id' => $inst->id,
            'courier_id' => Auth::id(),
            'vehicle_plate_number' => strtoupper($validated['vehicle_plate_number']),
            'allocated_portions' => $validated['allocated_portions'],
            'dispatched_portions' => $validated['dispatched_portions'],
            'departed_at' => now(),
            'status' => 'in_transit',
            'courier_notes' => $validated['courier_notes'],
        ]);

        ActivityLog::record(
            'create_delivery',
            'distribution',
            "Menerbitkan surat jalan {$delivery->waybill_number} ke {$inst->name} ({$delivery->dispatched_portions} porsi) armada {$delivery->vehicle_plate_number}."
        );

        return redirect()->route('distribution.index')
            ->with('success', 'Surat Jalan pengiriman baru berhasil diterbitkan dan status armada kini Dalam Perjalanan.');
    }

    public function updateDispatch(Request $request, Delivery $delivery): RedirectResponse
    {
        $validated = $request->validate([
            'dispatched_portions' => ['required', 'integer', 'min:1'],
            'vehicle_plate_number' => ['required', 'string', 'max:20'],
            'status' => ['required', 'in:preparing,in_transit,arrived'],
            'courier_notes' => ['nullable', 'string', 'max:500'],
            'dispatch_photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'], // 2MB max
        ]);

        $photoPath = $delivery->dispatch_photo_path;
        if ($request->hasFile('dispatch_photo')) {
            $photoPath = $request->file('dispatch_photo')->store('waybills', 'public');
        }

        $delivery->update([
            'dispatched_portions' => $validated['dispatched_portions'],
            'vehicle_plate_number' => strtoupper($validated['vehicle_plate_number']),
            'status' => $validated['status'],
            'departed_at' => $delivery->departed_at ?? now(),
            'arrived_at' => $validated['status'] === 'arrived' ? ($delivery->arrived_at ?? now()) : $delivery->arrived_at,
            'dispatch_photo_path' => $photoPath,
            'courier_notes' => $validated['courier_notes'],
        ]);

        ActivityLog::record(
            'update_dispatch',
            'distribution',
            "Tim Distribusi memperbarui surat jalan {$delivery->waybill_number} ke {$delivery->institution->name} status {$validated['status']}."
        );

        return redirect()->route('distribution.index')
            ->with('success', 'Status pengiriman Surat Jalan ' . $delivery->waybill_number . ' (' . $delivery->institution->name . ') berhasil diperbarui.');
    }

    public function storeConfirmation(Request $request, Delivery $delivery): RedirectResponse
    {
        $validated = $request->validate([
            'received_good_portions' => ['required', 'integer', 'min:0'],
            'received_damaged_or_missing_portions' => ['required', 'integer', 'min:0'],
            'aroma_test' => ['required', 'in:normal_segar,kurang_sedap,basi_asam'],
            'taste_test' => ['required', 'in:normal_lezat,hambar,tidak_layak'],
            'color_visual_test' => ['required', 'in:cerah_normal,pucat,berubah_warna'],
            'texture_test' => ['required', 'in:baik_sesuai,terlalu_keras_lembek,berlendir'],
            'arrival_temperature_c' => ['required', 'numeric', 'min:20', 'max:95'],
            'complaint_severity' => ['required', 'in:none,low,medium,critical'],
            'feedback_or_complaint' => ['nullable', 'string', 'max:600'],
            'reception_photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        DB::transaction(function () use ($request, $delivery, $validated) {
            $isFit = $request->boolean('is_fit_for_consumption', true);
            $photoPath = $delivery->confirmation?->reception_photo_path;
            if ($request->hasFile('reception_photo')) {
                $photoPath = $request->file('reception_photo')->store('reception_proofs', 'public');
            }

            DeliveryConfirmation::updateOrCreate(
                ['delivery_id' => $delivery->id],
                [
                    'confirmed_by' => Auth::id(),
                    'received_good_portions' => $validated['received_good_portions'],
                    'received_damaged_or_missing_portions' => $validated['received_damaged_or_missing_portions'],
                    'reception_variance' => $validated['received_good_portions'] - $delivery->dispatched_portions,
                    'aroma_test' => $validated['aroma_test'],
                    'taste_test' => $validated['taste_test'],
                    'color_visual_test' => $validated['color_visual_test'],
                    'texture_test' => $validated['texture_test'],
                    'arrival_temperature_c' => $validated['arrival_temperature_c'],
                    'is_fit_for_consumption' => $isFit,
                    'complaint_severity' => $validated['complaint_severity'],
                    'feedback_or_complaint' => $validated['feedback_or_complaint'] ?? 'Uji organoleptik lengkap dan makanan siap didistribusikan.',
                    'reception_photo_path' => $photoPath,
                    'confirmed_at' => now(),
                ]
            );

            $delivery->update([
                'arrived_at' => $delivery->arrived_at ?? now(),
                'status' => ($validated['complaint_severity'] === 'none' && $validated['received_damaged_or_missing_portions'] == 0)
                    ? 'confirmed_clean'
                    : 'confirmed_with_issue',
            ]);

            ActivityLog::record(
                'confirm_receipt',
                'reception',
                "Guru PIC {$delivery->institution->name} mengonfirmasi penerimaan: {$validated['received_good_portions']} baik, {$validated['received_damaged_or_missing_portions']} rusak, Aroma: {$validated['aroma_test']}, Rasa: {$validated['taste_test']}."
            );
        });

        return redirect()->route('distribution.index')
            ->with('success', 'Konfirmasi Penerimaan & Hasil Uji Organoleptik untuk ' . $delivery->institution->name . ' berhasil disimpan!');
    }

    public function destroy(Delivery $delivery): RedirectResponse
    {
        $wb = $delivery->waybill_number;
        $delivery->delete();

        ActivityLog::record(
            'delete_delivery',
            'distribution',
            "Menghapus arsip surat jalan {$wb} dari sistem."
        );

        return redirect()->route('distribution.index')
            ->with('success', "Surat jalan {$wb} telah dihapus dari database.");
    }
}
