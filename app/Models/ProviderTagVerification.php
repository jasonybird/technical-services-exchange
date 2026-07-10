<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'work_order_id', 'provider_profile_id', 'buyer_id', 'provider_id',
    'declared_level', 'confirmed_level', 'level_verdict',
    'confirmed_term_ids', 'disputed_term_ids', 'suggested_tags', 'notes',
])]
class ProviderTagVerification extends Model
{
    public const LEVEL_VERDICTS = [
        'confirmed' => 'Level matched the work',
        'overstated' => 'Provider level seemed overstated',
        'understated' => 'Provider could handle more advanced work',
        'not_observed' => 'Not enough evidence from this job',
    ];

    protected function casts(): array
    {
        return [
            'declared_level' => 'integer',
            'confirmed_level' => 'integer',
            'confirmed_term_ids' => 'array',
            'disputed_term_ids' => 'array',
            'suggested_tags' => 'array',
        ];
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function confirmedTerms()
    {
        return TaxonomyTerm::whereIn('id', $this->confirmed_term_ids ?? [])->get();
    }

    public function disputedTerms()
    {
        return TaxonomyTerm::whereIn('id', $this->disputed_term_ids ?? [])->get();
    }

    public function confirmedLevelDefinition(): ?array
    {
        if (! $this->confirmed_level) {
            return null;
        }

        return config('technician-levels')[$this->confirmed_level] ?? null;
    }
}
