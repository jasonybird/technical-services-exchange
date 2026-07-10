<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Provider Profile</h2></x-slot>
    <div class="mx-auto max-w-5xl space-y-6 p-6">
        <form method="POST" action="{{ route('providers.update') }}" class="space-y-4 rounded border bg-white p-6">
            @csrf @method('PUT')
            <x-field name="business_name" label="Business name" :value="$profile?->business_name" />
            <x-field name="headline" label="Headline" :value="$profile?->headline" />
            <x-field name="bio" label="Bio" :value="$profile?->bio" textarea />
            <x-field name="service_area" label="Service area" :value="$profile?->service_area" />
            <x-field name="skills" label="Skills" :value="$profile?->skills" textarea />
            <x-field name="tools" label="Tools and equipment" :value="$profile?->tools" textarea />
            <x-field name="certifications" label="Certifications" :value="$profile?->certifications" textarea />
            <x-field name="insurance_status" label="Insurance status" :value="$profile?->insurance_status" />
            <x-field name="rate_card" label="Rate card" :value="$profile?->rate_card" textarea />
            <x-field name="travel_policy" label="Travel policy" :value="$profile?->travel_policy" textarea />
            <x-field name="availability_notes" label="Availability notes" :value="$profile?->availability_notes" textarea />
            <x-field name="website_url" label="Website URL" :value="$profile?->website_url" />
            <x-field name="phone" label="Phone" :value="$profile?->phone" />
            <label class="flex gap-2 text-sm"><input type="checkbox" name="public_contact" value="1" @checked($profile?->public_contact)> Show contact details publicly</label>
            <x-primary-button>Save provider profile</x-primary-button>
        </form>
        @if ($profile)
            <section class="rounded border bg-white p-6">
                <h3 class="font-semibold">Profile photos and files</h3>
                <x-attachments :attachments="$profile->attachments" />
                <x-attachment-form type="provider_profile" :id="$profile->id" kind="profile" />
            </section>
            <form method="POST" action="{{ route('provider-imports.store') }}" class="space-y-4 rounded border bg-white p-6">
                @csrf
                <h3 class="font-semibold">External profile snapshot</h3>
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
