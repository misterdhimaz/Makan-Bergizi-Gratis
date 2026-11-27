<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class RunDailyBackupCommand extends Command
{
    protected $signature = 'mbg:backup-daily';

    protected $description = 'Menjalankan backup harian database SPPG REKAGIZI MBG tanpa menyertakan audit_logs';

    public function handle(DatabaseBackupService $backupService): int
    {
        $backup = $backupService->createBackup('scheduled');
        $this->info("Backup terjadwal selesai: {$backup->filename}");

        return self::SUCCESS;
    }
}
