@php
    $policiesText = collect($profile?->hiring_policies ?? [])->map(fn ($row) => trim(($row['name'] ?? '').' | '.($row['summary'] ?? ''), ' |'))->implode("\n");
    $locationsText = collect($profile?->locations ?? [])->map(fn ($row) => trim(($row['name'] ?? '').' | '.($row['region'] ?? ''), ' |'))->implode("\n");
    $visibility = $profile?->profile_visibility ?? [];
    $visible = fn (string $field): bool => (bool) ($visibility[$field] ?? true);
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Buyer Profile"
            description="Build a public company profile with hiring regions, onboarding policies, locations, and sample identity files."
        />
    </x-slot>

    <div class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('buyers.update') }}" class="tse-panel space-y-4 p-6">
            @csrf
            @method('PUT')
            <x-field name="company_name" label="Company name" :value="$profile?->company_name" />
            <x-field name="headline" label="Headline" :value="$profile?->headline" />
            <x-field name="description" label="Description" :value="$profile?->description" textarea />
            <x-field name="service_categories" label="Service categories" :value="$profile?->service_categories" textarea />
            <x-field name="hiring_regions" label="Hiring regions" :value="$profile?->hiring_regions" textarea />
            <x-field name="hiring_policies_text" label="Structured hiring policies" :value="$policiesText" textarea help="One per line. Use: policy name | summary, for example Net 15 ACH | Direct payment after approval." />
            <x-field name="locations_text" label="Locations" :value="$locationsText" textarea help="One per line. Use: location name | region, for example Tulsa dispatch | Oklahoma." />
            <x-field name="vendor_onboarding" label="Vendor onboarding" :value="$profile?->vendor_onboarding" textarea />
            <x-field name="payment_terms" label="Payment terms" :value="$profile?->payment_terms" textarea />
            <x-field name="website_url" label="Website URL" :value="$profile?->website_url" />
            <x-field name="contact_email" label="Contact email" :value="$profile?->contact_email" />

            <div class="rounded-md border border-slate-200 p-4 dark:border-slate-800">
                <h3 class="font-semibold text-slate-950 dark:text-white">Public sections</h3>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Unchecked sections remain private to the buyer profile owner.</p>
                <div class="mt-3 grid gap-3 md:grid-cols-3">
                    @foreach (['description' => 'Description', 'categories' => 'Categories', 'locations' => 'Locations', 'policies' => 'Hiring policies', 'payment_terms' => 'Payment terms', 'onboarding' => 'Onboarding'] as $key => $label)
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
            <x-field name="private_notes" label="Private notes" :value="$profile?->private_notes" textarea help="Internal notes for your company account. These are not shown publicly." />
            <x-primary-button>Save buyer profile</x-primary-button>
        </form>
        @if ($profile)
            <section class="tse-panel p-6">
                <h3 class="font-semibold text-slate-950 dark:text-white">Company identity, logos, location photos, and sample files</h3>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Use captions to distinguish logo, header, office, site, or example work-order files.</p>
                <x-attachments :attachments="$profile->attachments" />
                <x-attachment-form type="buyer_profile" :id="$profile->id" kind="profile" />
            </section>
        @endif
    </div>
</x-app-layout>
