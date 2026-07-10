<?php

namespace App\Http\Controllers;

use App\Models\ProviderProfile;
use App\Models\TaxonomyTerm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProviderProfileController extends Controller
{
    public function index(Request $request): View
    {
        $profiles = ProviderProfile::with('user', 'externalImports', 'taxonomyTerms', 'attachments', 'ratings.user')
            ->withAvg(['ratings as average_stars' => fn ($query) => $query->whereNotNull('stars')], 'stars')
            ->withCount('ratings');

        if ($search = $request->string('q')->toString()) {
            $profiles->where(fn ($query) => $query
                ->where('business_name', 'like', "%{$search}%")
                ->orWhere('headline', 'like', "%{$search}%")
                ->orWhere('service_area', 'like', "%{$search}%")
                ->orWhere('skills', 'like', "%{$search}%")
            );
        }

        if ($serviceArea = $request->string('service_area')->toString()) {
            $profiles->where('service_area', 'like', "%{$serviceArea}%");
        }

        if ($skill = $request->string('skill')->toString()) {
            $profiles->where('skills', 'like', "%{$skill}%");
        }

        if ($level = $request->integer('technician_level')) {
            $profiles->where('max_technician_level', '>=', $level);
        }

        if ($tag = $request->integer('taxonomy_term_id')) {
            $profiles->whereHas('taxonomyTerms', fn ($query) => $query->whereKey($tag));
        }

        if ($insurance = $request->string('insurance')->toString()) {
            $profiles->where('insurance_status', 'like', "%{$insurance}%");
        }

        if ($request->boolean('public_contact')) {
            $profiles->where('public_contact', true);
        }

        match ($request->string('sort')->toString()) {
            'name' => $profiles->orderBy('business_name'),
            'rating' => $profiles->orderByDesc('average_stars')->orderByDesc('ratings_count'),
            default => $profiles->latest(),
        };

        return view('providers.index', [
            'profiles' => $profiles->paginate(20)->withQueryString(),
            'filters' => $request->only(['q', 'service_area', 'skill', 'technician_level', 'taxonomy_term_id', 'insurance', 'public_contact', 'sort']),
            'technicianLevels' => config('technician-levels'),
            'taxonomyTerms' => TaxonomyTerm::whereIn('type', ['work_category', 'work_specialty', 'skill', 'tool', 'certification'])
                ->where('is_active', true)
                ->orderBy('type')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function edit(Request $request): View
    {
        abort_unless($request->user()->hasRole('provider'), 403);

        return view('providers.edit', [
            'profile' => $request->user()->providerProfile?->load('attachments', 'externalImports.attachments', 'taxonomyTerms'),
            'technicianLevels' => config('technician-levels'),
            'taxonomyTerms' => TaxonomyTerm::whereIn('type', ['work_category', 'work_specialty', 'skill', 'tool', 'certification'])
                ->where('is_active', true)
                ->orderBy('type')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasRole('provider'), 403);

        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'headline' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'service_area' => ['nullable', 'string', 'max:255'],
            'max_technician_level' => ['required', 'integer', 'min:1', 'max:5'],
            'skills' => ['nullable', 'string'],
            'taxonomy_terms' => ['nullable', 'array'],
            'taxonomy_terms.*' => ['integer', 'exists:taxonomy_terms,id'],
            'services_text' => ['nullable', 'string'],
            'tools' => ['nullable', 'string'],
            'tool_inventory_text' => ['nullable', 'string'],
            'certifications' => ['nullable', 'string'],
            'certification_records_text' => ['nullable', 'string'],
            'insurance_status' => ['nullable', 'string', 'max:255'],
            'rate_card' => ['nullable', 'string'],
            'travel_policy' => ['nullable', 'string'],
            'availability_notes' => ['nullable', 'string'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'public_contact' => ['nullable', 'boolean'],
            'profile_visibility' => ['nullable', 'array'],
            'profile_visibility.*' => ['nullable', 'boolean'],
            'private_notes' => ['nullable', 'string'],
        ]);

        $data['public_contact'] = $request->boolean('public_contact');
        $data['services'] = $this->linesToRecords($data['services_text'] ?? '', ['name', 'level']);
        $data['tool_inventory'] = $this->linesToRecords($data['tool_inventory_text'] ?? '', ['name', 'category']);
        $data['certification_records'] = $this->linesToRecords($data['certification_records_text'] ?? '', ['name', 'issuer']);
        $data['profile_visibility'] = $this->normalizeVisibility($request, [
            'bio', 'services', 'tools', 'certifications', 'rate_card', 'availability', 'imports',
        ]);
        unset($data['services_text'], $data['tool_inventory_text'], $data['certification_records_text']);

        $profile = $request->user()->providerProfile()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $data
        );

        $sync = collect($data['taxonomy_terms'] ?? [])
            ->mapWithKeys(fn ($termId): array => [(int) $termId => ['evidence_source' => 'self_declared']])
            ->all();
        $profile->taxonomyTerms()->sync($sync);

        return redirect()->route('providers.edit')->with('status', 'Provider profile saved.');
    }

    public function show(ProviderProfile $provider): View
    {
        return view('providers.show', [
            'profile' => $provider->load(
                'user',
                'externalImports.attachments',
                'taxonomyTerms',
                'attachments',
                'ratings.user',
                'tagVerifications.workOrder.jobPost',
                'tagVerifications.buyer',
            ),
        ]);
    }

    private function linesToRecords(string $text, array $keys): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $text))
            ->map(fn (string $line): array => array_pad(array_map('trim', explode('|', $line, count($keys))), count($keys), null))
            ->filter(fn (array $parts): bool => (bool) ($parts[0] ?? null))
            ->map(fn (array $parts): array => collect($keys)->mapWithKeys(fn (string $key, int $index): array => [$key => $parts[$index] ?? null])->all())
            ->values()
            ->all();
    }

    private function normalizeVisibility(Request $request, array $fields): array
    {
        return collect($fields)
            ->mapWithKeys(fn (string $field): array => [$field => $request->boolean("profile_visibility.$field")])
            ->all();
    }
}
