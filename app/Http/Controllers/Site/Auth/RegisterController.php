<?php

namespace App\Http\Controllers\Site\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\RegisterCustomerRequest;
use App\Services\Auth\CustomerAuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegisterController extends Controller
{
    private $authentication;

    public function __construct(CustomerAuthenticationService $authentication)
    {
        $this->authentication = $authentication;
    }

    public function create(): View
    {
        return view('site.auth.register');
    }

    public function store(RegisterCustomerRequest $request): RedirectResponse
    {
        $user = $this->authentication->register($request->validated());
        $this->authentication->login($user);
        $request->session()->regenerate();

        return redirect()->route('site.home');
    }
}
