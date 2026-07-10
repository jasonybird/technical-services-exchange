<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'work_order_id', 'reviewer_id', 'reviewee_id', 'rating', 'communication_rating',
    'preparedness_rating', 'scope_accuracy_rating', 'payment_reliability_rating',
    'contact_availability_rating', 'schedule_reasonableness_rating',
    'support_responsiveness_rating', 'closeout_fairness_rating', 'workmanship_rating',
    'timeliness_rating', 'closeout_quality_rating', 'professionalism_rating', 'body',
    'review_type', 'response_body', 'response_at', 'reported_at', 'reported_by_id',
    'report_reason', 'moderation_status', 'moderated_by_id', 'moderated_at',
    'moderation_notes',
])]
class Review extends Model
{
    public const MODERATION_STATUSES = ['published', 'reported', 'hidden', 'dismissed'];

    protected function casts(): array
    {
        return [
            'response_at' => 'datetime',
            'reported_at' => 'datetime',
            'moderated_at' => 'datetime',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by_id');
    }

    public function moderatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by_id');
    }

    public function isVisible(): bool
    {
        return $this->moderation_status !== 'hidden';
    }

    public function editableBy(User $user): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        $hours = (int) config('reputation.review_edit_window_hours', 48);

        return $this->reviewer_id === $user->id && $this->created_at?->greaterThanOrEqualTo(now()->subHours($hours));
    }
}
