<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\Auth\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    private $passwords;

    public function __construct(PasswordResetService $passwords)
    {
        $this->passwords = $passwords;
    }

    public function requestForm(): View
    {
        return view('admin.auth.forgot-password');
    }

    public function sendLink(ForgotPasswordRequest $request): RedirectResponse
    {
        $this->passwords->sendLink('admins', $request->validated()['email']);

        return back()->with('status', 'Se existir uma conta com este email, enviaremos um link para redefinir a senha.');
    }

    public function resetForm(Request $request, string $token): View
    {
        return view('admin.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function reset(ResetPasswordRequest $request): RedirectResponse
    {
        $status = $this->passwords->reset('admins', $request->validated());

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('admin.login')->with('status', 'Senha redefinida com sucesso. Você já pode entrar.');
        }

        return back()
            ->withErrors(['email' => 'Este link de redefinição é inválido, expirou ou já foi utilizado.'])
            ->onlyInput('email');
    }
}
