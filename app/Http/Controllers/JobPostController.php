<?php

namespace App\Http\Controllers;

use App\Models\JobPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobPostController extends Controller
{
    public function index(): View
    {
        return view('jobs.index', [
            'jobs' => JobPost::with('buyer.buyerProfile', 'attachments')->latest()->paginate(20),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->hasRole('buyer'), 403);

        return view('jobs.create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasRole('buyer'), 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'service_category' => ['nullable', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'starts_at' => ['nullable', 'date'],
            'time_window' => ['nullable', 'string', 'max:255'],
            'scope' => ['required', 'string'],
            'required_skills' => ['nullable', 'string'],
            'required_tools' => ['nullable', 'string'],
            'deliverables' => ['nullable', 'string'],
            'payment_terms' => ['nullable', 'string'],
            'vendor_onboarding' => ['nullable', 'string'],
            'visibility' => ['required', 'string', 'in:public,members'],
        ]);

        $job = $request->user()->jobPosts()->create($data);

        return redirect()->route('jobs.show', $job)->with('status', 'Job posted.');
    }

    public function show(JobPost $job): View
    {
        return view('jobs.show', [
            'job' => $job->load(
                'buyer.buyerProfile',
                'quotes.provider.providerProfile',
                'quotes.revisions.user',
                'workOrder',
                'attachments',
                'comments.user'
            ),
        ]);
    }
}
