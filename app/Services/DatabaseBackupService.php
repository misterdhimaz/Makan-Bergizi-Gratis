<?php

namespace App\Services;

use App\Models\SystemBackup;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class DatabaseBackupService
{
    /**
     * Daftar tabel yang dikecualikan dari dump agar audit log, daftar backup, sesi, cache, dan antrean tetap utuh.
     *
     * @var list<string>
     */
    public const EXCLUDED_TABLES = [
        'audit_logs',
        'system_backups',
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
    ];

    /**
     * Membangun argumen perintah mysqldump dengan --single-transaction dan --ignore-table.
     *
     * @return list<string>
     */
    public function buildDumpCommand(string $database, string $host, string $port, string $username): array
    {
        $mysqldump = (string) config('mbg.backup.mysqldump_path', 'mysqldump');

        $command = [
            $mysqldump,
            '--host='.$host,
            '--port='.$port,
            '--user='.$username,
            '--single-transaction',
            '--routines',
            '--triggers',
            '--no-tablespaces',
        ];

        foreach (self::EXCLUDED_TABLES as $table) {
            $command[] = '--ignore-table='.$database.'.'.$table;
        }

        $command[] = $database;

        return $command;
    }

    /**
     * Memastikan path backup berada di disk privat dan aman dari path traversal.
     */
    public function assertSafeBackupPath(SystemBackup $backup): void
    {
        if (
            $backup->disk !== 'local'
            || ! str_starts_with($backup->path, 'backups/')
            || str_contains($backup->path, '..')
            || ! Storage::disk($backup->disk)->exists($backup->path)
        ) {
            throw new RuntimeException('Berkas backup tidak valid atau tidak ditemukan di penyimpanan privat.');
        }
    }

    /**
     * Membuat backup database (manual, terjadwal, atau pre_restore) di disk privat.
     */
    public function createBackup(string $triggerType = 'manual', ?User $actor = null): SystemBackup
    {
        $disk = 'local';
        $timestamp = now('Asia/Jakarta')->format('Ymd_His');
        $suffix = substr(bin2hex(random_bytes(3)), 0, 6);
        $filename = "backup_ss1_{$triggerType}_{$timestamp}_{$suffix}.sql";
        $relativePath = "backups/{$filename}";

        Storage::disk($disk)->makeDirectory('backups');

        $connectionName = (string) config('database.default');
        $connection = config("database.connections.{$connectionName}", []);
        $driver = (string) ($connection['driver'] ?? 'mysql');

        $auditAction = match ($triggerType) {
            'manual' => 'backup_manual',
            'pre_restore' => 'backup_pre_restore',
            default => 'backup_terjadwal',
        };

        if ($driver === 'mysql') {
            $database = (string) ($connection['database'] ?? 'sim_mbg_selayur');
            $host = (string) ($connection['host'] ?? '127.0.0.1');
            $port = (string) ($connection['port'] ?? '3306');
            $username = (string) ($connection['username'] ?? 'root');
            $password = (string) ($connection['password'] ?? '');

            $command = $this->buildDumpCommand($database, $host, $port, $username);
            $env = $this->buildProcessEnv($password);
            $result = Process::env($env)->run($command);

            if ($result->failed()) {
                Log::error('Backup mysqldump gagal', [
                    'exit_code' => $result->exitCode(),
                    'error' => $result->errorOutput(),
                ]);
                SystemBackup::create([
                    'filename' => $filename,
                    'disk' => $disk,
                    'path' => $relativePath,
                    'size_bytes' => 0,
                    'trigger_type' => $triggerType,
                    'status' => 'failed',
                    'created_by' => $actor?->id,
                ]);

                AuditLogService::record(
                    action: $auditAction,
                    module: 'Pengaturan Sistem · Backup & Restore',
                    target: $filename,
                    newValues: ['status' => 'failed'],
                    reason: 'Backup gagal dijalankan',
                    actor: $actor,
                );

                throw new RuntimeException('Backup database gagal dijalankan. Periksa konfigurasi utilitas database.');
            }

            Storage::disk($disk)->put($relativePath, $result->output());
        } else {
            // Mode non-MySQL (SQLite pengujian): tulis snapshot aman tanpa menyentuh tabel terlarang.
            $excluded = implode(', ', self::EXCLUDED_TABLES);
            $content = "-- REKAGIZI MBG Snapshot ({$driver})\n-- Excluded: {$excluded}\n-- Generated at: ".now('Asia/Jakarta')->toIso8601String()."\n";
            Storage::disk($disk)->put($relativePath, $content);
        }

        $sizeBytes = (int) Storage::disk($disk)->size($relativePath);

        $backup = SystemBackup::create([
            'filename' => $filename,
            'disk' => $disk,
            'path' => $relativePath,
            'size_bytes' => $sizeBytes,
            'trigger_type' => $triggerType,
            'status' => 'success',
            'created_by' => $actor?->id,
        ]);

        AuditLogService::record(
            action: $auditAction,
            module: 'Pengaturan Sistem · Backup & Restore',
            target: $filename,
            newValues: [
                'size_bytes' => $sizeBytes,
                'trigger_type' => $triggerType,
                'excluded_tables' => self::EXCLUDED_TABLES,
            ],
            actor: $actor,
        );

        return $backup;
    }

    /**
     * Memulihkan database dari file backup setelah membuat backup pengaman pre_restore.
     */
    public function restore(SystemBackup $backup, User $actor): SystemBackup
    {
        $this->assertSafeBackupPath($backup);

        // 1. Backup pengaman otomatis sebelum restore
        $preRestoreBackup = $this->createBackup('pre_restore', $actor);

        $connectionName = (string) config('database.default');
        $connection = config("database.connections.{$connectionName}", []);
        $driver = (string) ($connection['driver'] ?? 'mysql');

        try {
            if ($driver === 'mysql') {
                $mysql = (string) config('mbg.backup.mysql_path', 'mysql');
                $database = (string) ($connection['database'] ?? 'sim_mbg_selayur');
                $host = (string) ($connection['host'] ?? '127.0.0.1');
                $port = (string) ($connection['port'] ?? '3306');
                $username = (string) ($connection['username'] ?? 'root');
                $password = (string) ($connection['password'] ?? '');

                $sql = (string) Storage::disk($backup->disk)->get($backup->path);

                // Pengaman tambahan: tolak/bersihkan jika ada perintah yang menyentuh audit_logs atau system_backups
                if (preg_match('/DROP\s+TABLE\s+IF\s+EXISTS\s+`?(audit_logs|system_backups)`?/i', $sql) === 1) {
                    throw new RuntimeException('Berkas backup mengandung definisi tabel yang dilindungi.');
                }

                $result = Process::env($this->buildProcessEnv($password))
                    ->input($sql)
                    ->run([
                        $mysql,
                        '--host='.$host,
                        '--port='.$port,
                        '--user='.$username,
                        $database,
                    ]);

                if ($result->failed()) {
                    Log::error('Restore mysql gagal', [
                        'exit_code' => $result->exitCode(),
                        'error' => $result->errorOutput(),
                    ]);
                    throw new RuntimeException('Proses pemulihan basis data mengembalikan status gagal.');
                }
            }

            $backup->forceFill([
                'restored_at' => now(),
                'restored_by' => $actor->id,
            ])->save();

            AuditLogService::record(
                action: 'restore',
                module: 'Pengaturan Sistem · Backup & Restore',
                target: $backup->filename,
                newValues: [
                    'restored_at' => now('Asia/Jakarta')->toIso8601String(),
                    'pre_restore_backup' => $preRestoreBackup->filename,
                    'audit_logs_preserved' => true,
                ],
                reason: 'Pemulihan basis data dari arsip backup',
                actor: $actor,
            );

            return $preRestoreBackup;
        } catch (Throwable) {
            AuditLogService::record(
                action: 'restore_gagal',
                module: 'Pengaturan Sistem · Backup & Restore',
                target: $backup->filename,
                newValues: [
                    'status' => 'failed',
                    'pre_restore_backup' => $preRestoreBackup->filename,
                ],
                reason: 'Pemulihan basis data gagal dijalankan',
                actor: $actor,
            );

            throw new RuntimeException('Pemulihan basis data gagal dijalankan. Salinan pengaman sebelum pemulihan telah disimpan.');
        }
    }

    /**
     * Menyertakan SystemRoot/WINDIR pada Windows agar socket Winsock tidak gagal (error 10106) di dalam php artisan serve.
     *
     * @return array<string, string>
     */
    private function buildProcessEnv(string $password): array
    {
        $env = [];

        if (PHP_OS_FAMILY === 'Windows') {
            $env['SystemRoot'] = (string) (getenv('SystemRoot') ?: 'C:\\Windows');
            $env['WINDIR'] = (string) (getenv('WINDIR') ?: 'C:\\Windows');
        }

        if ($password !== '') {
            $env['MYSQL_PWD'] = $password;
        }

        return $env;
    }
}
