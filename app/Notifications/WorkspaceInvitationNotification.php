<?php

namespace App\Notifications;

use App\Models\WorkspaceInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WorkspaceInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(public WorkspaceInvitation $invitation) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'workspace_invitation',
            'invitation_id' => $this->invitation->id,
            'workspace_name' => $this->invitation->workspace->name,
            'inviter_name' => $this->invitation->inviter->full_name,
            'role' => $this->invitation->role,
            'message' => $this->invitation->inviter->full_name.' شما را به فضای کاری '.$this->invitation->workspace->name.' دعوت کرده است.',
        ];
    }
}
