<?php

namespace App\Http\Controllers;

use App\Models\JobPost;
use App\Models\Quote;
use App\Models\WorkOrder;
use App\Notifications\ExchangeEventNotification;
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

        $quote = $job->quotes()->create($data + [
            'provider_id' => $request->user()->id,
            'status' => 'submitted',
        ]);

        $quote->revisions()->create($data + [
            'user_id' => $request->user()->id,
            'action' => 'submitted',
        ]);

        $job->buyer->notify(new ExchangeEventNotification(
            'New quote received',
            $request->user()->name.' submitted a quote for '.$job->title.'.',
            route('jobs.show', $job),
            'quote_submitted'
        ));

        return redirect()->route('jobs.show', $job)->with('status', 'Quote submitted.');
    }

    public function update(Request $request, Quote $quote): RedirectResponse
    {
        $quote->load('jobPost');

        abort_unless($quote->provider_id === $request->user()->id, 403);
        abort_unless(in_array($quote->status, ['submitted', 'countered', 'revised'], true), 422);
        abort_unless($quote->jobPost->status === 'open', 422);

        $data = $request->validate([
            'requested_amount' => ['nullable', 'numeric', 'min:0'],
            'rate_summary' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
        ]);

        $quote->update($data + ['status' => 'revised']);
        $quote->revisions()->create($data + [
            'user_id' => $request->user()->id,
            'action' => 'revised',
        ]);

        $quote->jobPost->buyer->notify(new ExchangeEventNotification(
            'Quote revised',
            $request->user()->name.' revised a quote for '.$quote->jobPost->title.'.',
            route('jobs.show', $quote->jobPost),
            'quote_revised'
        ));

        return redirect()->route('jobs.show', $quote->jobPost)->with('status', 'Quote revised.');
    }

    public function decline(Request $request, Quote $quote): RedirectResponse
    {
        $quote->load('jobPost');

        abort_unless($quote->jobPost->buyer_id === $request->user()->id, 403);
        abort_unless($quote->jobPost->status === 'open', 422);

        $quote->update(['status' => 'declined']);
        $quote->revisions()->create([
            'user_id' => $request->user()->id,
            'action' => 'declined',
        ]);

        $quote->provider->notify(new ExchangeEventNotification(
            'Quote declined',
            $quote->jobPost->buyer->name.' declined your quote for '.$quote->jobPost->title.'.',
            route('jobs.show', $quote->jobPost),
            'quote_declined'
        ));

        return redirect()->route('jobs.show', $quote->jobPost)->with('status', 'Quote declined.');
    }

    public function accept(Request $request, Quote $quote): RedirectResponse
    {
        $quote->load('jobPost');

        abort_unless($quote->jobPost->buyer_id === $request->user()->id, 403);
        abort_unless($quote->jobPost->status === 'open', 422);

        $quote->update(['status' => 'accepted']);
        $quote->revisions()->create([
            'user_id' => $request->user()->id,
            'action' => 'accepted',
        ]);
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
            'scheduled_at' => $quote->jobPost->starts_at,
            'appointment_window' => $quote->jobPost->time_window,
            'checklist_items' => collect(preg_split('/\r\n|\r|\n/', (string) $quote->jobPost->deliverables))
                ->map(fn (string $item): string => trim($item, " \t\n\r\0\x0B-[]"))
                ->filter()
                ->values()
                ->all(),
            'required_evidence' => $quote->jobPost->deliverables,
            'status_history' => [[
                'status' => 'assigned',
                'user_id' => $request->user()->id,
                'at' => now()->toIso8601String(),
            ]],
        ]);

        $quote->provider->notify(new ExchangeEventNotification(
            'Quote accepted',
            $quote->jobPost->buyer->name.' accepted your quote for '.$quote->jobPost->title.'.',
            route('work-orders.show', $workOrder),
            'quote_accepted'
        ));

        return redirect()->route('work-orders.show', $workOrder)->with('status', 'Quote accepted and work order created.');
    }
}
