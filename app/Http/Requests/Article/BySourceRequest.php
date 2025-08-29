<?php

namespace App\Http\Requests\Article;
use App\Enums\NewsSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class BySourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source' => ['required', 'string', 'in:the_guardian,new_york_times,mediastack'],
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'source.required' => 'Source is required.',
            'source.enum' => 'Invalid source provided.',
            'per_page.integer' => 'Per page must be a number.',
            'per_page.min' => 'Per page must be at least 1.',
            'per_page.max' => 'Per page cannot exceed 100.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'source' => $this->route('source'),
        ]);
    }
}
