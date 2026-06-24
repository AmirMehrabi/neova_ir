<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\View;

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
        $html = View::make('emails.daily-digest', [
            'user' => $notifiable,
            'activities' => $this->activities,
        ])->render();

        return (new MailMessage())
            ->subject('خلاصه فعالیت امروز شما')
            ->html($html);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'daily_digest',
            'activities' => $this->activities->toArray(),
        ];
    }
}
