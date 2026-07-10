<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['parent_id', 'type', 'name', 'slug', 'description', 'sort_order', 'is_active'])]
class TaxonomyTerm extends Model
{
    public const TYPES = [
        'work_category',
        'work_specialty',
        'skill',
        'tool',
        'certification',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function providerProfiles(): BelongsToMany
    {
        return $this->belongsToMany(ProviderProfile::class)
            ->withPivot('evidence_source')
            ->withTimestamps();
    }
}
