<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'ingredients' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'active' => ['required', 'boolean'],
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
            'name.required' => 'Informe o nome do produto.',
            'category_id.required' => 'Selecione uma categoria.',
            'category_id.exists' => 'A categoria selecionada não existe.',
            'price.required' => 'Informe o preço do produto.',
            'price.numeric' => 'O preço deve ser um valor numérico.',
            'price.gt' => 'O preço deve ser maior que zero.',
            'image.image' => 'O arquivo deve ser uma imagem válida.',
            'image.mimes' => 'A imagem deve estar em JPG, PNG ou WEBP.',
            'image.max' => 'A imagem deve ter no máximo 2 MB.',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->has('active')) {
            $this->merge(['active' => $this->boolean('active')]);
        }
    }
}
