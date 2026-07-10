<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'provider_profile_id', 'platform', 'external_id', 'profile_url', 'status',
    'visibility', 'verification_status', 'verified_by_id', 'verified_at',
    'rating', 'review_count', 'completed_jobs', 'work_categories', 'endorsements',
    'operational_metrics', 'metrics', 'review_snapshots', 'selected_reviews',
    'notes', 'imported_at',
])]
class ExternalProfileImport extends Model
{
    public const VISIBILITIES = [
        'private' => 'Private only',
        'summary' => 'Public summary only',
        'selected_reviews' => 'Public selected reviews',
        'proof_public' => 'Public proof attachments',
    ];

    public const VERIFICATION_STATUSES = [
        'unverified' => 'Unverified imported history',
        'provider_attested' => 'Provider attested',
        'admin_verified' => 'Admin verified',
        'needs_more_proof' => 'Needs more proof',
    ];

    protected function casts(): array
    {
        return [
            'work_categories' => 'array',
            'endorsements' => 'array',
            'operational_metrics' => 'array',
            'metrics' => 'array',
            'review_snapshots' => 'array',
            'selected_reviews' => 'array',
            'verified_at' => 'datetime',
            'imported_at' => 'datetime',
        ];
    }

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function publiclyVisible(): bool
    {
        return $this->visibility !== 'private';
    }

    public function canShowSelectedReviews(): bool
    {
        return in_array($this->visibility, ['selected_reviews', 'proof_public'], true);
    }

    public function canShowProofAttachments(): bool
    {
        return $this->visibility === 'proof_public';
    }
}
