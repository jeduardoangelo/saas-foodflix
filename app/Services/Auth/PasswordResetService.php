<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class PasswordResetService
{
    public function sendLink(string $broker, string $email): void
    {
        Password::broker($broker)->sendResetLink(['email' => $email]);
    }

    public function reset(string $broker, array $credentials): string
    {
        return Password::broker($broker)->reset(
            [
                'email' => $credentials['email'],
                'password' => $credentials['password'],
                'token' => $credentials['token'],
            ],
            function ($account, string $password) {
                $account->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );
    }
}
