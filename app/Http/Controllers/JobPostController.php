<?php

namespace App\Http\Controllers;

use App\Models\JobPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobPostController extends Controller
{
    public function index(Request $request): View
    {
        $jobs = JobPost::with('buyer.buyerProfile', 'attachments', 'ratings.user');

        if ($search = $request->string('q')->toString()) {
            $jobs->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('service_category', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%")
                ->orWhere('scope', 'like', "%{$search}%")
            );
        }

        if ($status = $request->string('status')->toString()) {
            $jobs->where('status', $status);
        }

        return view('jobs.index', [
            'jobs' => $jobs->latest()->paginate(20)->withQueryString(),
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
                'comments.user',
                'ratings.user'
            ),
        ]);
    }
}
