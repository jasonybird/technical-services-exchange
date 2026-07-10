@props(['ratings'])

@php
    $starRatings = $ratings->whereNotNull('stars');
    $thumbRatings = $ratings->whereNotNull('thumbs_up');
    $average = $starRatings->count() ? round($starRatings->avg('stars'), 1) : null;
    $up = $thumbRatings->where('thumbs_up', true)->count();
    $down = $thumbRatings->where('thumbs_up', false)->count();
@endphp

<div class="mt-4 rounded-md border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-800 dark:bg-slate-950">
    <p class="font-semibold text-slate-950 dark:text-white">Community rating</p>
    <p class="mt-1 text-slate-700 dark:text-slate-300">
        Stars: {{ $average ? $average.'/5' : 'n/a' }} from {{ $starRatings->count() }} vote{{ $starRatings->count() === 1 ? '' : 's' }}
        | Thumbs: {{ $up }} up / {{ $down }} down
    </p>
    @foreach ($ratings->take(5) as $rating)
        @php
            $value = $rating->stars
                ? $rating->stars.'/5'
                : ($rating->thumbs_up === null ? 'no vote' : ($rating->thumbs_up ? 'thumbs up' : 'thumbs down'));
        @endphp
        <p class="mt-2 text-slate-600 dark:text-slate-400">{{ $rating->user->name ?? 'User' }} | {{ $rating->category }} | {{ $value }} {{ $rating->body ? '- '.$rating->body : '' }}</p>
    @endforeach
</div>
