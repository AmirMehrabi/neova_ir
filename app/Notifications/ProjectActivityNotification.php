<?php

namespace App\Notifications;

use App\Mail\NeovaNotificationMail;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectActivityNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $kind,
        public string $message,
        public string $url,
        public int $projectId,
        public ?int $taskId = null,
        public ?Project $project = null,
        public ?Task $task = null,
        public ?string $actor = null,
        public ?string $fromColumn = null,
        public ?string $toColumn = null,
        public ?string $place = null,
    ) {}

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
        $templateMap = [
            'task_assigned' => 'emails.task-assigned',
            'task_updated' => 'emails.task-updated',
            'task_moved' => 'emails.task-moved',
            'task_mentioned' => 'emails.task-mentioned',
        ];

        $template = $templateMap[$this->kind] ?? 'emails.task-updated';

        return (new NeovaNotificationMail(
            neovaSubject: $this->getSubject(),
            neovaTemplate: $template,
            neovaData: [
                'user' => $notifiable,
                'actor' => $this->actor,
                'task' => $this->task,
                'project' => $this->project,
                'url' => $this->url,
                'fromColumn' => $this->fromColumn,
                'toColumn' => $this->toColumn,
                'place' => $this->place,
            ],
        ))->to($notifiable->email);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => $this->kind,
            'message' => $this->message,
            'url' => $this->url,
            'project_id' => $this->projectId,
            'task_id' => $this->taskId,
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

        $preferenceMap = [
            'task_assigned' => 'task_activity',
            'task_updated' => 'task_activity',
            'task_moved' => 'task_activity',
            'task_mentioned' => 'task_activity',
        ];

        $preference = $preferenceMap[$this->kind] ?? 'task_activity';

        return $notifiable->hasNotificationPreference($preference);
    }

    private function getSubject(): string
    {
        $subjects = [
            'task_assigned' => "{$this->actor} شما را به «{$this->task?->title}» اضافه کرد",
            'task_updated' => "{$this->actor} وظیفه «{$this->task?->title}» را به‌روز کرد",
            'task_moved' => "{$this->actor} وظیفه «{$this->task?->title}» را جابجا کرد",
            'task_mentioned' => "{$this->actor} از شما نام برد",
        ];

        return $subjects[$this->kind] ?? 'اعلان جدید در نئووا';
    }
}
