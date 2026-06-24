<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyDigestNotification extends Notification
{
    use Queueable;

    public function __construct(public \Illuminate\Support\Collection $activities) {}

    public function via(object $notifiable): array
    {
        if (! $notifiable instanceof User || empty($notifiable->email)) {
            return [];
        }

        if (! $notifiable->hasNotificationPreference('digest')) {
            return [];
        }

        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('خلاصه فعالیت امروز شما')
            ->view('emails.daily-digest', [
                'user' => $notifiable,
                'activities' => $this->activities,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'daily_digest',
            'activities' => $this->activities->toArray(),
        ];
    }
}
