<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\BeneficiaryInstitution;
use App\Models\ComplaintTicket;
use App\Models\Delivery;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ComplaintTicketController extends Controller
{
    /**
     * Buat Tiket Keluhan Baru oleh Guru PIC Sekolah / Posyandu (Fitur 7)
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'delivery_id' => ['nullable', 'exists:deliveries,id'],
            'beneficiary_institution_id' => ['required', 'exists:beneficiary_institutions,id'],
            'category' => ['required', 'in:porsi_kurang,basi_asam,benda_asing,keterlambatan,ompreng_rusak,lainnya'],
            'severity' => ['required', 'in:rendah,sedang,kritis'],
            'description' => ['required', 'string', 'max:1000'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'], // Max 2MB sesuai Dokumen Hal 14
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('complaints', 'public');
        }

        $user = $request->user();

        // 1. Penguncian Otorisasi Institusi Guru PIC (Anti-IDOR)
        if ($user->hasRole(UserRole::GURU_PIC)) {
            if (! $user->beneficiary_institution_id) {
                return back()->with('error', 'Akun Guru PIC Anda belum terhubung dengan institusi penerima.');
            }

            $validated['beneficiary_institution_id'] = $user->beneficiary_institution_id;

            if (! empty($validated['delivery_id'])) {
                $delivery = Delivery::find($validated['delivery_id']);
                if (! $delivery || $delivery->beneficiary_institution_id !== $user->beneficiary_institution_id) {
                    abort(403, 'Akses Ditolak: Anda tidak dapat melaporkan keluhan untuk pengiriman institusi lain.');
                }
            }
        } elseif (! empty($validated['delivery_id'])) {
            // Validasi konsistensi pengiriman vs institusi untuk Admin / Aslap
            $delivery = Delivery::find($validated['delivery_id']);
            if ($delivery && $delivery->beneficiary_institution_id !== (int) $validated['beneficiary_institution_id']) {
                return back()->with('error', 'Pengiriman yang dipilih tidak sesuai dengan institusi penerima.');
            }
        }

        $institution = BeneficiaryInstitution::findOrFail($validated['beneficiary_institution_id']);

        // 2. Transaksi & Penomoran Tiket Bebas Bentrok
        $todayStr = Carbon::today()->format('Ymd');

        $ticket = DB::transaction(function () use ($validated, $institution, $photoPath, $todayStr) {
            $countToday = ComplaintTicket::whereDate('created_at', Carbon::today())->count() + 1;
            $ticketNumber = 'TKT-' . $todayStr . '-' . str_pad((string) $countToday, 3, '0', STR_PAD_LEFT);

            while (ComplaintTicket::where('ticket_number', $ticketNumber)->exists()) {
                $countToday++;
                $ticketNumber = 'TKT-' . $todayStr . '-' . str_pad((string) $countToday, 3, '0', STR_PAD_LEFT);
            }

            return ComplaintTicket::create([
                'ticket_number' => $ticketNumber,
                'delivery_id' => $validated['delivery_id'] ?? null,
                'beneficiary_institution_id' => $institution->id,
                'reported_by' => Auth::id(),
                'category' => $validated['category'],
                'severity' => $validated['severity'],
                'description' => $validated['description'],
                'photo_path' => $photoPath,
                'status' => 'open',
            ]);
        });

        ActivityLog::record(
            'create_complaint',
            'complaints',
            "Menerbitkan tiket keluhan {$ticket->ticket_number} ({$ticket->categoryLabel()}) dengan tingkat keparahan " . strtoupper($ticket->severity) . " untuk {$institution->name}."
        );

        $msg = "Tiket Keluhan {$ticket->ticket_number} berhasil dibuat dan diteruskan ke Asisten Lapangan & Kepala Dapur.";
        if ($ticket->severity === 'kritis') {
            $msg .= ' [PERINGATAN KRITIS: Notifikasi prioritas telah diaktifkan di dashboard SPPG!]';
        }

        return back()->with('success', $msg);
    }

    /**
     * Tindak Lanjut & Penyelesaian Tiket Keluhan oleh Asisten Lapangan / Kepala Dapur (Fitur 7)
     */
    public function resolve(Request $request, ComplaintTicket $complaintTicket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:in_progress,resolved'],
            'resolution_notes' => ['required', 'string', 'max:1000'],
            'resolution_photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $photoPath = $complaintTicket->resolution_photo_path;
        if ($request->hasFile('resolution_photo')) {
            $photoPath = $request->file('resolution_photo')->store('complaint_resolutions', 'public');
        }

        $complaintTicket->update([
            'status' => $validated['status'],
            'resolved_by' => Auth::id(),
            'resolution_notes' => $validated['resolution_notes'],
            'resolution_photo_path' => $photoPath,
            'resolved_at' => $validated['status'] === 'resolved' ? now() : null,
        ]);

        ActivityLog::record(
            'resolve_complaint',
            'complaints',
            "Memperbarui penanganan tiket keluhan {$complaintTicket->ticket_number} menjadi status " . strtoupper(str_replace('_', ' ', $validated['status'])) . " dengan catatan: {$validated['resolution_notes']}."
        );

        return back()->with('success', "Tiket keluhan {$complaintTicket->ticket_number} berhasil diperbarui statusnya menjadi " . strtoupper(str_replace('_', ' ', $validated['status'])) . ".");
    }
}

