<?php

namespace App\Http\Controllers;

use App\Models\ProviderProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProviderProfileController extends Controller
{
    public function index(): View
    {
        return view('providers.index', [
            'profiles' => ProviderProfile::with('user', 'externalImports')->latest()->paginate(20),
        ]);
    }

    public function edit(Request $request): View
    {
        abort_unless($request->user()->hasRole('provider'), 403);

        return view('providers.edit', [
            'profile' => $request->user()->providerProfile,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasRole('provider'), 403);

        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'headline' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            'service_area' => ['nullable', 'string', 'max:255'],
            'skills' => ['nullable', 'string'],
            'tools' => ['nullable', 'string'],
            'certifications' => ['nullable', 'string'],
            'insurance_status' => ['nullable', 'string', 'max:255'],
            'rate_card' => ['nullable', 'string'],
            'travel_policy' => ['nullable', 'string'],
            'availability_notes' => ['nullable', 'string'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'public_contact' => ['nullable', 'boolean'],
        ]);

        $data['public_contact'] = $request->boolean('public_contact');

        $request->user()->providerProfile()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $data
        );

        return redirect()->route('providers.edit')->with('status', 'Provider profile saved.');
    }

    public function show(ProviderProfile $provider): View
    {
        return view('providers.show', [
            'profile' => $provider->load('user', 'externalImports'),
        ]);
    }
}
