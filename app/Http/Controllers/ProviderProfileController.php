<?php

namespace App\Http\Controllers;

use App\Models\ProviderProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProviderProfileController extends Controller
{
    public function index(Request $request): View
    {
        $profiles = ProviderProfile::with('user', 'externalImports', 'attachments', 'ratings.user')
            ->withAvg(['ratings as average_stars' => fn ($query) => $query->whereNotNull('stars')], 'stars')
            ->withCount('ratings');

        if ($search = $request->string('q')->toString()) {
            $profiles->where(fn ($query) => $query
                ->where('business_name', 'like', "%{$search}%")
                ->orWhere('headline', 'like', "%{$search}%")
                ->orWhere('service_area', 'like', "%{$search}%")
                ->orWhere('skills', 'like', "%{$search}%")
            );
        }

        if ($serviceArea = $request->string('service_area')->toString()) {
            $profiles->where('service_area', 'like', "%{$serviceArea}%");
        }

        if ($skill = $request->string('skill')->toString()) {
            $profiles->where('skills', 'like', "%{$skill}%");
        }

        if ($insurance = $request->string('insurance')->toString()) {
            $profiles->where('insurance_status', 'like', "%{$insurance}%");
        }

        if ($request->boolean('public_contact')) {
            $profiles->where('public_contact', true);
        }

        match ($request->string('sort')->toString()) {
            'name' => $profiles->orderBy('business_name'),
            'rating' => $profiles->orderByDesc('average_stars')->orderByDesc('ratings_count'),
            default => $profiles->latest(),
        };

        return view('providers.index', [
            'profiles' => $profiles->paginate(20)->withQueryString(),
            'filters' => $request->only(['q', 'service_area', 'skill', 'insurance', 'public_contact', 'sort']),
        ]);
    }

    public function edit(Request $request): View
    {
        abort_unless($request->user()->hasRole('provider'), 403);

        return view('providers.edit', [
            'profile' => $request->user()->providerProfile?->load('attachments', 'externalImports.attachments'),
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
            'profile' => $provider->load('user', 'externalImports.attachments', 'attachments', 'ratings.user'),
        ]);
    }
}
