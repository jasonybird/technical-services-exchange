<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use App\Models\JobPost;
use App\Models\SocialPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'commentable_type' => ['required', 'string', 'in:social_post,job_post,dispute'],
            'commentable_id' => ['required', 'integer'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $commentable = match ($data['commentable_type']) {
            'social_post' => SocialPost::findOrFail($data['commentable_id']),
            'job_post' => JobPost::findOrFail($data['commentable_id']),
            'dispute' => Dispute::findOrFail($data['commentable_id']),
        };

        if ($commentable instanceof Dispute) {
            abort_unless(
                in_array($request->user()->id, [$commentable->workOrder->buyer_id, $commentable->workOrder->provider_id], true)
                    || $request->user()->hasRole('admin'),
                403
            );
        }

        $commentable->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        return back()->with('status', 'Comment saved.');
    }
}
