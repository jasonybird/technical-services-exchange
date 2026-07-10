@props(['attachments'])

@if ($attachments->count())
    <div class="mt-4 grid gap-3 md:grid-cols-2">
        @foreach ($attachments as $attachment)
            <a href="{{ asset('storage/'.$attachment->path) }}" class="block rounded border p-3 text-sm" target="_blank">
                <span class="font-semibold">{{ $attachment->original_name }}</span>
                <span class="block text-gray-500">{{ $attachment->caption }}</span>
            </a>
        @endforeach
    </div>
@endif
