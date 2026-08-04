<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerAuthenticationService
{
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'cpf' => $data['cpf'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->addresses()->create([
                'street' => $data['street'],
                'number' => $data['number'],
                'neighborhood' => $data['neighborhood'],
                'city' => $data['city'],
                'complement' => $data['complement'] ?? null,
                'is_default' => true,
            ]);

            return $user;
        });
    }

    public function login(User $user): void
    {
        Auth::guard('web')->login($user);
    }

    public function attempt(string $email, string $password): bool
    {
        return Auth::guard('web')->attempt([
            'email' => $email,
            'password' => $password,
        ]);
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();
    }
}
