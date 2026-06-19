<?php

namespace App\Jobs;

use App\Models\WorkspaceInvitation;
use App\Services\KavenegarVerifyLookupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

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

        $kavenegar->sendWorkspaceInvitation($invitation, $this->code);
        $invitation->update(['sent_at' => now()]);
    }
}
