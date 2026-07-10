@props(['ratings'])

@php
    $starRatings = $ratings->whereNotNull('stars');
    $thumbRatings = $ratings->whereNotNull('thumbs_up');
    $average = $starRatings->count() ? round($starRatings->avg('stars'), 1) : null;
    $up = $thumbRatings->where('thumbs_up', true)->count();
    $down = $thumbRatings->where('thumbs_up', false)->count();
@endphp

<div class="mt-4 rounded border bg-gray-50 p-4 text-sm">
    <p class="font-semibold">Community rating</p>
    <p class="mt-1 text-gray-700">
        Stars: {{ $average ? $average.'/5' : 'n/a' }} from {{ $starRatings->count() }} vote{{ $starRatings->count() === 1 ? '' : 's' }}
        | Thumbs: {{ $up }} up / {{ $down }} down
    </p>
    @foreach ($ratings->take(5) as $rating)
        @php
            $value = $rating->stars
                ? $rating->stars.'/5'
                : ($rating->thumbs_up === null ? 'no vote' : ($rating->thumbs_up ? 'thumbs up' : 'thumbs down'));
        @endphp
        <p class="mt-2 text-gray-600">{{ $rating->user->name ?? 'User' }} | {{ $rating->category }} | {{ $value }} {{ $rating->body ? '- '.$rating->body : '' }}</p>
    @endforeach
</div>
