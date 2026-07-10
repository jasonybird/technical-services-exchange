<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id', 'job_alerts', 'quote_updates', 'work_order_updates',
    'review_updates', 'dispute_updates', 'email_enabled', 'push_enabled',
])]
class NotificationPreference extends Model
{
    protected function casts(): array
    {
        return [
            'job_alerts' => 'boolean',
            'quote_updates' => 'boolean',
            'work_order_updates' => 'boolean',
            'review_updates' => 'boolean',
            'dispute_updates' => 'boolean',
            'email_enabled' => 'boolean',
            'push_enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
