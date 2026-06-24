<?php

namespace App\Notifications;

use App\Mail\NeovaNotificationMail;
use App\Models\User;
use App\Models\WorkspaceInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WorkspaceInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(public WorkspaceInvitation $invitation) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($this->shouldSendEmail($notifiable)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): NeovaNotificationMail
    {
        return (new NeovaNotificationMail(
            neovaSubject: "{$this->invitation->inviter->full_name} شما را به «{$this->invitation->workspace->name}» دعوت کرد",
            neovaTemplate: 'emails.workspace-invitation',
            neovaData: [
                'user' => $notifiable,
                'inviter' => $this->invitation->inviter->full_name,
                'workspace' => $this->invitation->workspace,
                'role' => $this->invitation->role,
                'invitationCode' => $this->invitation->code_hash,
                'expiresAt' => $this->invitation->expires_at->format('Y/m/d'),
            ],
        ))->to($notifiable->email);
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

    private function shouldSendEmail(object $notifiable): bool
    {
        if (! $notifiable instanceof User) {
            return false;
        }

        if (empty($notifiable->email)) {
            return false;
        }

        return $notifiable->hasNotificationPreference('invitations');
    }
}
