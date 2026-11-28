<?php

namespace App\Http\Controllers\AhliGizi;

use App\Enums\SopChecklistType;
use App\Http\Controllers\Controller;
use App\Models\FoodSafetyCheck;
use App\Models\FoodSafetyFinding;
use App\Models\SopChecklist;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FoodSafetyController extends Controller
{
    public function index(Request $request): View
    {
        $date = (string) $request->query('tanggal', now('Asia/Jakarta')->toDateString());
        $shift = (string) $request->query('shift', 'Pagi');
        if (! in_array($shift, ['Pagi', 'Siang', 'Malam'], true)) {
            $shift = 'Pagi';
        }
        $activeTab = (string) $request->query('tab', 'pemeriksaan');

        $currentCheck = FoodSafetyCheck::query()
            ->whereDate('check_date', $date)
            ->where('shift', $shift)
            ->first();

        $todayChecks = FoodSafetyCheck::query()
            ->whereDate('check_date', $date)
            ->get()
            ->keyBy('shift');

        $hygieneChecklist = SopChecklist::query()
            ->where('type', SopChecklistType::KEBERSIHAN)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $apdChecklist = SopChecklist::query()
            ->where('type', SopChecklistType::APD)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $defaultTemps = [
            ['label' => 'Bahan baku dingin / kulkas', 'standard' => '0 – 4 °C', 'actual' => 3.2, 'compliant' => true],
            ['label' => 'Bahan baku beku / freezer', 'standard' => '≤ -18 °C', 'actual' => -19.0, 'compliant' => true],
            ['label' => 'Penyimpanan bahan kering', 'standard' => '18 – 25 °C', 'actual' => 22.5, 'compliant' => true],
            ['label' => 'Makanan matang / inti masakan', 'standard' => '≥ 75 °C', 'actual' => 82.0, 'compliant' => true],
            ['label' => 'Makanan siap saji di wadah distribusi', 'standard' => '≥ 60 °C', 'actual' => 56.5, 'compliant' => false],
            ['label' => 'Ruang pengolahan / area pemorsian', 'standard' => '≤ 25 °C', 'actual' => 23.8, 'compliant' => true],
        ];

        $temps = $currentCheck?->temperatures_json ?? $defaultTemps;
        $apds = $currentCheck?->apd_json ?? $apdChecklist->map(fn ($item): array => [
            'label' => $item->title,
            'compliant' => true,
        ])->values()->all();

        // Riwayat 7 hari terakhir (per tanggal, 3 shift)
        $historyRows = [];
        for ($i = 0; $i < 7; $i++) {
            $d = now('Asia/Jakarta')->subDays($i)->toDateString();
            $dayChecks = FoodSafetyCheck::query()->whereDate('check_date', $d)->get()->keyBy('shift');
            $historyRows[] = [
                'date_label' => Carbon::parse($d)->translatedFormat('d M Y'),
                'pagi' => $dayChecks->get('Pagi'),
                'siang' => $dayChecks->get('Siang'),
                'malam' => $dayChecks->get('Malam'),
            ];
        }

        $findings = FoodSafetyFinding::with('handler')
            ->orderByDesc('finding_date')
            ->orderByDesc('id')
            ->get();

        return view('ahli-gizi.keamanan-pangan', [
            'selectedDate' => $date,
            'selectedShift' => $shift,
            'activeTab' => $activeTab,
            'currentCheck' => $currentCheck,
            'todayChecks' => $todayChecks,
            'temps' => $temps,
            'hygieneChecklist' => $hygieneChecklist,
            'apds' => $apds,
            'historyRows' => $historyRows,
            'findings' => $findings,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'check_date' => ['required', 'date'],
            'shift' => ['required', 'in:Pagi,Siang,Malam'],
            'compliant_volunteers' => ['required', 'integer', 'min:0', 'max:35'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'temps' => ['required', 'array'],
            'apds' => ['nullable', 'array'],
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('food-safety-attachments', 'local');
        }

        $temps = collect($validated['temps'])->map(fn ($row): array => [
            'label' => (string) ($row['label'] ?? ''),
            'standard' => (string) ($row['standard'] ?? ''),
            'actual' => (float) ($row['actual'] ?? 0),
            'compliant' => filter_var($row['compliant'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ])->values()->all();

        $apds = collect($validated['apds'] ?? [])->map(fn ($row): array => [
            'label' => (string) ($row['label'] ?? ''),
            'compliant' => filter_var($row['compliant'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ])->values()->all();

        $check = FoodSafetyCheck::updateOrCreate(
            ['check_date' => $validated['check_date'], 'shift' => $validated['shift']],
            [
                'temperatures_json' => $temps,
                'apd_json' => $apds,
                'total_volunteers' => 35,
                'compliant_volunteers' => (int) $validated['compliant_volunteers'],
                'attachment_path' => $attachmentPath,
                'notes' => $validated['notes'] ?? null,
                'inspector_id' => $request->user()?->id,
                'checked_at' => now(),
            ]
        );

        // Catat temuan terbuka jika ada suhu atau APD yang tidak sesuai
        foreach ($temps as $t) {
            if (! $t['compliant']) {
                FoodSafetyFinding::firstOrCreate(
                    [
                        'finding_date' => $validated['check_date'],
                        'type' => 'Suhu',
                        'description' => $t['label'].' ('.$t['actual'].' °C) di luar standar SOP',
                    ],
                    [
                        'status' => 'terbuka',
                        'handler_note' => 'Menunggu Kepala Dapur',
                    ]
                );
            }
        }

        AuditLogService::record(
            action: 'simpan',
            module: 'Ahli Gizi · Keamanan Pangan & APD',
            target: $validated['check_date'].' · Shift '.$validated['shift'],
            newValues: ['compliant_volunteers' => $check->compliant_volunteers],
            actor: $request->user(),
        );

        return redirect()
            ->route('ahli-gizi.safety.index', ['tanggal' => $validated['check_date'], 'shift' => $validated['shift']])
            ->with('success', 'Pemeriksaan Keamanan Pangan & APD berhasil disimpan.');
    }
}
