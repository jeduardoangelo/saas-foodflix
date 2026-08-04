<?php

namespace App\Http\Requests\Site;

use App\Rules\Cpf;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterCustomerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $cpf = preg_replace('/\D/', '', (string) $this->input('cpf'));

        if (strlen($cpf) === 11) {
            $cpf = substr($cpf, 0, 3).'.'.substr($cpf, 3, 3).'.'.substr($cpf, 6, 3).'-'.substr($cpf, 9, 2);
        }

        $this->merge([
            'name' => trim(preg_replace('/\s+/', ' ', (string) $this->input('name'))),
            'cpf' => $cpf,
            'email' => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255', 'regex:/^\S+(?:\s+\S+)+$/u'],
            'cpf' => ['required', 'string', new Cpf(), Rule::unique('users', 'cpf')],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'street' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:20'],
            'neighborhood' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'complement' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Informe seu nome completo.',
            'name.regex' => 'Informe nome e sobrenome.',
            'cpf.required' => 'Informe o CPF.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'email.required' => 'Informe o email.',
            'email.email' => 'Informe um email válido.',
            'email.unique' => 'Este email já está cadastrado.',
            'street.required' => 'Informe a rua ou avenida.',
            'number.required' => 'Informe o número.',
            'neighborhood.required' => 'Informe o bairro.',
            'city.required' => 'Informe a cidade.',
            'password.required' => 'Informe uma senha.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação da senha não corresponde.',
        ];
    }
}
