<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">{{ $profile->business_name }}</h2></x-slot>
    <div class="mx-auto max-w-5xl space-y-6 p-6">
        <section class="rounded border bg-white p-6">
            <p class="text-lg font-semibold">{{ $profile->headline }}</p>
            <p class="mt-4 whitespace-pre-line">{{ $profile->bio }}</p>
            <dl class="mt-6 grid gap-4 md:grid-cols-2">
                <div><dt class="text-sm text-gray-500">Service area</dt><dd>{{ $profile->service_area }}</dd></div>
                <div><dt class="text-sm text-gray-500">Insurance</dt><dd>{{ $profile->insurance_status }}</dd></div>
                <div><dt class="text-sm text-gray-500">Skills</dt><dd class="whitespace-pre-line">{{ $profile->skills }}</dd></div>
                <div><dt class="text-sm text-gray-500">Tools</dt><dd class="whitespace-pre-line">{{ $profile->tools }}</dd></div>
                <div><dt class="text-sm text-gray-500">Rate card</dt><dd class="whitespace-pre-line">{{ $profile->rate_card }}</dd></div>
                <div><dt class="text-sm text-gray-500">Travel policy</dt><dd class="whitespace-pre-line">{{ $profile->travel_policy }}</dd></div>
            </dl>
            <x-attachments :attachments="$profile->attachments" />
            <x-rating-summary :ratings="$profile->ratings" />
            <x-rating-form type="provider_profile" :id="$profile->id" category="provider_overall" />
        </section>
        <section class="rounded border bg-white p-6">
            <h3 class="font-semibold">External profile history</h3>
            @forelse ($profile->externalImports as $import)
                <div class="mt-4 rounded border p-4">
                    <p class="font-semibold">{{ $import->platform }} {{ $import->external_id ? '#'.$import->external_id : '' }}</p>
                    <p class="text-sm text-gray-600">Rating: {{ $import->rating ?? 'n/a' }} | Reviews: {{ $import->review_count ?? 'n/a' }} | Completed: {{ $import->completed_jobs ?? 'n/a' }}</p>
                    <p class="mt-2 whitespace-pre-line text-sm">{{ $import->notes }}</p>
                </div>
            @empty
                <p class="mt-2 text-sm text-gray-600">No external profile snapshots yet.</p>
            @endforelse
        </section>
    </div>
</x-app-layout>
