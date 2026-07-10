<?php

namespace App\Http\Controllers;

use App\Models\JobPost;
use App\Models\TaxonomyTerm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobPostController extends Controller
{
    public function index(Request $request): View
    {
        $jobs = JobPost::with('buyer.buyerProfile', 'workCategory', 'workSpecialty', 'attachments', 'ratings.user')
            ->withCount('quotes');

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

        if ($category = $request->integer('work_category_id')) {
            $jobs->where('work_category_id', $category);
        }

        if ($level = $request->integer('technician_level')) {
            $jobs->where('required_technician_level', '<=', $level);
        }

        if ($scope = $request->string('scope_clarity')->toString()) {
            $jobs->where('scope_clarity_status', $scope);
        }

        if ($request->boolean('support_certified')) {
            $jobs->where('contact_certified', true);
        }

        if ($request->boolean('remote_only')) {
            $jobs->where(fn ($query) => $query->where('remote_eligible', true)->orWhere('work_mode', 'remote'));
        }

        if ($request->boolean('hide_risky')) {
            $jobs->where(fn ($query) => $query->whereNull('risk_flags')->orWhereJsonLength('risk_flags', 0));
        }

        return view('jobs.index', [
            'jobs' => $jobs->latest()->paginate(20)->withQueryString(),
            'categories' => TaxonomyTerm::where('type', 'work_category')->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'technicianLevels' => config('technician-levels'),
            'filters' => $request->only(['q', 'status', 'work_category_id', 'technician_level', 'scope_clarity', 'support_certified', 'remote_only', 'hide_risky']),
        ]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->hasRole('buyer'), 403);

        return view('jobs.create', [
            'categories' => TaxonomyTerm::where('type', 'work_category')->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'specialties' => TaxonomyTerm::where('type', 'work_specialty')->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'technicianLevels' => config('technician-levels'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasRole('buyer'), 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'service_category' => ['nullable', 'string', 'max:255'],
            'work_category_id' => ['nullable', 'integer', 'exists:taxonomy_terms,id'],
            'work_specialty_id' => ['nullable', 'integer', 'exists:taxonomy_terms,id'],
            'required_technician_level' => ['required', 'integer', 'min:1', 'max:5'],
            'work_mode' => ['required', 'string', 'in:onsite,remote,hybrid'],
            'pay_type' => ['nullable', 'string', 'in:fixed,hourly,blended,not_listed'],
            'posted_terms_summary' => ['nullable', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'starts_at' => ['nullable', 'date'],
            'time_window' => ['nullable', 'string', 'max:255'],
            'schedule_type' => ['nullable', 'string', 'max:255'],
            'remote_eligible' => ['nullable', 'boolean'],
            'scope' => ['required', 'string'],
            'primary_objective' => ['nullable', 'string'],
            'included_work' => ['nullable', 'string'],
            'excluded_work' => ['nullable', 'string'],
            'maximum_onsite_expectations' => ['nullable', 'string'],
            'expected_duration' => ['nullable', 'string', 'max:255'],
            'required_skills' => ['nullable', 'string'],
            'required_tools' => ['nullable', 'string'],
            'required_certifications' => ['nullable', 'string'],
            'required_safety_gear' => ['nullable', 'string'],
            'deliverables' => ['nullable', 'string'],
            'closeout_conditions' => ['nullable', 'string'],
            'buyer_provided_equipment' => ['nullable', 'string'],
            'provider_provided_equipment' => ['nullable', 'string'],
            'return_shipment_expectations' => ['nullable', 'string'],
            'parking_access_notes' => ['nullable', 'string'],
            'onsite_restrictions' => ['nullable', 'string'],
            'supplemental_instructions' => ['nullable', 'string'],
            'payment_terms' => ['nullable', 'string'],
            'vendor_onboarding' => ['nullable', 'string'],
            'primary_contact_name' => ['nullable', 'string', 'max:255'],
            'primary_contact_phone' => ['nullable', 'string', 'max:255'],
            'primary_contact_email' => ['nullable', 'email', 'max:255'],
            'backup_contact_name' => ['nullable', 'string', 'max:255'],
            'backup_contact_phone' => ['nullable', 'string', 'max:255'],
            'backup_contact_email' => ['nullable', 'email', 'max:255'],
            'dispatch_contact_name' => ['nullable', 'string', 'max:255'],
            'dispatch_contact_phone' => ['nullable', 'string', 'max:255'],
            'dispatch_contact_email' => ['nullable', 'email', 'max:255'],
            'technical_bridge' => ['nullable', 'string', 'max:255'],
            'escalation_contact' => ['nullable', 'string', 'max:255'],
            'support_channel' => ['nullable', 'string', 'max:255'],
            'support_expected_response_time' => ['nullable', 'string', 'max:255'],
            'support_availability_window' => ['nullable', 'string', 'max:255'],
            'contact_certified' => ['nullable', 'accepted'],
            'visibility' => ['required', 'string', 'in:public,members'],
        ]);

        $job = new JobPost($data);
        $job->buyer_id = $request->user()->id;
        $job->remote_eligible = $request->boolean('remote_eligible');
        $job->contact_certified = $request->boolean('contact_certified');

        if ($job->contact_certified) {
            $job->contact_certified_by_id = $request->user()->id;
            $job->contact_certified_at = now();
        }

        $job->risk_flags = $job->computeRiskFlags();
        $job->scope_clarity_status = $job->computedScopeClarityStatus();
        $job->save();

        return redirect()->route('jobs.show', $job)->with('status', 'Job posted.');
    }

    public function show(JobPost $job): View
    {
        return view('jobs.show', [
            'job' => $job->load(
                'buyer.buyerProfile',
                'workCategory',
                'workSpecialty',
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
