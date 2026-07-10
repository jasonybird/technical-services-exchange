<?php

namespace App\Http\Controllers;

use App\Models\BuyerProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BuyerProfileController extends Controller
{
    public function index(Request $request): View
    {
        $profiles = BuyerProfile::with('user', 'attachments', 'ratings.user');

        if ($search = $request->string('q')->toString()) {
            $profiles->where(fn ($query) => $query
                ->where('company_name', 'like', "%{$search}%")
                ->orWhere('headline', 'like', "%{$search}%")
                ->orWhere('service_categories', 'like', "%{$search}%")
                ->orWhere('hiring_regions', 'like', "%{$search}%")
            );
        }

        return view('buyers.index', [
            'profiles' => $profiles->latest()->paginate(20)->withQueryString(),
        ]);
    }

    public function edit(Request $request): View
    {
        abort_unless($request->user()->hasRole('buyer'), 403);

        return view('buyers.edit', [
            'profile' => $request->user()->buyerProfile?->load('attachments'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasRole('buyer'), 403);

        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'headline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'service_categories' => ['nullable', 'string'],
            'hiring_regions' => ['nullable', 'string'],
            'vendor_onboarding' => ['nullable', 'string'],
            'payment_terms' => ['nullable', 'string'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'public_contact' => ['nullable', 'boolean'],
        ]);

        $data['public_contact'] = $request->boolean('public_contact');

        $request->user()->buyerProfile()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $data
        );

        return redirect()->route('buyers.edit')->with('status', 'Buyer profile saved.');
    }

    public function show(BuyerProfile $buyer): View
    {
        return view('buyers.show', [
            'profile' => $buyer->load('user', 'attachments', 'ratings.user'),
        ]);
    }
}
