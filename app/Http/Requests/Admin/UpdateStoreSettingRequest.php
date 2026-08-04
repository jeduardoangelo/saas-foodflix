<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'store_name' => ['required', 'string', 'max:255'],
            'cnpj' => ['required', 'string', 'max:18'],
            'physical_address' => ['required', 'string', 'max:500'],
            'opening_hours' => ['required', 'string'],
            'delivery_fee' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'estimated_delivery_minutes' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'store_name.required' => 'Informe o nome da loja.',
            'cnpj.required' => 'Informe o CNPJ da loja.',
            'cnpj.max' => 'O CNPJ deve ter no máximo 18 caracteres.',
            'physical_address.required' => 'Informe o endereço físico da loja.',
            'opening_hours.required' => 'Informe o horário de funcionamento.',
            'delivery_fee.required' => 'Informe a taxa de entrega.',
            'delivery_fee.numeric' => 'A taxa de entrega deve ser um valor numérico.',
            'delivery_fee.min' => 'A taxa de entrega não pode ser negativa.',
            'estimated_delivery_minutes.required' => 'Informe o tempo estimado de entrega.',
            'estimated_delivery_minutes.integer' => 'O tempo estimado deve ser informado em minutos inteiros.',
            'estimated_delivery_minutes.min' => 'O tempo estimado deve ser maior que zero.',
        ];
    }
}
