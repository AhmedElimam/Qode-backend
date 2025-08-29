<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\NewsSource;
use App\Enums\NewsCategory;

class Article extends Model
{
    protected $fillable = [
        'title',
        'description',
        'content',
        'url',
        'image_url',
        'author',
        'source',
        'category',
        'published_at',
        'external_id',
        'metadata',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'metadata' => 'array',
        'source' => NewsSource::class,
        'category' => NewsCategory::class,
    ];

    public function scopeBySource($query, $source)
    {
        return $query->where('source', $source);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('published_at', [$startDate, $endDate]);
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('title', 'like', "%{$keyword}%")
              ->orWhere('description', 'like', "%{$keyword}%")
              ->orWhere('content', 'like', "%{$keyword}%");
        });
    }
}
