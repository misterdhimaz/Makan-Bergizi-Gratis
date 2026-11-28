<?php

namespace App\Http\Controllers\GuruPic;

use App\Http\Controllers\Controller;
use App\Models\BeneficiaryInstitution;
use App\Models\ComplaintTicket;
use App\Models\Location;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $location = $user?->location_id
            ? Location::find($user->location_id)
            : Location::where('is_active', true)->orderBy('id')->first();

        $tickets = $location
            ? ComplaintTicket::with(['resolver'])
                ->where('location_id', $location->id)
                ->orderByDesc('reported_at')
                ->orderByDesc('id')
                ->get()
            : collect();

        $activeCount = $tickets->whereIn('status', ['open', 'in_progress'])->count();
        $resolvedThisMonth = $tickets->where('status', 'resolved')->count();

        $avgResponseMinutes = (int) round(
            $tickets
                ->filter(fn ($t) => $t->reported_at && $t->resolved_at)
                ->avg(fn ($t) => max(1, $t->reported_at->diffInMinutes($t->resolved_at))) ?? 45
        );

        $nextTicketNumber = $this->generateTicketNumber();

        return view('guru-pic.keluhan', [
            'location' => $location,
            'tickets' => $tickets,
            'activeCount' => $activeCount,
            'resolvedThisMonth' => $resolvedThisMonth,
            'avgResponseMinutes' => $avgResponseMinutes,
            'nextTicketNumber' => $nextTicketNumber,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $location = $user?->location_id
            ? Location::findOrFail($user->location_id)
            : Location::where('is_active', true)->orderBy('id')->firstOrFail();

        $validated = $request->validate([
            'category' => ['required', 'in:kemasan_tumpah,selisih_porsi,keterlambatan,kualitas_organoleptik'],
            'priority' => ['required', 'in:normal,serius'],
            'description' => ['required', 'string', 'min:5', 'max:2000'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ], [
            'category.required' => 'Kategori keluhan wajib dipilih.',
            'priority.required' => 'Prioritas keluhan wajib dipilih.',
            'description.required' => 'Uraian keluhan wajib diisi.',
            'description.min' => 'Uraian keluhan minimal 5 karakter.',
            'photo.mimes' => 'Lampiran harus berformat JPG, JPEG, PNG, atau PDF.',
            'photo.max' => 'Ukuran foto bukti maksimal 2 MB.',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('complaint-evidences', 'local');
        }

        $isSerious = $validated['priority'] === 'serius';
        $ticketNumber = $this->generateTicketNumber();
        $institution = BeneficiaryInstitution::where('name', 'like', '%'.explode(' ', $location->name)[0].'%')->first()
            ?? BeneficiaryInstitution::firstOrFail();

        $ticket = ComplaintTicket::create([
            'ticket_number' => $ticketNumber,
            'beneficiary_institution_id' => $institution->id,
            'location_id' => $location->id,
            'reported_by' => $user->id,
            'category' => $validated['category'],
            'severity' => $isSerious ? 'kritis' : 'sedang',
            'description' => $validated['description'],
            'photo_path' => $photoPath,
            'status' => 'open',
            'reported_at' => now('Asia/Jakarta'),
            'priority_notified_at' => $isSerious ? now('Asia/Jakarta') : null,
            'priority_recipients' => $isSerious ? 'Asisten Lapangan & Kepala Dapur (SPPG Sungai Selayur 1)' : null,
        ]);

        AuditLogService::record(
            action: $isSerious ? 'notifikasi_prioritas' : 'tambah',
            module: 'Guru PIC · Keluhan & Tindak Lanjut',
            target: $ticket->ticket_number.' · '.$location->name,
            newValues: [
                'category' => $ticket->categoryLabel(),
                'severity' => $ticket->severity,
                'priority_recipients' => $ticket->priority_recipients,
            ],
            reason: $validated['description'],
            actor: $user,
        );

        $flashMsg = $isSerious
            ? "Tiket {$ticketNumber} berhasil dikirim. Notifikasi prioritas telah diteruskan ke Asisten Lapangan & Kepala Dapur."
            : "Tiket keluhan {$ticketNumber} berhasil dikirim.";

        return redirect()
            ->route('guru-pic.complaints.index')
            ->with('success', $flashMsg);
    }

    private function generateTicketNumber(): string
    {
        $todayCode = now('Asia/Jakarta')->format('Ymd');
        $prefix = "#KLP-{$todayCode}-";

        $last = ComplaintTicket::query()
            ->where('ticket_number', 'like', $prefix.'%')
            ->orderByDesc('ticket_number')
            ->value('ticket_number');

        $nextSeq = 1;
        if (is_string($last) && preg_match('/-(\d+)$/', $last, $matches)) {
            $nextSeq = ((int) $matches[1]) + 1;
        }

        return sprintf('%s%02d', $prefix, $nextSeq);
    }
}
