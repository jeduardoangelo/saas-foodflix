<?php

namespace Tests\Feature;

use App\Services\Backup\DatabaseBackupService;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class HardeningTest extends TestCase
{
    public function test_payments_log_uses_a_dedicated_daily_channel(): void
    {
        $this->assertSame('daily', config('logging.channels.payments.driver'));
        $this->assertSame(storage_path('logs/payments.log'), config('logging.channels.payments.path'));
        $this->assertSame('error', config('logging.channels.payments.level'));
    }

    public function test_backup_command_reports_the_created_file(): void
    {
        $backup = Mockery::mock(DatabaseBackupService::class);
        $backup->shouldReceive('create')->once()->andReturn(storage_path('app/backups/foodflix-test.sql'));
        $this->app->instance(DatabaseBackupService::class, $backup);

        $this->artisan('backup:database')
            ->expectsOutput('Backup criado com sucesso: '.storage_path('app/backups/foodflix-test.sql'))
            ->assertExitCode(0);
    }

    public function test_backup_command_fails_clearly_when_backup_cannot_be_created(): void
    {
        $backup = Mockery::mock(DatabaseBackupService::class);
        $backup->shouldReceive('create')->once()->andThrow(new RuntimeException('mysqldump indisponível.'));
        $this->app->instance(DatabaseBackupService::class, $backup);

        $this->artisan('backup:database')
            ->expectsOutput('mysqldump indisponível.')
            ->assertExitCode(1);
    }
}
