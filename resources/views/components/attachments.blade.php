@props(['attachments'])

@if ($attachments->count())
    <div class="mt-4 grid gap-3 md:grid-cols-2">
        @foreach ($attachments as $attachment)
            <div class="rounded-md border border-slate-200 bg-white p-3 text-sm dark:border-slate-800 dark:bg-slate-900">
                @if ($attachment->isImage())
                    <a href="{{ $attachment->publicUrl() }}" target="_blank" class="block overflow-hidden rounded-md border border-slate-200 dark:border-slate-800">
                        <img src="{{ $attachment->publicUrl() }}" alt="{{ $attachment->caption ?: $attachment->original_name }}" class="h-36 w-full object-cover">
                    </a>
                @endif
                <div class="{{ $attachment->isImage() ? 'mt-3' : '' }} flex items-start justify-between gap-3">
                    <a href="{{ $attachment->publicUrl() }}" class="font-semibold text-sky-700 hover:text-sky-900 dark:text-sky-300 dark:hover:text-sky-200" target="_blank">
                        {{ $attachment->original_name }}
                    </a>
                    @auth
                        @if (auth()->id() === $attachment->user_id || auth()->user()->hasRole('admin'))
                            <form method="POST" action="{{ route('attachments.destroy', $attachment) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">Delete</button>
                            </form>
                        @endif
                    @endauth
                </div>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    {{ $attachment->kind }} | {{ $attachment->mime_type ?: 'unknown type' }} | {{ $attachment->formattedSize() }}
                </p>
                @if ($attachment->caption)
                    <p class="mt-2 text-slate-600 dark:text-slate-400">{{ $attachment->caption }}</p>
                @endif
                <x-moderation-report-form type="attachment" :id="$attachment->id" />
            </div>
        @endforeach
    </div>
@endif
