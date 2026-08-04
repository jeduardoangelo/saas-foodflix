<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Auth;

class AdminAuthenticationService
{
    public function attempt(string $email, string $password): bool
    {
        return Auth::guard('admin')->attempt([
            'email' => $email,
            'password' => $password,
        ]);
    }

    public function logout(): void
    {
        Auth::guard('admin')->logout();
    }
}
