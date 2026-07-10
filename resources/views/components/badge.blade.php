@props(['tone' => 'slate'])

@php
$tones = [
    'sky' => 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-200',
    'emerald' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200',
    'amber' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200',
    'rose' => 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-200',
    'slate' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold '.$tones[$tone]]) }}>
    {{ $slot }}
</span>
