<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'work_order_id', 'reviewer_id', 'reviewee_id', 'rating', 'communication_rating',
    'scope_accuracy_rating', 'payment_reliability_rating', 'workmanship_rating',
    'timeliness_rating', 'body', 'review_type',
])]
class Review extends Model
{
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
}
