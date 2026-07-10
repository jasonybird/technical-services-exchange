@props(['label', 'value', 'description' => null])

<div class="tse-panel p-5">
    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p>
    <p class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 dark:text-white">{{ $value }}</p>
    @if ($description)
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $description }}</p>
    @endif
</div>
