<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\DailyDigestNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SendDailyDigestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $users = User::query()
            ->whereNotNull('email')
            ->where('is_active', true)
            ->get()
            ->filter(fn (User $user) => $user->hasNotificationPreference('digest'));

        foreach ($users as $user) {
            $activities = $this->getActivitiesForUser($user);

            if ($activities->isEmpty()) {
                continue;
            }

            $user->notify(new DailyDigestNotification($activities));
        }
    }

    private function getActivitiesForUser(User $user): \Illuminate\Support\Collection
    {
        $since = now()->subDay();

        $workspaceIds = $user->ownedWorkspaces()->pluck('workspaces.id')
            ->merge($user->workspaces()->pluck('workspaces.id'))
            ->unique();

        $projectIds = DB::table('projects')
            ->whereIn('workspace_id', $workspaceIds)
            ->pluck('id');

        $columnIds = DB::table('project_columns')
            ->whereIn('project_id', $projectIds)
            ->pluck('id');

        $notifications = DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->where('created_at', '>=', $since)
            ->where('type', 'App\\Notifications\\ProjectActivityNotification')
            ->get();

        $activities = collect();

        foreach ($notifications as $notification) {
            $data = json_decode($notification->data, true);
            $projectId = $data['project_id'] ?? null;
            $projectName = DB::table('projects')->where('id', $projectId)->value('name') ?? 'پروژه';

            $activities->push([
                'project' => $projectName,
                'message' => $data['message'] ?? '',
            ]);
        }

        return $activities->groupBy('project')->map(fn ($items) => $items->pluck('message')->toArray());
    }
}
