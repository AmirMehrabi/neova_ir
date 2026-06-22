<?php

namespace App\Jobs;

use App\Models\WorkspaceInvitation;
use App\Services\KavenegarVerifyLookupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendWorkspaceInvitationSms implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public int $invitationId,
        public string $code,
    ) {}

    public function handle(KavenegarVerifyLookupService $kavenegar): void
    {
        $invitation = WorkspaceInvitation::with(['workspace', 'inviter'])->find($this->invitationId);

        if (! $invitation || ! $invitation->isPending()) {
            return;
        }

        try {
            $kavenegar->sendWorkspaceInvitation($invitation, $this->code);
            $invitation->update(['sent_at' => now()]);
        } catch (\Throwable $e) {
            Log::error('Failed to send workspace invitation SMS', [
                'invitation_id' => $this->invitationId,
                'phone' => $invitation->phone,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
