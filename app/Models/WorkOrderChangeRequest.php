<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'work_order_id',
    'requester_id',
    'responder_id',
    'reason_code',
    'summary',
    'details',
    'scope_impact',
    'schedule_impact',
    'terms_impact',
    'status',
    'resolution_notes',
    'responded_at',
])]
class WorkOrderChangeRequest extends Model
{
    public const REASON_CODES = [
        'scope_expansion' => 'Scope expansion',
        'missing_scope' => 'Missing or ambiguous scope',
        'schedule_change' => 'Schedule change',
        'site_condition' => 'Unexpected site condition',
        'buyer_request' => 'Buyer-requested change',
        'provider_request' => 'Provider-requested change',
        'other' => 'Other',
    ];

    public const STATUSES = ['open', 'accepted', 'declined', 'withdrawn'];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responder_id');
    }
}
