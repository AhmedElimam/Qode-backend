<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\NewsCategory;

class UserCategory extends Model
{
    protected $fillable = [
        'user_id',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'category' => NewsCategory::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
