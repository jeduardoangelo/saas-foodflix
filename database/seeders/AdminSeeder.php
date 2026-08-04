<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminSeeder extends Seeder
{
    private const PLACEHOLDER = 'defina-localmente';

    /**
     * Seed the application's initial administrator.
     *
     * @return void
     */
    public function run()
    {
        $email = env('ADMIN_SEED_EMAIL');
        $password = env('ADMIN_SEED_PASSWORD');

        if (! is_string($email) || trim($email) === '' || strpos($email, self::PLACEHOLDER) !== false) {
            throw new RuntimeException(
                'ADMIN_SEED_EMAIL must be defined in the local environment with a valid administrator email.'
            );
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new RuntimeException(
                'ADMIN_SEED_EMAIL must contain a valid email address.'
            );
        }

        if (! is_string($password) || trim($password) === '' || $password === self::PLACEHOLDER) {
            throw new RuntimeException(
                'ADMIN_SEED_PASSWORD must be defined in the local environment and cannot use the example placeholder.'
            );
        }

        Admin::updateOrCreate(
            ['email' => trim($email)],
            [
                'name' => 'Administrador',
                'password' => Hash::make($password),
            ]
        );
    }
}
