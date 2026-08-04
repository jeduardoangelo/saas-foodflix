<?php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;

class CatalogSearchRequest extends FormRequest
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
            'q' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function prepareForValidation()
    {
        if (is_string($this->input('q'))) {
            $this->merge(['q' => trim($this->input('q'))]);
        }
    }
}
