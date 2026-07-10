<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Community Feed</h2></x-slot>
    <div class="mx-auto max-w-4xl space-y-6 p-6">
        @auth
            <form method="POST" action="{{ route('feed.store') }}" class="space-y-4 rounded border bg-white p-6">
                @csrf
                <x-field name="title" label="Title" />
                <x-field name="body" label="Post" textarea />
                <input type="hidden" name="visibility" value="public">
                <x-primary-button>Publish</x-primary-button>
            </form>
        @endauth
        @foreach ($posts as $post)
            <article class="rounded border bg-white p-6">
                <p class="text-sm text-gray-500">{{ $post->user->name }} | {{ $post->created_at->diffForHumans() }}</p>
                @if ($post->title)<h3 class="mt-2 font-semibold">{{ $post->title }}</h3>@endif
                <p class="mt-3 whitespace-pre-line">{{ $post->body }}</p>
            </article>
        @endforeach
        {{ $posts->links() }}
    </div>
</x-app-layout>
