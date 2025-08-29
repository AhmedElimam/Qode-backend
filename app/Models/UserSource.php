<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\NewsSource;

class UserSource extends Model
{
    protected $fillable = [
        'user_id',
        'source',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'source' => NewsSource::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
