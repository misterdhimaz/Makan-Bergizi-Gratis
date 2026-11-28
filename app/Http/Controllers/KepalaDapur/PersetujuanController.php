<?php

namespace App\Http\Controllers\KepalaDapur;

use App\Enums\MenuStatus;
use App\Exceptions\MenuStatusConflictException;
use App\Http\Controllers\Controller;
use App\Http\Requests\KepalaDapur\MenuDecisionRequest;
use App\Models\DailyMenu;
use App\Models\DataRevision;
use App\Services\KepalaDapurService;
use App\Services\MenuWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersetujuanController extends Controller
{
    public function index(Request $request, KepalaDapurService $service): View
    {
        $tab = $request->string('tab')->toString();
        if (! in_array($tab, ['menunggu', 'koreksi', 'disetujui'], true)) {
            $tab = 'menunggu';
        }

        $menusPending = DailyMenu::query()
            ->with(['latestPaguCheck.checker', 'categoryPortions.recipientCategory', 'components'])
            ->where('status', MenuStatus::LOLOS_PAGU->value)
            ->orderBy('operational_date')
            ->get();

        $revisionsPending = DataRevision::query()
            ->with(['requester', 'revisable'])
            ->where('status', 'menunggu')
            ->whereNull('approved_at')
            ->orderByDesc('created_at')
            ->get();

        $menusApproved = DailyMenu::query()
            ->with(['approvedBy'])
            ->where('status', MenuStatus::DISETUJUI->value)
            ->orderByDesc('operational_date')
            ->get();

        $revisionsDecided = DataRevision::query()
            ->with(['requester', 'approver', 'revisable'])
            ->whereIn('status', ['disetujui', 'ditolak'])
            ->orWhereNotNull('approved_at')
            ->orderByDesc('approved_at')
            ->get();

        return view('kepala-dapur.persetujuan', [
            'tab' => $tab,
            'menusPending' => $menusPending,
            'revisionsPending' => $revisionsPending,
            'menusApproved' => $menusApproved,
            'revisionsDecided' => $revisionsDecided,
            'badges' => $service->sidebarBadges(),
        ]);
    }

    public function decideMenu(
        MenuDecisionRequest $request,
        DailyMenu $dailyMenu,
        MenuWorkflow $menuWorkflow,
    ): RedirectResponse {
        try {
            $actor = $request->user();
            if ($request->input('decision') === 'setujui') {
                $menuWorkflow->approveByKepalaDapur($dailyMenu, $actor, $request->input('catatan'));
                $message = 'Menu berhasil disetujui dan dikunci.';
            } else {
                $menuWorkflow->requestRevisionByKepalaDapur($dailyMenu, $actor, (string) $request->input('catatan'));
                $message = 'Permintaan revisi menu berhasil dikirim ke Ahli Gizi.';
            }

            return redirect()
                ->route('kepala-dapur.persetujuan.index', ['tab' => 'menunggu'])
                ->with('success', $message);
        } catch (MenuStatusConflictException) {
            return redirect()
                ->route('kepala-dapur.persetujuan.index', ['tab' => 'menunggu'])
                ->with('error', 'Status menu sudah berubah, muat ulang halaman.')
                ->setStatusCode(409);
        }
    }

    public function approveRevision(
        Request $request,
        DataRevision $revision,
        KepalaDapurService $service,
    ): RedirectResponse {
        $service->approveRevision($revision, $request->user(), $request->input('decision_notes'));

        return redirect()
            ->route('kepala-dapur.persetujuan.index', ['tab' => 'koreksi'])
            ->with('success', 'Koreksi data terkunci berhasil disetujui dan dicatat di Audit Trail.');
    }

    public function rejectRevision(
        Request $request,
        DataRevision $revision,
        KepalaDapurService $service,
    ): RedirectResponse {
        $validated = $request->validate([
            'decision_notes' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'decision_notes.required' => 'Alasan penolakan wajib diisi.',
            'decision_notes.min' => 'Alasan penolakan wajib diisi minimal 5 karakter.',
        ]);

        $service->rejectRevision($revision, $request->user(), $validated['decision_notes']);

        return redirect()
            ->route('kepala-dapur.persetujuan.index', ['tab' => 'koreksi'])
            ->with('success', 'Permintaan koreksi ditolak dan dicatat di Audit Trail.');
    }
}
