@props(['type', 'id', 'kind' => 'general'])

@php
    $maxMb = round(((int) config('provider-exchange.attachments.max_kb')) / 1024, 1);
    $mimeTypes = implode(', ', config('provider-exchange.attachments.allowed_mime_types'));
@endphp

<form method="POST" action="{{ route('attachments.store') }}" enctype="multipart/form-data" class="mt-4 space-y-3 rounded-md border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
    @csrf
    <input type="hidden" name="attachable_type" value="{{ $type }}">
    <input type="hidden" name="attachable_id" value="{{ $id }}">
    <input type="hidden" name="kind" value="{{ $kind }}">
    <x-field name="caption" label="Caption" />
    <div>
        <label for="file-{{ $type }}-{{ $id }}-{{ $kind }}" class="block text-sm font-medium text-slate-800 dark:text-slate-200">File</label>
        <input id="file-{{ $type }}-{{ $id }}-{{ $kind }}" type="file" name="file" required class="mt-1 block w-full text-sm text-slate-700 file:me-4 file:rounded-md file:border-0 file:bg-sky-600 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-sky-700 dark:text-slate-300">
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Max {{ $maxMb }} MB. Allowed: {{ $mimeTypes }}.</p>
        <x-input-error :messages="$errors->get('file')" class="mt-2" />
    </div>
    <x-primary-button>Upload file</x-primary-button>
</form>
