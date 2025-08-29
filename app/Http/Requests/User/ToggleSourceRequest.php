<?php

namespace App\Http\Requests\User;

use App\Enums\NewsSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ToggleSourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source' => ['required', 'string', 'in:the_guardian,new_york_times,mediastack'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'source' => $this->route('source'),
        ]);
    }

    public function messages(): array
    {
        return [
            'source.required' => 'Source is required.',
            'source.enum' => 'Invalid source provided.',
        ];
    }
}
