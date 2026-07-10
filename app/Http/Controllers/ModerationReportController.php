<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\AuditLog;
use App\Models\JobPost;
use App\Models\ModerationReport;
use App\Models\ProviderProfile;
use App\Models\BuyerProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ModerationReportController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'reportable_type' => ['required', 'string', 'in:provider_profile,buyer_profile,job_post,attachment'],
            'reportable_id' => ['required', 'integer'],
            'reason_code' => ['required', 'string', 'in:'.implode(',', array_keys(ModerationReport::REASON_CODES))],
            'details' => ['required', 'string', 'max:4000'],
        ]);

        $reportable = $this->resolveReportable($data['reportable_type'], (int) $data['reportable_id']);

        $report = new ModerationReport([
            'reporter_id' => $request->user()->id,
            'reason_code' => $data['reason_code'],
            'details' => $data['details'],
            'status' => 'open',
        ]);
        $report->reportable()->associate($reportable);
        $report->save();

        AuditLog::record($request, 'moderation.reported', $report, [
            'reportable_type' => $data['reportable_type'],
            'reportable_id' => $data['reportable_id'],
            'reason_code' => $data['reason_code'],
        ]);

        return back()->with('status', 'Report submitted for moderation.');
    }

    public function moderate(Request $request, ModerationReport $report): RedirectResponse
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        $data = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', ModerationReport::STATUSES)],
            'moderation_notes' => ['nullable', 'string', 'max:4000'],
        ]);

        $report->update([
            'status' => $data['status'],
            'moderated_by_id' => $request->user()->id,
            'moderated_at' => now(),
            'moderation_notes' => $data['moderation_notes'] ?? null,
        ]);

        AuditLog::record($request, 'moderation.report_moderated', $report, [
            'status' => $data['status'],
        ]);

        return redirect()->route('admin.index')->with('status', 'Moderation report updated.');
    }

    private function resolveReportable(string $type, int $id): mixed
    {
        return match ($type) {
            'provider_profile' => ProviderProfile::findOrFail($id),
            'buyer_profile' => BuyerProfile::findOrFail($id),
            'job_post' => JobPost::findOrFail($id),
            'attachment' => Attachment::findOrFail($id),
        };
    }
}
