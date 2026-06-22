<?php

namespace App\Notifications;

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
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
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
}
