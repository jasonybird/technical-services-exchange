<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id', 'company_name', 'headline', 'description', 'service_categories',
    'hiring_regions', 'hiring_policies', 'locations', 'vendor_onboarding',
    'payment_terms', 'website_url', 'contact_email', 'public_contact',
    'profile_visibility', 'private_notes',
])]
class BuyerProfile extends Model
{
    protected function casts(): array
    {
        return [
            'hiring_policies' => 'array',
            'locations' => 'array',
            'profile_visibility' => 'array',
            'public_contact' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function ratings(): MorphMany
    {
        return $this->morphMany(Rating::class, 'rateable');
    }

    public function visible(string $field): bool
    {
        $visibility = $this->profile_visibility ?? [];

        return (bool) ($visibility[$field] ?? true);
    }
}
