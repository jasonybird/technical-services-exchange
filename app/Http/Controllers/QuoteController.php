<?php

namespace App\Http\Controllers;

use App\Models\JobPost;
use App\Models\Quote;
use App\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public function store(Request $request, JobPost $job): RedirectResponse
    {
        abort_unless($request->user()->hasRole('provider'), 403);
        abort_if($job->buyer_id === $request->user()->id, 403);
        abort_unless($job->status === 'open', 422);

        $data = $request->validate([
            'requested_amount' => ['nullable', 'numeric', 'min:0'],
            'rate_summary' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
        ]);

        $job->quotes()->create($data + [
            'provider_id' => $request->user()->id,
            'status' => 'submitted',
        ]);

        return redirect()->route('jobs.show', $job)->with('status', 'Quote submitted.');
    }

    public function accept(Request $request, Quote $quote): RedirectResponse
    {
        $quote->load('jobPost');

        abort_unless($quote->jobPost->buyer_id === $request->user()->id, 403);
        abort_unless($quote->jobPost->status === 'open', 422);

        $quote->update(['status' => 'accepted']);
        $quote->jobPost->quotes()->whereKeyNot($quote->id)->update(['status' => 'declined']);
        $quote->jobPost->update(['status' => 'assigned']);

        $workOrder = WorkOrder::create([
            'job_post_id' => $quote->job_post_id,
            'buyer_id' => $quote->jobPost->buyer_id,
            'provider_id' => $quote->provider_id,
            'accepted_quote_id' => $quote->id,
            'status' => 'assigned',
            'agreed_terms' => trim(($quote->rate_summary ?? '')."\n\n".($quote->terms ?? '')),
            'deliverables_checklist' => $quote->jobPost->deliverables,
            'status_history' => [[
                'status' => 'assigned',
                'user_id' => $request->user()->id,
                'at' => now()->toIso8601String(),
            ]],
        ]);

        return redirect()->route('work-orders.show', $workOrder)->with('status', 'Quote accepted and work order created.');
    }
}
