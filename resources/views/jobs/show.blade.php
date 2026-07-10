<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">{{ $job->title }}</h2></x-slot>
    <div class="mx-auto max-w-6xl space-y-6 p-6">
        <section class="rounded border bg-white p-6">
            <p class="text-sm text-gray-500">{{ $job->location }} | {{ $job->service_category }} | {{ $job->status }}</p>
            <p class="mt-4 whitespace-pre-line">{{ $job->scope }}</p>
            <dl class="mt-6 grid gap-4 md:grid-cols-2">
                <div><dt class="text-sm text-gray-500">Required skills</dt><dd class="whitespace-pre-line">{{ $job->required_skills }}</dd></div>
                <div><dt class="text-sm text-gray-500">Required tools</dt><dd class="whitespace-pre-line">{{ $job->required_tools }}</dd></div>
                <div><dt class="text-sm text-gray-500">Deliverables</dt><dd class="whitespace-pre-line">{{ $job->deliverables }}</dd></div>
                <div><dt class="text-sm text-gray-500">Payment terms</dt><dd class="whitespace-pre-line">{{ $job->payment_terms }}</dd></div>
            </dl>
        </section>
        @auth
            @role('provider')
                @if ($job->status === 'open' && $job->buyer_id !== auth()->id())
                    <form method="POST" action="{{ route('quotes.store', $job) }}" class="space-y-4 rounded border bg-white p-6">
                        @csrf
                        <h3 class="font-semibold">Submit quote</h3>
                        <x-field name="requested_amount" label="Requested amount" type="number" />
                        <x-field name="rate_summary" label="Rate summary" />
                        <x-field name="message" label="Message" textarea />
                        <x-field name="terms" label="Terms" textarea />
                        <x-primary-button>Submit quote</x-primary-button>
                    </form>
                @endif
            @endrole
        @endauth
        <section class="rounded border bg-white p-6">
            <h3 class="font-semibold">Quotes</h3>
            @forelse ($job->quotes as $quote)
                <div class="mt-4 rounded border p-4">
                    <p class="font-semibold">{{ $quote->provider->providerProfile?->business_name ?? $quote->provider->name }}</p>
                    <p class="text-sm text-gray-600">{{ $quote->status }} | {{ $quote->rate_summary }} | {{ $quote->requested_amount }}</p>
                    <p class="mt-2 whitespace-pre-line text-sm">{{ $quote->message }}</p>
                    @auth
                        @if (auth()->id() === $job->buyer_id && $job->status === 'open')
                            <form method="POST" action="{{ route('quotes.accept', $quote) }}" class="mt-3">
                                @csrf
                                <x-primary-button>Accept quote</x-primary-button>
                            </form>
                        @endif
                    @endauth
                </div>
            @empty
                <p class="mt-2 text-sm text-gray-600">No quotes yet.</p>
            @endforelse
        </section>
        @if ($job->workOrder)
            <a class="rounded bg-gray-900 px-4 py-2 text-white" href="{{ route('work-orders.show', $job->workOrder) }}">View work order</a>
        @endif
    </div>
</x-app-layout>
