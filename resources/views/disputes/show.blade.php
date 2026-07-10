<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Peer Review: {{ $dispute->summary }}</h2></x-slot>
    <div class="mx-auto max-w-5xl space-y-6 p-6">
        <section class="rounded border bg-white p-6">
            <p class="text-sm text-gray-500">Opened by {{ $dispute->openedBy->name }} | {{ $dispute->status }}</p>
            <h3 class="mt-4 font-semibold">Claim</h3>
            <p class="mt-2 whitespace-pre-line">{{ $dispute->claim }}</p>
            <h3 class="mt-4 font-semibold">Evidence notes</h3>
            <p class="mt-2 whitespace-pre-line">{{ $dispute->evidence_notes }}</p>
            <x-attachments :attachments="$dispute->attachments" />
            <x-attachment-form type="dispute" :id="$dispute->id" kind="evidence" />
            <h3 class="mt-4 font-semibold">Recommended resolution</h3>
            <p class="mt-2 whitespace-pre-line">{{ $dispute->recommended_resolution ?? 'Pending peer review.' }}</p>
        </section>
        <section class="rounded border bg-white p-6">
            <h3 class="font-semibold">Peer votes</h3>
            <form method="POST" action="{{ route('dispute-votes.store', $dispute) }}" class="mt-4 space-y-4">
                @csrf
                <label class="block text-sm font-medium">Recommendation</label>
                <select name="recommendation" class="rounded-md border-gray-300">
                    <option value="provider">Provider position</option>
                    <option value="buyer">Buyer position</option>
                    <option value="split">Split resolution</option>
                    <option value="insufficient_evidence">Insufficient evidence</option>
                </select>
                <x-field name="reason" label="Reason" textarea />
                <x-primary-button>Save vote</x-primary-button>
            </form>
            @foreach ($dispute->votes as $vote)
                <div class="mt-3 rounded bg-gray-50 p-3 text-sm">
                    <p class="font-semibold">{{ $vote->user->name }} | {{ str_replace('_', ' ', $vote->recommendation) }}</p>
                    <p class="whitespace-pre-line">{{ $vote->reason }}</p>
                </div>
            @endforeach
        </section>
        <section class="rounded border bg-white p-6">
            <h3 class="font-semibold">Comments</h3>
            <form method="POST" action="{{ route('comments.store') }}" class="mt-4 space-y-4">
                @csrf
                <input type="hidden" name="commentable_type" value="dispute">
                <input type="hidden" name="commentable_id" value="{{ $dispute->id }}">
                <x-field name="body" label="Comment" textarea />
                <x-primary-button>Comment</x-primary-button>
            </form>
            @foreach ($dispute->comments as $comment)
                <div class="mt-3 rounded bg-gray-50 p-3 text-sm">
                    <p class="font-semibold">{{ $comment->user->name }}</p>
                    <p class="whitespace-pre-line">{{ $comment->body }}</p>
                </div>
            @endforeach
        </section>
    </div>
</x-app-layout>
