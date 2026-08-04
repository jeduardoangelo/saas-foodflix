<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class CustomerAuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        RateLimiter::clear('cliente@example.com|127.0.0.1');
        RateLimiter::clear('desconhecido@example.com|127.0.0.1');

        parent::tearDown();
    }

    public function test_login_and_registration_pages_are_public(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Acesse sua Conta')
            ->assertSee('Esqueceu sua senha?')
            ->assertSee('Cadastre-se');

        $this->get('/register')
            ->assertOk()
            ->assertSee('Crie sua Conta')
            ->assertSee('Dados Pessoais')
            ->assertSee('Endereço de Entrega')
            ->assertSee('Segurança');
    }

    public function test_customer_registration_creates_user_and_default_address_and_authenticates(): void
    {
        $response = $this->post('/register', $this->validRegistrationData());

        $response->assertRedirect('/');

        $user = User::where('email', 'cliente@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user, 'web');
        $this->assertTrue(Hash::check('senha-segura', $user->password));
        $this->assertNotSame('senha-segura', $user->password);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Maria da Silva',
            'cpf' => '529.982.247-25',
            'email' => 'cliente@example.com',
        ]);
        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'street' => 'Rua das Flores',
            'number' => '123',
            'neighborhood' => 'Centro',
            'city' => 'São Paulo',
            'complement' => 'Apto 12',
            'is_default' => true,
        ]);
        $this->assertCount(1, $user->addresses);
    }

    public function test_registration_normalizes_an_unmasked_cpf(): void
    {
        $data = $this->validRegistrationData();
        $data['cpf'] = '52998224725';

        $this->post('/register', $data)->assertRedirect('/');

        $this->assertDatabaseHas('users', ['cpf' => '529.982.247-25']);
    }

    public function test_user_model_hashes_plain_passwords_without_hashing_existing_hashes_twice(): void
    {
        $user = User::create([
            'name' => 'Maria da Silva',
            'cpf' => '529.982.247-25',
            'email' => 'cliente@example.com',
            'password' => 'senha-segura',
        ]);

        $this->assertTrue(Hash::check('senha-segura', $user->password));

        $existingHash = Hash::make('outra-senha');
        $user->password = $existingHash;

        $this->assertSame($existingHash, $user->password);
    }

    public function test_registration_rejects_invalid_cpf_password_confirmation_and_missing_address(): void
    {
        $data = $this->validRegistrationData();
        $data['cpf'] = '111.111.111-11';
        $data['password_confirmation'] = 'outra-senha';
        unset($data['street']);

        $response = $this->from('/register')->post('/register', $data);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['cpf', 'password', 'street']);
        $this->assertGuest('web');
        $this->assertDatabaseMissing('users', ['email' => 'cliente@example.com']);
    }

    public function test_registration_rejects_duplicate_email_and_cpf_with_clear_messages(): void
    {
        User::create([
            'name' => 'Cliente Existente',
            'cpf' => '529.982.247-25',
            'email' => 'cliente@example.com',
            'password' => Hash::make('senha-segura'),
        ]);

        $response = $this->from('/register')->post('/register', $this->validRegistrationData());

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'cpf' => 'Este CPF já está cadastrado.',
            'email' => 'Este email já está cadastrado.',
        ]);
    }

    public function test_customer_can_login_with_web_guard_and_logout(): void
    {
        $user = User::create([
            'name' => 'Maria da Silva',
            'cpf' => '529.982.247-25',
            'email' => 'cliente@example.com',
            'password' => Hash::make('senha-segura'),
        ]);

        $this->post('/login', [
            'email' => 'CLIENTE@EXAMPLE.COM',
            'password' => 'senha-segura',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($user, 'web');

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest('web');
    }

    public function test_invalid_login_always_uses_the_same_generic_message(): void
    {
        User::create([
            'name' => 'Maria da Silva',
            'cpf' => '529.982.247-25',
            'email' => 'cliente@example.com',
            'password' => Hash::make('senha-correta'),
        ]);

        $existingEmail = $this->post('/login', [
            'email' => 'cliente@example.com',
            'password' => 'senha-errada',
        ]);
        $unknownEmail = $this->post('/login', [
            'email' => 'desconhecido@example.com',
            'password' => 'senha-errada',
        ]);

        $message = 'As credenciais informadas são inválidas.';
        $existingEmail->assertSessionHasErrors(['email' => $message]);
        $unknownEmail->assertSessionHasErrors(['email' => $message]);
        $this->assertGuest('web');
    }

    public function test_admin_credentials_cannot_authenticate_the_web_guard(): void
    {
        Admin::create([
            'name' => 'Administrador',
            'email' => 'cliente@example.com',
            'password' => Hash::make('senha-segura'),
        ]);

        $this->post('/login', [
            'email' => 'cliente@example.com',
            'password' => 'senha-segura',
        ])->assertSessionHasErrors([
            'email' => 'As credenciais informadas são inválidas.',
        ]);

        $this->assertGuest('web');
    }

    public function test_authenticated_customer_cannot_reopen_guest_pages(): void
    {
        $user = User::create([
            'name' => 'Maria da Silva',
            'cpf' => '529.982.247-25',
            'email' => 'cliente@example.com',
            'password' => Hash::make('senha-segura'),
        ]);

        $this->actingAs($user, 'web')->get('/login')->assertRedirect('/');
        $this->actingAs($user, 'web')->get('/register')->assertRedirect('/');
    }

    private function validRegistrationData(): array
    {
        return [
            'name' => 'Maria da Silva',
            'cpf' => '529.982.247-25',
            'email' => 'cliente@example.com',
            'street' => 'Rua das Flores',
            'number' => '123',
            'neighborhood' => 'Centro',
            'city' => 'São Paulo',
            'complement' => 'Apto 12',
            'password' => 'senha-segura',
            'password_confirmation' => 'senha-segura',
        ];
    }
}
