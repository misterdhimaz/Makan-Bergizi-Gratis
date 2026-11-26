<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SopChecklistType;
use App\Http\Controllers\Controller;
use App\Models\SopChecklist;
use App\Services\DataMasterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SopChecklistController extends Controller
{
    public function __construct(
        private readonly DataMasterService $masterService,
    ) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $type = (string) $request->query('type', 'all');
        $status = (string) $request->query('status', 'all');

        $query = SopChecklist::query()->orderBy('type')->orderBy('code');

        if ($q !== '') {
            $query->where(function ($builder) use ($q): void {
                $builder->where('title', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%")
                    ->orWhere('threshold_value', 'like', "%{$q}%");
            });
        }

        if ($type !== 'all' && $type !== '') {
            $query->where('type', $type);
        }

        if ($status === 'aktif') {
            $query->where('is_active', true);
        } elseif ($status === 'nonaktif') {
            $query->where('is_active', false);
        }

        return view('admin.data-master.checklist-sop', [
            'checklists' => $query->paginate(10)->withQueryString(),
            'types' => SopChecklistType::cases(),
            'filters' => ['q' => $q, 'type' => $type, 'status' => $status],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:40', 'unique:sop_checklists,code'],
            'type' => ['required', Rule::enum(SopChecklistType::class)],
            'title' => ['required', 'string', 'max:150'],
            'threshold_value' => ['nullable', 'string', 'max:120'],
            'frequency' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $checklist = SopChecklist::create([
            ...$validated,
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.data-master.checklist-sop.index')
            ->with('success', "Item SOP {$checklist->title} berhasil ditambahkan.");
    }

    public function update(Request $request, SopChecklist $checklist): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:40', Rule::unique('sop_checklists', 'code')->ignore($checklist->id)],
            'type' => ['required', Rule::enum(SopChecklistType::class)],
            'title' => ['required', 'string', 'max:150'],
            'threshold_value' => ['nullable', 'string', 'max:120'],
            'frequency' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:500'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $old = $checklist->only(['code', 'type', 'title', 'threshold_value', 'frequency', 'description']);
        $checklist->update([
            'code' => $validated['code'],
            'type' => $validated['type'],
            'title' => $validated['title'],
            'threshold_value' => $validated['threshold_value'] ?? null,
            'frequency' => $validated['frequency'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        $this->masterService->recordRevision(
            $checklist,
            $old,
            $checklist->only(['code', 'type', 'title', 'threshold_value', 'frequency', 'description']),
            $validated['reason'] ?? null,
            $request->user(),
        );

        return redirect()
            ->route('admin.data-master.checklist-sop.index')
            ->with('success', "Item SOP {$checklist->title} berhasil diperbarui.");
    }

    public function deactivate(Request $request, SopChecklist $checklist): RedirectResponse
    {
        $this->masterService->setActive($checklist, false, $request->input('reason'), $request->user());

        return redirect()
            ->route('admin.data-master.checklist-sop.index')
            ->with('success', "Item SOP {$checklist->title} dinonaktifkan.");
    }

    public function activate(Request $request, SopChecklist $checklist): RedirectResponse
    {
        $this->masterService->setActive($checklist, true, $request->input('reason'), $request->user());

        return redirect()
            ->route('admin.data-master.checklist-sop.index')
            ->with('success', "Item SOP {$checklist->title} diaktifkan kembali.");
    }
}
