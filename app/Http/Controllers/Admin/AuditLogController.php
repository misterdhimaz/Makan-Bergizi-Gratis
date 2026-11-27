<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Menampilkan daftar Log Aktivitas (append-only, 10 baris per halaman) dengan filter.
     */
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $peran = (string) $request->query('peran', $request->query('role', 'all'));
        $modul = (string) $request->query('modul', $request->query('module', 'all'));
        $aksi = (string) $request->query('aksi', $request->query('action', 'all'));
        $rentang = (string) $request->query('rentang', $request->query('range', 'semua'));

        $query = AuditLog::query()
            ->with('approver:id,name')
            ->orderByDesc('occurred_at')
            ->orderByDesc('id');

        if ($q !== '') {
            $query->where(function ($builder) use ($q): void {
                $builder->where('user_name_snapshot', 'like', "%{$q}%")
                    ->orWhere('target', 'like', "%{$q}%")
                    ->orWhere('module', 'like', "%{$q}%")
                    ->orWhere('action', 'like', "%{$q}%")
                    ->orWhere('reason', 'like', "%{$q}%")
                    ->orWhere('ip_address', 'like', "%{$q}%");
            });
        }

        if ($peran !== 'all' && $peran !== '') {
            $query->where('role', $peran);
        }

        if ($modul !== 'all' && $modul !== '') {
            $query->where('module', $modul);
        }

        if ($aksi !== 'all' && $aksi !== '') {
            $query->where('action', $aksi);
        }

        if ($rentang === 'hari-ini') {
            $query->where('occurred_at', '>=', now()->startOfDay());
        } elseif ($rentang === '7-hari') {
            $query->where('occurred_at', '>=', now()->subDays(7)->startOfDay());
        } elseif ($rentang === '30-hari') {
            $query->where('occurred_at', '>=', now()->subDays(30)->startOfDay());
        }

        $logs = $query->paginate(10)->withQueryString();

        $stats = [
            'total' => AuditLog::query()->count(),
            'hari_ini' => AuditLog::query()->where('occurred_at', '>=', now()->startOfDay())->count(),
            'login_gagal_24_jam' => AuditLog::query()
                ->where('action', 'login_gagal')
                ->where('occurred_at', '>=', now()->subHours(24))
                ->count(),
            'perubahan_sistem' => AuditLog::query()
                ->whereIn('action', [
                    'tambah',
                    'ubah',
                    'nonaktifkan',
                    'aktifkan',
                    'reset_sandi',
                    'ubah_pengaturan',
                    'revisi',
                    'backup',
                    'restore',
                ])
                ->count(),
        ];

        $roleOptions = collect(UserRole::cases())
            ->map(fn (UserRole $r): string => $r->label())
            ->merge(AuditLog::query()->distinct()->pluck('role'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $moduleOptions = AuditLog::query()
            ->distinct()
            ->orderBy('module')
            ->pluck('module')
            ->filter()
            ->values();

        $actionOptions = AuditLog::query()
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->filter()
            ->values();

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'stats' => $stats,
            'roleOptions' => $roleOptions,
            'moduleOptions' => $moduleOptions,
            'actionOptions' => $actionOptions,
            'filters' => [
                'q' => $q,
                'peran' => $peran,
                'modul' => $modul,
                'aksi' => $aksi,
                'rentang' => $rentang,
            ],
        ]);
    }

    /**
     * Mengembalikan detail satu entri Log Aktivitas untuk drawer (read-only).
     */
    public function show(AuditLog $log): JsonResponse
    {
        $log->loadMissing('approver:id,name');

        return response()->json([
            'id' => $log->id,
            'occurred_at' => $log->occurred_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i:s').' WIB',
            'user_name_snapshot' => $log->user_name_snapshot,
            'role' => $log->role,
            'action' => $log->action,
            'module' => $log->module,
            'target' => $log->target ?? '-',
            'ip_address' => $log->ip_address ?? '-',
            'reason' => $log->reason,
            'approver_name' => $log->approver?->name,
            'old_values' => AuditLogService::sanitize($log->old_values),
            'new_values' => AuditLogService::sanitize($log->new_values),
        ]);
    }
}
