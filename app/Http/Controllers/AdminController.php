<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Dispute;
use App\Models\JobPost;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        return view('admin.index', [
            'counts' => [
                'users' => User::count(),
                'jobs' => JobPost::count(),
                'work_orders' => WorkOrder::count(),
                'disputes' => Dispute::count(),
                'attachments' => Attachment::count(),
            ],
            'users' => User::with('roles')->latest()->limit(10)->get(),
            'jobs' => JobPost::with('buyer')->latest()->limit(10)->get(),
            'workOrders' => WorkOrder::with('jobPost', 'buyer', 'provider')->latest()->limit(10)->get(),
            'disputes' => Dispute::with('workOrder.jobPost', 'openedBy')->latest()->limit(10)->get(),
        ]);
    }
}
