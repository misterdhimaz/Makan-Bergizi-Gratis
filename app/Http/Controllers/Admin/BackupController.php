<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemBackup;
use App\Services\AuditLogService;
use App\Services\DatabaseBackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function __construct(
        private readonly DatabaseBackupService $backupService,
    ) {}

    public function index(): View
    {
        $backups = SystemBackup::query()
            ->with(['creator:id,name', 'restorer:id,name'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.settings.backup', [
            'backups' => $backups,
            'scheduleTime' => (string) config('mbg.backup.schedule_time', '02:00'),
            'retentionDays' => (int) config('mbg.backup.retention_days', 30),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $backup = $this->backupService->createBackup('manual', $request->user());

            return redirect()
                ->route('admin.settings.backup.index')
                ->with('success', "Backup manual {$backup->filename} berhasil dibuat.");
        } catch (RuntimeException $e) {
            return redirect()
                ->route('admin.settings.backup.index')
                ->with('error', $e->getMessage());
        }
    }

    public function download(Request $request, SystemBackup $backup): StreamedResponse|RedirectResponse
    {
        try {
            $this->backupService->assertSafeBackupPath($backup);
        } catch (RuntimeException $e) {
            return redirect()
                ->route('admin.settings.backup.index')
                ->with('error', $e->getMessage());
        }

        AuditLogService::record(
            action: 'unduh_backup',
            module: 'Pengaturan Sistem · Backup & Restore',
            target: $backup->filename,
            newValues: ['size_bytes' => $backup->size_bytes],
            actor: $request->user(),
        );

        return Storage::disk($backup->disk)->download($backup->path, $backup->filename);
    }

    public function restore(Request $request, SystemBackup $backup): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'current_password'],
        ], [
            'password.required' => 'Kata sandi admin wajib diisi untuk konfirmasi pemulihan.',
            'password.current_password' => 'Kata sandi konfirmasi tidak sesuai dengan akun Anda.',
        ]);

        try {
            $preRestore = $this->backupService->restore($backup, $request->user());

            return redirect()
                ->route('admin.settings.backup.index')
                ->with('success', "Basis data berhasil dipulihkan dari {$backup->filename}. Backup pengaman {$preRestore->filename} disimpan dan Log Aktivitas tetap utuh.");
        } catch (RuntimeException $e) {
            return redirect()
                ->route('admin.settings.backup.index')
                ->with('error', $e->getMessage());
        }
    }
}
