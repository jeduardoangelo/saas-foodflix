<?php

namespace App\Http\Controllers\Site\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\LoginRequest;
use App\Services\Auth\CustomerAuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginController extends Controller
{
    private $authentication;

    public function __construct(CustomerAuthenticationService $authentication)
    {
        $this->authentication = $authentication;
    }

    public function create(): View
    {
        return view('site.auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        if (! $this->authentication->attempt($credentials['email'], $credentials['password'])) {
            return back()
                ->withErrors(['email' => 'As credenciais informadas são inválidas.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('site.home'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->authentication->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('site.home');
    }
}
