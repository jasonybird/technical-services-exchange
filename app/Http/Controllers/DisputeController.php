<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use App\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DisputeController extends Controller
{
    public function show(Request $request, Dispute $dispute): View
    {
        $dispute->load('workOrder.buyer', 'workOrder.provider', 'openedBy');

        abort_unless(
            in_array($request->user()->id, [$dispute->workOrder->buyer_id, $dispute->workOrder->provider_id], true)
                || $request->user()->hasRole('admin'),
            403
        );

        return view('disputes.show', ['dispute' => $dispute]);
    }

    public function store(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        abort_unless(in_array($request->user()->id, [$workOrder->buyer_id, $workOrder->provider_id], true), 403);

        $data = $request->validate([
            'summary' => ['required', 'string', 'max:255'],
            'claim' => ['required', 'string'],
            'evidence_notes' => ['nullable', 'string'],
        ]);

        $dispute = $workOrder->disputes()->create($data + [
            'opened_by_id' => $request->user()->id,
            'status' => 'open',
        ]);

        $workOrder->update(['status' => 'disputed']);

        return redirect()->route('disputes.show', $dispute)->with('status', 'Dispute opened for peer review.');
    }
}
