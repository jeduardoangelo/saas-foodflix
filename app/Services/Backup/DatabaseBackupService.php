<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DatabaseBackupService
{
    public function create(): string
    {
        $connectionName = config('database.default');
        $connection = config("database.connections.{$connectionName}");

        if (! is_array($connection) || ($connection['driver'] ?? null) !== 'mysql') {
            throw new RuntimeException('O comando de backup suporta somente uma conexão MySQL como banco padrão.');
        }

        $directory = storage_path('app/backups');
        File::ensureDirectoryExists($directory);

        $filename = 'foodflix-'.now()->format('Y-m-d_His_u').'.sql';
        $path = $directory.DIRECTORY_SEPARATOR.$filename;
        $command = $this->command($connection, $path);

        $process = new Process($command, base_path(), [
            'MYSQL_PWD' => (string) ($connection['password'] ?? ''),
        ]);
        $process->setTimeout(300);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            File::delete($path);

            throw new RuntimeException(
                'Não foi possível criar o backup. Confirme se o mysqldump está instalado e se as credenciais do banco estão corretas.',
                0,
                $exception
            );
        }

        return $path;
    }

    private function command(array $connection, string $path): array
    {
        $command = [
            (string) ($connection['dump_binary'] ?? 'mysqldump'),
            '--user='.(string) ($connection['username'] ?? ''),
            '--single-transaction',
            '--quick',
            '--lock-tables=false',
            '--no-tablespaces',
            '--result-file='.$path,
        ];

        if (! empty($connection['unix_socket'])) {
            $command[] = '--socket='.(string) $connection['unix_socket'];
        } else {
            $command[] = '--host='.(string) ($connection['host'] ?? '127.0.0.1');
            $command[] = '--port='.(string) ($connection['port'] ?? '3306');
        }

        $command[] = (string) ($connection['database'] ?? '');

        return $command;
    }
}
