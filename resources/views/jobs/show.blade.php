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
            <x-attachments :attachments="$job->attachments" />
            <x-rating-summary :ratings="$job->ratings" />
            <x-rating-form type="job_post" :id="$job->id" category="job_quality" />
            @auth
                @if (auth()->id() === $job->buyer_id)
                    <x-attachment-form type="job_post" :id="$job->id" kind="job" />
                @endif
            @endauth
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
                    @if ($quote->revisions->count())
                        <details class="mt-3 text-sm">
                            <summary class="cursor-pointer font-semibold">Revision history</summary>
                            @foreach ($quote->revisions as $revision)
                                <p class="mt-2 text-gray-600">{{ $revision->created_at->diffForHumans() }} | {{ $revision->action }} | {{ $revision->rate_summary }} | {{ $revision->requested_amount }}</p>
                            @endforeach
                        </details>
                    @endif
                    @auth
                        @if (auth()->id() === $quote->provider_id && in_array($quote->status, ['submitted', 'countered', 'revised'], true) && $job->status === 'open')
                            <form method="POST" action="{{ route('quotes.update', $quote) }}" class="mt-4 space-y-3 rounded bg-gray-50 p-3">
                                @csrf @method('PATCH')
                                <x-field name="requested_amount" label="Revised amount" type="number" :value="$quote->requested_amount" />
                                <x-field name="rate_summary" label="Rate summary" :value="$quote->rate_summary" />
                                <x-field name="message" label="Message" :value="$quote->message" textarea />
                                <x-field name="terms" label="Terms" :value="$quote->terms" textarea />
                                <x-primary-button>Revise quote</x-primary-button>
                            </form>
                        @endif
                        @if (auth()->id() === $job->buyer_id && $job->status === 'open')
                            <form method="POST" action="{{ route('quotes.accept', $quote) }}" class="mt-3 inline-block">
                                @csrf
                                <x-primary-button>Accept quote</x-primary-button>
                            </form>
                            <form method="POST" action="{{ route('quotes.decline', $quote) }}" class="mt-3 inline-block">
                                @csrf
                                <x-secondary-button>Decline</x-secondary-button>
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
        <section class="rounded border bg-white p-6">
            <h3 class="font-semibold">Discussion</h3>
            @auth
                <form method="POST" action="{{ route('comments.store') }}" class="mt-4 space-y-3">
                    @csrf
                    <input type="hidden" name="commentable_type" value="job_post">
                    <input type="hidden" name="commentable_id" value="{{ $job->id }}">
                    <x-field name="body" label="Comment" textarea />
                    <x-primary-button>Comment</x-primary-button>
                </form>
            @endauth
            @foreach ($job->comments as $comment)
                <div class="mt-3 rounded bg-gray-50 p-3 text-sm">
                    <p class="font-semibold">{{ $comment->user->name }}</p>
                    <p class="whitespace-pre-line">{{ $comment->body }}</p>
                </div>
            @endforeach
        </section>
    </div>
</x-app-layout>
