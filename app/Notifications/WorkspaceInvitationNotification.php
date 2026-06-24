<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\WorkspaceInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\View;

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

    public function toMail(object $notifiable): MailMessage
    {
        $html = View::make('emails.workspace-invitation', [
            'user' => $notifiable,
            'inviter' => $this->invitation->inviter->full_name,
            'workspace' => $this->invitation->workspace,
            'role' => $this->invitation->role,
            'invitationCode' => $this->invitation->code_hash,
            'expiresAt' => $this->invitation->expires_at->format('Y/m/d'),
        ])->render();

        return (new MailMessage())
            ->subject("{$this->invitation->inviter->full_name} شما را به «{$this->invitation->workspace->name}» دعوت کرد")
            ->html($html);
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
