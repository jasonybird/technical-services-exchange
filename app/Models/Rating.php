<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'category', 'stars', 'thumbs_up', 'body'])]
class Rating extends Model
{
    protected function casts(): array
    {
        return [
            'stars' => 'integer',
            'thumbs_up' => 'boolean',
        ];
    }

    public function rateable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
