<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'work_order_id', 'opened_by_id', 'status', 'summary', 'reason_code', 'claim', 'response',
    'evidence_notes', 'recommended_resolution', 'peer_votes', 'resolved_at',
])]
class Dispute extends Model
{
    public const REASON_CODES = [
        'scope_expansion' => 'Scope expansion',
        'unreachable_contact' => 'Unreachable contact',
        'support_unavailable' => 'Support unavailable',
        'missing_deliverable' => 'Missing deliverable',
        'payment_issue' => 'Payment issue',
        'schedule_issue' => 'Schedule issue',
        'quality_issue' => 'Quality issue',
        'insufficient_evidence' => 'Insufficient evidence',
        'other' => 'Other',
    ];

    protected function casts(): array
    {
        return [
            'peer_votes' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(DisputeVote::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }
}
