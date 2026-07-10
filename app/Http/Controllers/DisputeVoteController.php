<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DisputeVoteController extends Controller
{
    public function store(Request $request, Dispute $dispute): RedirectResponse
    {
        abort_unless($request->user()->hasAnyRole(['provider', 'buyer', 'admin']), 403);

        $data = $request->validate([
            'recommendation' => ['required', 'string', 'in:provider,buyer,split,insufficient_evidence'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $dispute->votes()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $data
        );

        return redirect()->route('disputes.show', $dispute)->with('status', 'Peer vote saved.');
    }
}
