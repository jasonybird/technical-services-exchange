<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'reporter_id',
    'reason_code',
    'details',
    'status',
    'moderated_by_id',
    'moderated_at',
    'moderation_notes',
])]
class ModerationReport extends Model
{
    public const REASON_CODES = [
        'spam' => 'Spam or solicitation',
        'misleading' => 'Misleading or false information',
        'private_info' => 'Private or sensitive information',
        'abuse' => 'Abuse or harassment',
        'unsafe' => 'Unsafe work or conduct',
        'other' => 'Other',
    ];

    public const STATUSES = ['open', 'reviewing', 'action_taken', 'dismissed'];

    protected function casts(): array
    {
        return [
            'moderated_at' => 'datetime',
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function moderatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by_id');
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }
}
