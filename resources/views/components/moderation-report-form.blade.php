@props(['type', 'id'])

@auth
    <details class="mt-4 rounded-md border border-slate-200 p-4 text-sm dark:border-slate-800">
        <summary class="cursor-pointer font-semibold text-slate-950 dark:text-white">Report for moderation</summary>
        <form method="POST" action="{{ route('moderation-reports.store') }}" class="mt-3 space-y-3">
            @csrf
            <input type="hidden" name="reportable_type" value="{{ $type }}">
            <input type="hidden" name="reportable_id" value="{{ $id }}">
            <div>
                <label class="block text-sm font-medium text-slate-800 dark:text-slate-200">Reason</label>
                <select name="reason_code" class="mt-1 block w-full rounded-md border-slate-300 bg-white text-slate-950 shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                    @foreach (\App\Models\ModerationReport::REASON_CODES as $code => $label)
                        <option value="{{ $code }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <x-field name="details" label="Details" textarea />
            <x-secondary-button type="submit">Submit report</x-secondary-button>
        </form>
    </details>
@endauth
