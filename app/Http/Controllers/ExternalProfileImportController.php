<?php

namespace App\Http\Controllers;

use App\Models\ExternalProfileImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ExternalProfileImportController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $profile = $request->user()->providerProfile;

        abort_unless($request->user()->hasRole('provider') && $profile, 403);

        $data = $request->validate([
            'platform' => ['required', 'string', 'max:255'],
            'external_id' => ['nullable', 'string', 'max:255'],
            'profile_url' => ['nullable', 'url', 'max:255'],
            'visibility' => ['nullable', 'string', 'in:'.implode(',', array_keys(ExternalProfileImport::VISIBILITIES))],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'review_count' => ['nullable', 'integer', 'min:0'],
            'completed_jobs' => ['nullable', 'integer', 'min:0'],
            'client_count' => ['nullable', 'integer', 'min:0'],
            'on_time_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'backout_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'work_categories_text' => ['nullable', 'string'],
            'endorsements' => ['nullable', 'array'],
            'endorsements.*' => ['nullable', 'string', 'max:255'],
            'selected_reviews_text' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $operationalMetrics = array_filter([
            'client_count' => $data['client_count'] ?? null,
            'on_time_rate' => $data['on_time_rate'] ?? null,
            'backout_rate' => $data['backout_rate'] ?? null,
        ], fn ($value): bool => $value !== null && $value !== '');

        $profile->externalImports()->create([
            'platform' => $data['platform'],
            'external_id' => $data['external_id'] ?? null,
            'profile_url' => $data['profile_url'] ?? null,
            'status' => 'manual',
            'visibility' => $data['visibility'] ?? 'private',
            'verification_status' => 'provider_attested',
            'rating' => $data['rating'] ?? null,
            'review_count' => $data['review_count'] ?? null,
            'completed_jobs' => $data['completed_jobs'] ?? null,
            'work_categories' => $this->linesToList($data['work_categories_text'] ?? ''),
            'endorsements' => array_values(array_filter($data['endorsements'] ?? [])),
            'operational_metrics' => $operationalMetrics,
            'metrics' => $operationalMetrics,
            'selected_reviews' => $this->linesToList($data['selected_reviews_text'] ?? ''),
            'review_snapshots' => $this->linesToList($data['selected_reviews_text'] ?? ''),
            'notes' => $data['notes'] ?? null,
            'imported_at' => now(),
        ]);

        return redirect()->route('providers.edit')->with('status', 'External profile snapshot saved.');
    }

    public function verify(Request $request, ExternalProfileImport $import): RedirectResponse
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        $data = $request->validate([
            'verification_status' => ['required', 'string', 'in:admin_verified,needs_more_proof,unverified'],
        ]);

        $import->update([
            'verification_status' => $data['verification_status'],
            'verified_by_id' => $request->user()->id,
            'verified_at' => now(),
        ]);

        return back()->with('status', 'Imported history verification updated.');
    }

    private function linesToList(string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $text))
            ->map(fn (string $line): string => trim($line, " \t\n\r\0\x0B-"))
            ->filter()
            ->values()
            ->all();
    }
}
