<?php

namespace App\Http\Controllers;

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
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'review_count' => ['nullable', 'integer', 'min:0'],
            'completed_jobs' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $profile->externalImports()->create($data + [
            'status' => 'manual',
            'imported_at' => now(),
        ]);

        return redirect()->route('providers.edit')->with('status', 'External profile snapshot saved.');
    }
}
