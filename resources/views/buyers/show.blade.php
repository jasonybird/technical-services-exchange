<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">{{ $profile->company_name }}</h2></x-slot>
    <div class="mx-auto max-w-5xl rounded border bg-white p-6">
        <p class="text-lg font-semibold">{{ $profile->headline }}</p>
        <p class="mt-4 whitespace-pre-line">{{ $profile->description }}</p>
        <dl class="mt-6 grid gap-4 md:grid-cols-2">
            <div><dt class="text-sm text-gray-500">Service categories</dt><dd class="whitespace-pre-line">{{ $profile->service_categories }}</dd></div>
            <div><dt class="text-sm text-gray-500">Hiring regions</dt><dd class="whitespace-pre-line">{{ $profile->hiring_regions }}</dd></div>
            <div><dt class="text-sm text-gray-500">Vendor onboarding</dt><dd class="whitespace-pre-line">{{ $profile->vendor_onboarding }}</dd></div>
            <div><dt class="text-sm text-gray-500">Payment terms</dt><dd class="whitespace-pre-line">{{ $profile->payment_terms }}</dd></div>
        </dl>
    </div>
</x-app-layout>
