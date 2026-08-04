<?php

namespace App\Console\Commands;

use App\Services\Backup\DatabaseBackupService;
use Illuminate\Console\Command;
use Throwable;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database';

    protected $description = 'Cria um dump do banco MySQL em storage/app/backups';

    public function handle(DatabaseBackupService $backup): int
    {
        try {
            $path = $backup->create();
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return 1;
        }

        $this->info("Backup criado com sucesso: {$path}");

        return 0;
    }
}
