<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_password_recovery_pages_and_login_links_are_available_for_both_guards(): void
    {
        $this->get('/login')->assertSee(route('site.password.request'));
        $this->get('/forgot-password')->assertOk()->assertSee('Esqueci minha senha');
        $this->get('/admin/login')->assertSee(route('admin.password.request'));
        $this->get('/admin/forgot-password')->assertOk()->assertSee('Esqueci minha senha');
    }

    public function test_existing_customer_receives_reset_link_with_customer_url(): void
    {
        Notification::fake();
        $user = $this->createUser('cliente-reset@example.com');

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHas('status', $this->genericSentMessage());

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            return strpos($notification->toMail($user)->actionUrl, '/reset-password/'.$notification->token) !== false
                && strpos($notification->toMail($user)->actionUrl, 'email=cliente-reset%40example.com') !== false;
        });
    }

    public function test_existing_admin_receives_reset_link_with_admin_url(): void
    {
        Notification::fake();
        $admin = $this->createAdmin('admin-reset@example.com');

        $this->post('/admin/forgot-password', ['email' => $admin->email])
            ->assertSessionHas('status', $this->genericSentMessage());

        Notification::assertSentTo($admin, ResetPassword::class, function ($notification) use ($admin) {
            return strpos($notification->toMail($admin)->actionUrl, '/admin/reset-password/'.$notification->token) !== false;
        });
    }

    public function test_unknown_and_existing_emails_receive_identical_response(): void
    {
        Notification::fake();
        $user = $this->createUser('known@example.com');

        $known = $this->post('/forgot-password', ['email' => $user->email]);
        $unknown = $this->post('/forgot-password', ['email' => 'unknown@example.com']);

        $known->assertSessionHas('status', $this->genericSentMessage());
        $unknown->assertSessionHas('status', $this->genericSentMessage());
    }

    public function test_customer_can_reset_password_and_token_cannot_be_reused(): void
    {
        $user = $this->createUser('reuse@example.com');
        $token = Password::broker('users')->createToken($user);
        $payload = $this->resetPayload($user->email, $token, 'nova-senha-segura');

        $this->post('/reset-password', $payload)
            ->assertRedirect('/login')
            ->assertSessionHas('status', 'Senha redefinida com sucesso. Você já pode entrar.');

        $this->assertTrue(Hash::check('nova-senha-segura', $user->fresh()->password));
        $this->post('/reset-password', $payload)
            ->assertSessionHasErrors(['email' => 'Este link de redefinição é inválido, expirou ou já foi utilizado.']);

        $this->post('/login', ['email' => $user->email, 'password' => 'senha-original'])
            ->assertSessionHasErrors(['email']);
        $this->post('/login', ['email' => $user->email, 'password' => 'nova-senha-segura'])
            ->assertRedirect('/');
    }

    public function test_expired_token_is_rejected(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-04 10:00:00'));
        $user = $this->createUser('expired@example.com');
        $oldHash = $user->password;
        $token = Password::broker('users')->createToken($user);

        Carbon::setTestNow(Carbon::parse('2026-08-04 11:01:00'));

        $this->post('/reset-password', $this->resetPayload($user->email, $token, 'nova-senha-segura'))
            ->assertSessionHasErrors(['email' => 'Este link de redefinição é inválido, expirou ou já foi utilizado.']);
        $this->assertSame($oldHash, $user->fresh()->password);
    }

    public function test_customer_and_admin_tokens_are_isolated_even_with_the_same_email(): void
    {
        $email = 'same-email@example.com';
        $user = $this->createUser($email);
        $admin = $this->createAdmin($email);
        $userToken = Password::broker('users')->createToken($user);
        $adminToken = Password::broker('admins')->createToken($admin);

        $this->post('/admin/reset-password', $this->resetPayload($email, $userToken, 'admin-nova-senha'))
            ->assertSessionHasErrors(['email']);
        $this->post('/reset-password', $this->resetPayload($email, $adminToken, 'cliente-nova-senha'))
            ->assertSessionHasErrors(['email']);

        $this->assertTrue(Hash::check('senha-original', $user->fresh()->password));
        $this->assertTrue(Hash::check('senha-original', $admin->fresh()->password));
    }

    public function test_admin_can_reset_password_and_use_it_to_login(): void
    {
        $admin = $this->createAdmin('admin-login-reset@example.com');
        $token = Password::broker('admins')->createToken($admin);

        $this->post('/admin/reset-password', $this->resetPayload($admin->email, $token, 'admin-nova-senha'))
            ->assertRedirect('/admin/login');

        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'senha-original'])
            ->assertSessionHasErrors(['email']);
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'admin-nova-senha'])
            ->assertRedirect('/admin/dashboard');
    }

    private function createUser(string $email): User
    {
        return User::create([
            'name' => 'Cliente Reset',
            'cpf' => '529.982.247-25',
            'email' => $email,
            'phone' => '(11) 99999-1234',
            'password' => 'senha-original',
        ]);
    }

    private function createAdmin(string $email): Admin
    {
        return Admin::create([
            'name' => 'Admin Reset',
            'email' => $email,
            'password' => Hash::make('senha-original'),
        ]);
    }

    private function resetPayload(string $email, string $token, string $password): array
    {
        return [
            'email' => $email,
            'token' => $token,
            'password' => $password,
            'password_confirmation' => $password,
        ];
    }

    private function genericSentMessage(): string
    {
        return 'Se existir uma conta com este email, enviaremos um link para redefinir a senha.';
    }
}
