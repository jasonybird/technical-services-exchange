<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'title', 'body', 'visibility'])]
class SocialPost extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
