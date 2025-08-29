<?php

namespace App\Http\Requests\Article;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
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
            'keyword' => 'nullable|string|max:255',
            'source' => 'nullable|string|in:the_guardian,new_york_times,mediastack',
            'category' => 'nullable|string|in:business,technology,sports,entertainment,health,science,politics,world,national,local,opinion,arts,food,travel,education',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'keyword.max' => 'Search keyword cannot exceed 255 characters',
            'source.in' => 'Invalid news source selected',
            'category.in' => 'Invalid news category selected',
            'start_date.date' => 'Start date must be a valid date',
            'end_date.date' => 'End date must be a valid date',
            'end_date.after_or_equal' => 'End date must be after or equal to start date',
            'per_page.integer' => 'Per page must be a number',
            'per_page.min' => 'Per page must be at least 1',
            'per_page.max' => 'Per page cannot exceed 100',
        ];
    }
}
