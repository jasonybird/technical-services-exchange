<?php

namespace App\Http\Controllers;

use App\Models\ProviderTagVerification;
use App\Models\TaxonomyTerm;
use App\Models\WorkOrder;
use App\Notifications\ExchangeEventNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProviderTagVerificationController extends Controller
{
    public function store(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        abort_unless($request->user()->id === $workOrder->buyer_id || $request->user()->hasRole('admin'), 403);
        abort_unless(in_array($workOrder->status, ['completed', 'buyer_approved', 'closed'], true), 422, 'Provider tag verification is available after completed work.');

        $profile = $workOrder->provider?->providerProfile;
        abort_unless($profile, 422, 'The assigned provider does not have a provider profile yet.');

        $data = $request->validate([
            'level_verdict' => ['required', 'string', 'in:'.implode(',', array_keys(ProviderTagVerification::LEVEL_VERDICTS))],
            'confirmed_level' => ['nullable', 'integer', 'min:1', 'max:5'],
            'confirmed_term_ids' => ['nullable', 'array'],
            'confirmed_term_ids.*' => ['integer', 'exists:taxonomy_terms,id'],
            'disputed_term_ids' => ['nullable', 'array'],
            'disputed_term_ids.*' => ['integer', 'exists:taxonomy_terms,id'],
            'suggested_tags_text' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:4000'],
        ]);

        $confirmed = $this->knownProfileTermIds($profile->id, $data['confirmed_term_ids'] ?? []);
        $disputed = $this->knownProfileTermIds($profile->id, $data['disputed_term_ids'] ?? []);
        $suggestedTags = collect(preg_split('/\r\n|\r|\n/', (string) ($data['suggested_tags_text'] ?? '')))
            ->map(fn (string $tag): string => trim($tag))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $verification = $workOrder->providerTagVerification()->updateOrCreate(
            ['work_order_id' => $workOrder->id],
            [
                'provider_profile_id' => $profile->id,
                'buyer_id' => $workOrder->buyer_id,
                'provider_id' => $workOrder->provider_id,
                'declared_level' => $profile->max_technician_level,
                'confirmed_level' => $data['confirmed_level'] ?? null,
                'level_verdict' => $data['level_verdict'],
                'confirmed_term_ids' => $confirmed,
                'disputed_term_ids' => $disputed,
                'suggested_tags' => $suggestedTags,
                'notes' => $data['notes'] ?? null,
            ]
        );

        if ($confirmed !== []) {
            $profile->taxonomyTerms()->syncWithoutDetaching(
                collect($confirmed)->mapWithKeys(fn (int $termId): array => [$termId => ['evidence_source' => 'buyer_endorsed']])->all()
            );
        }

        $workOrder->provider->notify(new ExchangeEventNotification(
            'Provider tags reviewed',
            $request->user()->name.' added post-work competency evidence for '.$workOrder->jobPost->title.'.',
            route('work-orders.show', $workOrder),
            'provider_tag_verification'
        ));

        return redirect()->route('work-orders.show', $workOrder)->with('status', 'Provider tag verification saved.');
    }

    private function knownProfileTermIds(int $profileId, array $termIds): array
    {
        if ($termIds === []) {
            return [];
        }

        return TaxonomyTerm::whereIn('id', $termIds)
            ->whereHas('providerProfiles', fn ($query) => $query->whereKey($profileId))
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }
}
