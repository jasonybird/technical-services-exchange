<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use App\Models\ExternalProfileImport;
use App\Models\JobPost;
use App\Models\ProviderProfile;
use App\Models\SocialPost;
use App\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttachmentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'attachable_type' => ['required', 'string', 'in:provider_profile,buyer_profile,social_post,job_post,work_order,dispute,external_profile_import'],
            'attachable_id' => ['required', 'integer'],
            'kind' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:1000'],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $attachable = $this->resolveAttachable($data['attachable_type'], (int) $data['attachable_id']);
        $this->authorizeAttachment($request, $attachable);

        $file = $request->file('file');
        $path = $file->store('attachments/'.Str::slug($data['attachable_type']), 'public');

        $attachable->attachments()->create([
            'user_id' => $request->user()->id,
            'kind' => $data['kind'] ?? 'general',
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize() ?: 0,
            'caption' => $data['caption'] ?? null,
        ]);

        return back()->with('status', 'File uploaded.');
    }

    private function resolveAttachable(string $type, int $id): mixed
    {
        return match ($type) {
            'provider_profile' => ProviderProfile::findOrFail($id),
            'buyer_profile' => \App\Models\BuyerProfile::findOrFail($id),
            'social_post' => SocialPost::findOrFail($id),
            'job_post' => JobPost::findOrFail($id),
            'work_order' => WorkOrder::findOrFail($id),
            'dispute' => Dispute::findOrFail($id),
            'external_profile_import' => ExternalProfileImport::findOrFail($id),
        };
    }

    private function authorizeAttachment(Request $request, mixed $attachable): void
    {
        $user = $request->user();

        if ($attachable instanceof ProviderProfile) {
            abort_unless($attachable->user_id === $user->id || $user->hasRole('admin'), 403);
        } elseif ($attachable instanceof \App\Models\BuyerProfile) {
            abort_unless($attachable->user_id === $user->id || $user->hasRole('admin'), 403);
        } elseif ($attachable instanceof SocialPost) {
            abort_unless($attachable->user_id === $user->id || $user->hasRole('admin'), 403);
        } elseif ($attachable instanceof JobPost) {
            abort_unless($attachable->buyer_id === $user->id || $user->hasRole('admin'), 403);
        } elseif ($attachable instanceof WorkOrder) {
            abort_unless(in_array($user->id, [$attachable->buyer_id, $attachable->provider_id], true) || $user->hasRole('admin'), 403);
        } elseif ($attachable instanceof Dispute) {
            abort_unless(in_array($user->id, [$attachable->workOrder->buyer_id, $attachable->workOrder->provider_id], true) || $user->hasRole('admin'), 403);
        } elseif ($attachable instanceof ExternalProfileImport) {
            abort_unless($attachable->providerProfile->user_id === $user->id || $user->hasRole('admin'), 403);
        }
    }
}
