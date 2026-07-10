<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Buyer Profile</h2></x-slot>
    <div class="mx-auto max-w-5xl p-6">
        <form method="POST" action="{{ route('buyers.update') }}" class="space-y-4 rounded border bg-white p-6">
            @csrf @method('PUT')
            <x-field name="company_name" label="Company name" :value="$profile?->company_name" />
            <x-field name="headline" label="Headline" :value="$profile?->headline" />
            <x-field name="description" label="Description" :value="$profile?->description" textarea />
            <x-field name="service_categories" label="Service categories" :value="$profile?->service_categories" textarea />
            <x-field name="hiring_regions" label="Hiring regions" :value="$profile?->hiring_regions" textarea />
            <x-field name="vendor_onboarding" label="Vendor onboarding" :value="$profile?->vendor_onboarding" textarea />
            <x-field name="payment_terms" label="Payment terms" :value="$profile?->payment_terms" textarea />
            <x-field name="website_url" label="Website URL" :value="$profile?->website_url" />
            <x-field name="contact_email" label="Contact email" :value="$profile?->contact_email" />
            <label class="flex gap-2 text-sm"><input type="checkbox" name="public_contact" value="1" @checked($profile?->public_contact)> Show contact details publicly</label>
            <x-primary-button>Save buyer profile</x-primary-button>
        </form>
        @if ($profile)
            <section class="mt-6 rounded border bg-white p-6">
                <h3 class="font-semibold">Company photos and files</h3>
                <x-attachments :attachments="$profile->attachments" />
                <x-attachment-form type="buyer_profile" :id="$profile->id" kind="profile" />
            </section>
        @endif
    </div>
</x-app-layout>
