@props(['type', 'id', 'category' => 'overall', 'mode' => 'stars'])

@auth
    <form method="POST" action="{{ route('ratings.store') }}" class="mt-4 space-y-3 rounded border p-4">
        @csrf
        <input type="hidden" name="rateable_type" value="{{ $type }}">
        <input type="hidden" name="rateable_id" value="{{ $id }}">
        <input type="hidden" name="category" value="{{ $category }}">
        @if ($mode === 'thumbs')
            <label class="block text-sm font-medium">Vote</label>
            <select name="thumbs_up" class="rounded-md border-gray-300">
                <option value="1">Thumbs up</option>
                <option value="0">Thumbs down</option>
            </select>
        @else
            <x-field name="stars" label="Stars 1-5" type="number" />
        @endif
        <x-field name="body" label="Reason" textarea />
        <x-primary-button>Save rating</x-primary-button>
    </form>
@endauth
