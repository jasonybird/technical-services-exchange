<?php

namespace App\Http\Controllers;

use App\Models\SocialPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SocialPostController extends Controller
{
    public function index(): View
    {
        return view('feed.index', [
            'posts' => SocialPost::with('user', 'attachments', 'comments.user')->latest()->paginate(20),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'visibility' => ['required', 'string', 'in:public,members'],
        ]);

        $request->user()->socialPosts()->create($data);

        return redirect()->route('feed.index')->with('status', 'Post published.');
    }
}
