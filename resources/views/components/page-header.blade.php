@props(['eyebrow' => null, 'title', 'description' => null])

<div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
    <div>
        @if ($eyebrow)
            <p class="text-sm font-semibold uppercase tracking-wide text-sky-700 dark:text-sky-300">{{ $eyebrow }}</p>
        @endif
        <h1 class="text-2xl font-semibold tracking-tight text-slate-950 dark:text-white">{{ $title }}</h1>
        @if ($description)
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $description }}</p>
        @endif
    </div>

    @if (trim($slot) !== '')
        <div class="flex flex-wrap items-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
