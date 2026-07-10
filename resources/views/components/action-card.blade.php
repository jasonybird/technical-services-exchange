@props(['title', 'description', 'href' => null, 'label' => 'Open'])

<div class="tse-panel flex h-full flex-col p-5">
    <h3 class="text-base font-semibold text-slate-950 dark:text-white">{{ $title }}</h3>
    <p class="mt-2 flex-1 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $description }}</p>
    @if ($href)
        <a href="{{ $href }}" class="mt-4 inline-flex items-center text-sm font-semibold text-sky-700 hover:text-sky-900 dark:text-sky-300 dark:hover:text-sky-200">
            {{ $label }}
            <span aria-hidden="true" class="ms-1">-&gt;</span>
        </a>
    @endif
</div>
