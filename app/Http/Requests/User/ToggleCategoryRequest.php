<?php

namespace App\Http\Requests\User;

use App\Enums\NewsCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ToggleCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'string', new Enum(NewsCategory::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => 'Category is required.',
            'category.enum' => 'Invalid category provided.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'category' => $this->route('category'),
        ]);
    }
}
