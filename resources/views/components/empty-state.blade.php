@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'tse-panel p-8 text-center']) }}>
    <h3 class="text-base font-semibold text-slate-950 dark:text-white">{{ $title }}</h3>
    @if ($description)
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $description }}</p>
    @endif
    @if (trim($slot) !== '')
        <div class="mt-4 flex flex-wrap justify-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
