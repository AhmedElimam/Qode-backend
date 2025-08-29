<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePreferenceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'language' => 'nullable|string|size:2',
            'country' => 'nullable|string|size:2',
            'articles_per_page' => 'nullable|integer|min:5|max:100',
            'notifications_enabled' => 'nullable|boolean',
            'sources' => 'nullable|array',
            'sources.*' => 'string|in:the_guardian,new_york_times,mediastack',
            'categories' => 'nullable|array',
            'categories.*' => 'string|in:business,technology,sports,entertainment,health,science,politics,world,national,local,opinion,arts,food,travel,education',
        ];
    }

    public function messages(): array
    {
        return [
            'language.size' => 'Language must be a 2-character code',
            'country.size' => 'Country must be a 2-character code',
            'articles_per_page.integer' => 'Articles per page must be a number',
            'articles_per_page.min' => 'Articles per page must be at least 5',
            'articles_per_page.max' => 'Articles per page cannot exceed 100',
            'notifications_enabled.boolean' => 'Notifications enabled must be true or false',
            'sources.array' => 'Sources must be an array',
            'sources.*.in' => 'Invalid news source selected',
            'categories.array' => 'Categories must be an array',
            'categories.*.in' => 'Invalid news category selected',
        ];
    }
}
