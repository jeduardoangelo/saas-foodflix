<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Site\LoginRequest;
use App\Http\Requests\Site\RegisterCustomerRequest;
use App\Http\Resources\Api\ErrorResource;
use App\Http\Resources\Api\MessageResource;
use App\Http\Resources\Api\TokenResource;
use App\Services\Auth\CustomerAuthenticationService;
use App\Services\Auth\PasswordResetService;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    private $authentication;
    private $passwords;

    public function __construct(
        CustomerAuthenticationService $authentication,
        PasswordResetService $passwords
    ) {
        $this->authentication = $authentication;
        $this->passwords = $passwords;
    }

    public function register(RegisterCustomerRequest $request)
    {
        $user = $this->authentication->register($request->validated())->load('addresses');
        $token = auth('api')->login($user);

        return (new TokenResource(compact('token', 'user')))
            ->response()
            ->setStatusCode(201);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        $token = auth('api')->attempt($credentials);

        if (! $token) {
            return $this->error('invalid_credentials', 'As credenciais informadas são inválidas.', 401);
        }

        $user = auth('api')->user()->load('addresses');

        return new TokenResource(compact('token', 'user'));
    }

    public function logout()
    {
        auth('api')->logout();

        return new MessageResource(['message' => 'Sessão encerrada com sucesso.']);
    }

    public function refresh()
    {
        $guard = auth('api');
        $token = $guard->refresh();
        $user = $guard->setToken($token)->user()->load('addresses');

        return new TokenResource(compact('token', 'user'));
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $this->passwords->sendLink('users', $request->validated()['email']);

        return new MessageResource([
            'message' => 'Se existir uma conta com este email, enviaremos um link para redefinir a senha.',
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $status = $this->passwords->reset('users', $request->validated());

        if ($status !== Password::PASSWORD_RESET) {
            return $this->error(
                'invalid_reset_token',
                'Este token de redefinição é inválido, expirou ou já foi utilizado.',
                422
            );
        }

        return new MessageResource(['message' => 'Senha redefinida com sucesso.']);
    }

    private function error(string $code, string $message, int $status)
    {
        return (new ErrorResource(compact('code', 'message')))
            ->response()
            ->setStatusCode($status);
    }
}
