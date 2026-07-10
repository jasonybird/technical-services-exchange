<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Peer Review: {{ $dispute->summary }}</h2></x-slot>
    <div class="mx-auto max-w-5xl space-y-6 p-6">
        <section class="rounded border bg-white p-6">
            <p class="text-sm text-gray-500">Opened by {{ $dispute->openedBy->name }} | {{ $dispute->status }}</p>
            <h3 class="mt-4 font-semibold">Claim</h3>
            <p class="mt-2 whitespace-pre-line">{{ $dispute->claim }}</p>
            <h3 class="mt-4 font-semibold">Evidence notes</h3>
            <p class="mt-2 whitespace-pre-line">{{ $dispute->evidence_notes }}</p>
            <h3 class="mt-4 font-semibold">Recommended resolution</h3>
            <p class="mt-2 whitespace-pre-line">{{ $dispute->recommended_resolution ?? 'Pending peer review.' }}</p>
        </section>
    </div>
</x-app-layout>
