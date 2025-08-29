<?php

namespace App\Http\Requests\Article;

use App\Enums\NewsCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ByCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'string', new Enum(NewsCategory::class)],
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => 'Category is required.',
            'category.enum' => 'Invalid category provided.',
            'per_page.integer' => 'Per page must be a number.',
            'per_page.min' => 'Per page must be at least 1.',
            'per_page.max' => 'Per page cannot exceed 100.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'category' => $this->route('category'),
        ]);
    }
}
