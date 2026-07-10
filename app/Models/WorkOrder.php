<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'job_post_id', 'buyer_id', 'provider_id', 'accepted_quote_id', 'status',
    'status_history', 'agreed_terms', 'deliverables_checklist', 'completion_notes',
    'en_route_at', 'on_site_at', 'started_at', 'completed_at', 'approved_at', 'closed_at',
])]
class WorkOrder extends Model
{
    public const STATUSES = [
        'assigned', 'en_route', 'on_site', 'in_progress', 'completed',
        'buyer_approved', 'disputed', 'closed', 'cancelled',
    ];

    public const ALLOWED_TRANSITIONS = [
        'assigned' => ['en_route', 'cancelled', 'disputed'],
        'en_route' => ['on_site', 'cancelled', 'disputed'],
        'on_site' => ['in_progress', 'completed', 'disputed'],
        'in_progress' => ['completed', 'disputed'],
        'completed' => ['buyer_approved', 'disputed'],
        'buyer_approved' => ['closed', 'disputed'],
        'disputed' => ['closed'],
        'closed' => [],
        'cancelled' => [],
    ];

    protected function casts(): array
    {
        return [
            'status_history' => 'array',
            'en_route_at' => 'datetime',
            'on_site_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'approved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function acceptedQuote(): BelongsTo
    {
        return $this->belongsTo(Quote::class, 'accepted_quote_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WorkOrderMessage::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function canTransitionTo(string $status): bool
    {
        return in_array($status, self::ALLOWED_TRANSITIONS[$this->status] ?? [], true);
    }
}
