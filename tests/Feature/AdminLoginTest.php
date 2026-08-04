<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        RateLimiter::clear('rate-limit-admin@example.com|127.0.0.1');

        parent::tearDown();
    }

    public function test_admin_login_page_is_publicly_accessible(): void
    {
        $response = $this->get('/admin/login');

        $response->assertOk();
        $response->assertSee('Foodflix');
        $response->assertSee('Painel de Controle');
        $response->assertSee('Esqueceu sua senha?');
    }

    public function test_unauthenticated_visitors_are_redirected_to_admin_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/dashboard')->assertRedirect('/admin/login');
        $this->post('/admin/logout')->assertRedirect('/admin/login');
    }

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $password = 'valid-admin-password';
        $admin = $this->createAdmin('valid-admin@example.com', $password);

        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => $password,
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Login realizado com sucesso');
    }

    public function test_client_credentials_are_rejected_by_admin_guard(): void
    {
        $password = 'client-only-password';
        $user = User::create([
            'name' => 'Cliente Isolado',
            'cpf' => '529.982.247-25',
            'email' => 'client-only@example.com',
            'password' => Hash::make($password),
        ]);

        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors([
            'email' => 'As credenciais informadas são inválidas.',
        ]);
        $this->assertGuest('admin');
    }

    public function test_invalid_login_uses_the_same_generic_error_for_existing_and_unknown_emails(): void
    {
        $admin = $this->createAdmin('generic-error-admin@example.com', 'correct-password');

        $existingEmailResponse = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ]);
        $unknownEmailResponse = $this->post('/admin/login', [
            'email' => 'unknown-admin@example.com',
            'password' => 'wrong-password',
        ]);

        $error = 'As credenciais informadas são inválidas.';
        $existingEmailResponse->assertSessionHasErrors(['email' => $error]);
        $unknownEmailResponse->assertSessionHasErrors(['email' => $error]);
        $this->assertGuest('admin');
    }

    public function test_admin_login_validates_required_fields(): void
    {
        $response = $this->from('/admin/login')->post('/admin/login', []);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest('admin');
    }

    public function test_authenticated_admin_is_redirected_away_from_login_page(): void
    {
        $admin = $this->createAdmin('already-authenticated@example.com', 'password');

        $this->actingAs($admin, 'admin')
            ->get('/admin/login')
            ->assertRedirect('/admin/dashboard');
    }

    public function test_authenticated_client_cannot_access_admin_pages(): void
    {
        $user = User::create([
            'name' => 'Cliente Web',
            'cpf' => '111.444.777-35',
            'email' => 'authenticated-client@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user, 'web')
            ->get('/admin/dashboard')
            ->assertRedirect('/admin/login');
    }

    public function test_admin_can_logout(): void
    {
        $admin = $this->createAdmin('logout-admin@example.com', 'password');

        $response = $this->actingAs($admin, 'admin')->post('/admin/logout');

        $response->assertRedirect('/admin/login');
        $this->assertGuest('admin');
    }

    public function test_admin_login_is_rate_limited(): void
    {
        $payload = [
            'email' => 'rate-limit-admin@example.com',
            'password' => 'invalid-password',
        ];

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post('/admin/login', $payload)->assertStatus(302);
        }

        $this->post('/admin/login', $payload)
            ->assertStatus(429)
            ->assertSee('Muitas tentativas de login. Aguarde um minuto e tente novamente.');
    }

    private function createAdmin(string $email, string $password): Admin
    {
        return Admin::create([
            'name' => 'Administrador Teste',
            'email' => $email,
            'password' => Hash::make($password),
        ]);
    }
}
