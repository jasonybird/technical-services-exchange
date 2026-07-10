<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold">Providers</h2></x-slot>
    <div class="mx-auto max-w-7xl space-y-4 p-6">
        <form method="GET" action="{{ route('providers.index') }}" class="flex gap-3 rounded border bg-white p-4">
            <input name="q" value="{{ request('q') }}" placeholder="Search providers..." class="w-full rounded-md border-gray-300">
            <x-primary-button>Search</x-primary-button>
        </form>
        @foreach ($profiles as $profile)
            <a href="{{ route('providers.show', $profile) }}" class="block rounded border bg-white p-4 shadow-sm">
                <h3 class="font-semibold">{{ $profile->business_name }}</h3>
                <p class="text-sm text-gray-600">{{ $profile->headline }}</p>
                <p class="mt-2 text-sm">{{ $profile->service_area }}</p>
                <x-rating-summary :ratings="$profile->ratings" />
                @foreach ($profile->externalImports as $import)
                    <span class="mt-2 inline-block rounded bg-gray-100 px-2 py-1 text-xs">{{ $import->platform }} {{ $import->rating ? $import->rating.'/5' : '' }}</span>
                @endforeach
            </a>
        @endforeach
        {{ $profiles->links() }}
    </div>
</x-app-layout>
