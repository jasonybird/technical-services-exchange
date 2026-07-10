<?php

namespace App\Http\Controllers;

use App\Models\BuyerProfile;
use App\Models\Dispute;
use App\Models\JobPost;
use App\Models\ProviderProfile;
use App\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'rateable_type' => ['required', 'string', 'in:provider_profile,buyer_profile,job_post,work_order,dispute'],
            'rateable_id' => ['required', 'integer'],
            'category' => ['required', 'string', 'max:255'],
            'stars' => ['nullable', 'integer', 'min:1', 'max:5'],
            'thumbs_up' => ['nullable', 'boolean'],
            'body' => ['nullable', 'string', 'max:2000'],
        ]);

        abort_if(($data['stars'] ?? null) === null && ! $request->has('thumbs_up'), 422, 'A star rating or thumbs vote is required.');

        $rateable = match ($data['rateable_type']) {
            'provider_profile' => ProviderProfile::findOrFail($data['rateable_id']),
            'buyer_profile' => BuyerProfile::findOrFail($data['rateable_id']),
            'job_post' => JobPost::findOrFail($data['rateable_id']),
            'work_order' => WorkOrder::findOrFail($data['rateable_id']),
            'dispute' => Dispute::findOrFail($data['rateable_id']),
        };

        $this->authorizeRating($request, $rateable);

        $rateable->ratings()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'category' => $data['category'],
            ],
            [
                'stars' => $data['stars'] ?? null,
                'thumbs_up' => $request->has('thumbs_up') ? $request->boolean('thumbs_up') : null,
                'body' => $data['body'] ?? null,
            ]
        );

        return back()->with('status', 'Rating saved.');
    }

    private function authorizeRating(Request $request, mixed $rateable): void
    {
        $user = $request->user();

        if ($rateable instanceof ProviderProfile) {
            abort_if($rateable->user_id === $user->id, 403);
            abort_unless($user->hasAnyRole(['buyer', 'provider', 'admin']), 403);
        } elseif ($rateable instanceof BuyerProfile) {
            abort_if($rateable->user_id === $user->id, 403);
            abort_unless($user->hasAnyRole(['buyer', 'provider', 'admin']), 403);
        } elseif ($rateable instanceof JobPost) {
            abort_unless($user->hasAnyRole(['buyer', 'provider', 'admin']), 403);
        } elseif ($rateable instanceof WorkOrder) {
            abort_unless(in_array($user->id, [$rateable->buyer_id, $rateable->provider_id], true) || $user->hasRole('admin'), 403);
        } elseif ($rateable instanceof Dispute) {
            abort_unless($user->hasAnyRole(['buyer', 'provider', 'admin']), 403);
        }
    }
}
