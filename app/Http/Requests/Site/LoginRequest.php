<?php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules()
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Informe o email.',
            'email.email' => 'Informe um email válido.',
            'password.required' => 'Informe a senha.',
        ];
    }
}
