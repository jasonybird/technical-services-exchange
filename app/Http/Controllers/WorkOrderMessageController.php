<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Notifications\ExchangeEventNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WorkOrderMessageController extends Controller
{
    public function store(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        abort_unless(
            in_array($request->user()->id, [$workOrder->buyer_id, $workOrder->provider_id], true)
                || $request->user()->hasRole('admin'),
            403
        );

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $workOrder->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        $recipient = $request->user()->id === $workOrder->buyer_id
            ? $workOrder->provider
            : $workOrder->buyer;

        $recipient->notify(new ExchangeEventNotification(
            'New work-order message',
            $request->user()->name.' sent a message on '.$workOrder->jobPost->title.'.',
            route('work-orders.show', $workOrder),
            'work_order_message'
        ));

        return redirect()->route('work-orders.show', $workOrder)->with('status', 'Message sent.');
    }
}
