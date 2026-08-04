<?php

namespace Tests\Feature;

use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class AdminSeederTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_seeder_fails_when_required_environment_variables_are_missing(): void
    {
        $originalEmail = $this->environmentValue('ADMIN_SEED_EMAIL');
        $originalPassword = $this->environmentValue('ADMIN_SEED_PASSWORD');

        try {
            $this->setEnvironmentValue('ADMIN_SEED_EMAIL', null);
            $this->setEnvironmentValue('ADMIN_SEED_PASSWORD', null);

            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('ADMIN_SEED_EMAIL must be defined');

            (new AdminSeeder())->run();
        } finally {
            $this->setEnvironmentValue('ADMIN_SEED_EMAIL', $originalEmail);
            $this->setEnvironmentValue('ADMIN_SEED_PASSWORD', $originalPassword);
        }
    }

    public function test_admin_seeder_creates_an_admin_with_a_hashed_password(): void
    {
        $originalEmail = $this->environmentValue('ADMIN_SEED_EMAIL');
        $originalPassword = $this->environmentValue('ADMIN_SEED_PASSWORD');
        $email = 'seeded-admin@example.com';
        $password = 'local-test-password';

        try {
            $this->setEnvironmentValue('ADMIN_SEED_EMAIL', $email);
            $this->setEnvironmentValue('ADMIN_SEED_PASSWORD', $password);

            (new AdminSeeder())->run();

            $admin = \App\Models\Admin::where('email', $email)->firstOrFail();

            $this->assertSame('Administrador', $admin->name);
            $this->assertNotSame($password, $admin->password);
            $this->assertTrue(Hash::check($password, $admin->password));
        } finally {
            $this->setEnvironmentValue('ADMIN_SEED_EMAIL', $originalEmail);
            $this->setEnvironmentValue('ADMIN_SEED_PASSWORD', $originalPassword);
        }
    }

    /**
     * @return mixed
     */
    private function environmentValue(string $key)
    {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        if (array_key_exists($key, $_SERVER)) {
            return $_SERVER[$key];
        }

        $value = getenv($key);

        return $value === false ? null : $value;
    }

    /**
     * @param mixed $value
     */
    private function setEnvironmentValue(string $key, $value): void
    {
        if ($value === null) {
            unset($_ENV[$key], $_SERVER[$key]);
            putenv($key);

            return;
        }

        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv($key.'='.$value);
    }
}
