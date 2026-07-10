@php
    $servicesText = collect($profile?->services ?? [])->map(fn ($row) => trim(($row['name'] ?? '').' | '.($row['level'] ?? ''), ' |'))->implode("\n");
    $toolsText = collect($profile?->tool_inventory ?? [])->map(fn ($row) => trim(($row['name'] ?? '').' | '.($row['category'] ?? ''), ' |'))->implode("\n");
    $certsText = collect($profile?->certification_records ?? [])->map(fn ($row) => trim(($row['name'] ?? '').' | '.($row['issuer'] ?? ''), ' |'))->implode("\n");
    $visibility = $profile?->profile_visibility ?? [];
    $visible = fn (string $field): bool => (bool) ($visibility[$field] ?? true);
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Provider Profile"
            description="Build a public business profile with services, tools, proof records, galleries, and private owner notes."
        />
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('providers.update') }}" class="tse-panel space-y-4 p-6">
            @csrf
            @method('PUT')
            <x-field name="business_name" label="Business name" :value="$profile?->business_name" />
            <x-field name="headline" label="Headline" :value="$profile?->headline" />
            <x-field name="bio" label="Bio" :value="$profile?->bio" textarea />
            <x-field name="service_area" label="Service area" :value="$profile?->service_area" />
            <x-field name="skills" label="Skills summary" :value="$profile?->skills" textarea />
            <x-field name="services_text" label="Structured services" :value="$servicesText" textarea help="One per line. Use: service name | level, for example POS install | experienced." />
            <x-field name="tools" label="Tools and equipment summary" :value="$profile?->tools" textarea />
            <x-field name="tool_inventory_text" label="Tool inventory" :value="$toolsText" textarea help="One per line. Use: tool name | category, for example Cable tester | network." />
            <x-field name="certifications" label="Certifications summary" :value="$profile?->certifications" textarea />
            <x-field name="certification_records_text" label="Certification records" :value="$certsText" textarea help="One per line. Use: certification | issuer. Upload proof files below." />
            <x-field name="insurance_status" label="Insurance status" :value="$profile?->insurance_status" />
            <x-field name="rate_card" label="Rate card" :value="$profile?->rate_card" textarea />
            <x-field name="travel_policy" label="Travel policy" :value="$profile?->travel_policy" textarea />
            <x-field name="availability_notes" label="Availability notes" :value="$profile?->availability_notes" textarea />
            <x-field name="website_url" label="Website URL" :value="$profile?->website_url" />
            <x-field name="phone" label="Phone" :value="$profile?->phone" />

            <div class="rounded-md border border-slate-200 p-4 dark:border-slate-800">
                <h3 class="font-semibold text-slate-950 dark:text-white">Public sections</h3>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Unchecked sections remain available to you but do not display on the public provider profile.</p>
                <div class="mt-3 grid gap-3 md:grid-cols-3">
                    @foreach (['bio' => 'Bio', 'services' => 'Services', 'tools' => 'Tools', 'certifications' => 'Certifications', 'rate_card' => 'Rate card', 'availability' => 'Availability', 'imports' => 'Imported history'] as $key => $label)
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                            <input type="checkbox" name="profile_visibility[{{ $key }}]" value="1" @checked($visible($key)) class="rounded border-slate-300 text-sky-600 shadow-sm focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            <label class="flex gap-2 text-sm text-slate-700 dark:text-slate-300">
                <input type="checkbox" name="public_contact" value="1" @checked($profile?->public_contact) class="rounded border-slate-300 text-sky-600 shadow-sm focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950">
                Show contact details publicly
            </label>
            <x-field name="private_notes" label="Private notes" :value="$profile?->private_notes" textarea help="Internal notes for your account. These are not shown publicly." />
            <x-primary-button>Save provider profile</x-primary-button>
        </form>
        @if ($profile)
            <section class="tse-panel p-6">
                <h3 class="font-semibold text-slate-950 dark:text-white">Profile photos, certification proof, tools, and job files</h3>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Use captions to distinguish van photos, tool photos, certification proof, job photos, and insurance documents.</p>
                <x-attachments :attachments="$profile->attachments" />
                <x-attachment-form type="provider_profile" :id="$profile->id" kind="profile" />
            </section>
            <form method="POST" action="{{ route('provider-imports.store') }}" class="tse-panel space-y-4 p-6">
                @csrf
                <h3 class="font-semibold text-slate-950 dark:text-white">External profile snapshot</h3>
                <x-field name="platform" label="Platform" value="Field Nation" />
                <x-field name="external_id" label="External ID" value="172-630" />
                <x-field name="profile_url" label="Profile URL" />
                <x-field name="rating" label="Rating" type="number" />
                <x-field name="review_count" label="Review count" type="number" />
                <x-field name="completed_jobs" label="Completed jobs" type="number" />
                <x-field name="notes" label="Notes or copied review summary" textarea />
                <x-primary-button>Save external snapshot</x-primary-button>
            </form>
        @endif
    </div>
</x-app-layout>
