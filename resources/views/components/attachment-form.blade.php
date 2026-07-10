@props(['type', 'id', 'kind' => 'general'])

<form method="POST" action="{{ route('attachments.store') }}" enctype="multipart/form-data" class="mt-4 space-y-3 rounded border p-4">
    @csrf
    <input type="hidden" name="attachable_type" value="{{ $type }}">
    <input type="hidden" name="attachable_id" value="{{ $id }}">
    <input type="hidden" name="kind" value="{{ $kind }}">
    <x-field name="caption" label="Caption" />
    <input type="file" name="file" required class="block w-full text-sm">
    <x-primary-button>Upload file</x-primary-button>
</form>
