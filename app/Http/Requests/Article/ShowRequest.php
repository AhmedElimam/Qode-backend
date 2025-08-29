<?php

namespace App\Http\Requests\Article;

use Illuminate\Foundation\Http\FormRequest;

class ShowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'id.required' => 'Article ID is required.',
            'id.integer' => 'Article ID must be a number.',
            'id.min' => 'Article ID must be at least 1.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->route('id'),
        ]);
    }
}
