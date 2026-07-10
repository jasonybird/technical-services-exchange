@props(['name', 'label', 'value' => '', 'textarea' => false, 'type' => 'text', 'help' => null])

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-slate-800 dark:text-slate-200">{{ $label }}</label>
    @if ($textarea)
        <textarea id="{{ $name }}" name="{{ $name }}" rows="4" class="mt-1 block w-full rounded-md border-slate-300 bg-white text-slate-950 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">{{ old($name, $value) }}</textarea>
    @else
        <input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $value) }}" class="mt-1 block w-full rounded-md border-slate-300 bg-white text-slate-950 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
    @endif
    @if ($help)
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $help }}</p>
    @endif
    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
