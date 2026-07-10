<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Post Job</h2></x-slot>
    <div class="mx-auto max-w-5xl p-6">
        <form method="POST" action="{{ route('jobs.store') }}" class="space-y-4 rounded border bg-white p-6">
            @csrf
            <x-field name="title" label="Title" />
            <x-field name="service_category" label="Service category" />
            <x-field name="location" label="Location" />
            <x-field name="starts_at" label="Start date/time" type="datetime-local" />
            <x-field name="time_window" label="Time window" />
            <x-field name="scope" label="Scope" textarea />
            <x-field name="required_skills" label="Required skills" textarea />
            <x-field name="required_tools" label="Required tools" textarea />
            <x-field name="deliverables" label="Deliverables checklist" textarea />
            <x-field name="payment_terms" label="Payment terms" textarea />
            <x-field name="vendor_onboarding" label="Vendor onboarding" textarea />
            <input type="hidden" name="visibility" value="public">
            <x-primary-button>Post job</x-primary-button>
        </form>
    </div>
</x-app-layout>
