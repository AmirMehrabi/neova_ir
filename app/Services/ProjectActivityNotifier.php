<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\ProjectActivityNotification;
use Illuminate\Support\Collection;

class ProjectActivityNotifier
{
    public function taskCreated(Task $task, User $actor): void
    {
        $project = $this->project($task);
        $this->usersForAssigneeNames($project, $task->assignees ?? [])
            ->reject(fn (User $user) => $user->is($actor))
            ->each(fn (User $user) => $this->notify(
                $user,
                'task_assigned',
                "{$actor->full_name} شما را به وظیفه «{$task->title}» در پروژه «{$project->name}» اضافه کرد.",
                $project,
                $task,
            ));

        $this->notifyMentions($task, $actor, [], $task->comments ?? [], '', (string) $task->description);
    }

    public function taskUpdated(Task $task, array $before, User $actor): void
    {
        $project = $this->project($task);
        $beforeAssignees = collect($before['assignees'] ?? []);
        $afterAssignees = collect($task->assignees ?? []);
        $newAssignees = $afterAssignees->diff($beforeAssignees)->values();
        $newAssigneeUsers = $this->usersForAssigneeNames($project, $newAssignees->all());

        $newAssigneeUsers
            ->reject(fn (User $user) => $user->is($actor))
            ->each(fn (User $user) => $this->notify(
                $user,
                'task_assigned',
                "{$actor->full_name} شما را به وظیفه «{$task->title}» در پروژه «{$project->name}» اضافه کرد.",
                $project,
                $task,
            ));

        $mentionedIds = $this->notifyMentions(
            $task,
            $actor,
            $before['comments'] ?? [],
            $task->comments ?? [],
            (string) ($before['description'] ?? ''),
            (string) $task->description,
        );

        $changed = collect([
            'title', 'description', 'priority', 'due_date', 'tags', 'checklist', 'comments', 'column_id',
        ])->contains(fn (string $field) => ($before[$field] ?? null) != $task->{$field});

        if (! $changed) {
            return;
        }

        $excludedIds = $newAssigneeUsers->pluck('id')->merge($mentionedIds)->push($actor->id)->unique();

        $this->usersForAssigneeNames($project, $afterAssignees->all())
            ->reject(fn (User $user) => $excludedIds->contains($user->id))
            ->each(fn (User $user) => $this->notify(
                $user,
                'task_updated',
                "{$actor->full_name} وظیفه «{$task->title}» در پروژه «{$project->name}» را تغییر داد.",
                $project,
                $task,
            ));
    }

    public function taskMoved(Task $task, User $actor, string $columnTitle): void
    {
        $project = $this->project($task);
        $this->usersForAssigneeNames($project, $task->assignees ?? [])
            ->reject(fn (User $user) => $user->is($actor))
            ->each(fn (User $user) => $this->notify(
                $user,
                'task_moved',
                "{$actor->full_name} وظیفه «{$task->title}» را به ستون «{$columnTitle}» منتقل کرد.",
                $project,
                $task,
            ));
    }

    private function notifyMentions(
        Task $task,
        User $actor,
        array $beforeComments,
        array $afterComments,
        string $beforeDescription,
        string $afterDescription,
    ): Collection {
        $project = $this->project($task);
        $descriptionMentionIds = $beforeDescription === $afterDescription
            ? collect()
            : $this->extractMentionIds($afterDescription);
        $newComments = array_slice($afterComments, count($beforeComments));
        $commentMentionIds = collect($newComments)
            ->flatMap(fn (array $comment) => $comment['mention_ids'] ?? $this->extractMentionIds((string) ($comment['text'] ?? '')))
            ->map(fn ($id) => (int) $id);
        $mentionIds = $descriptionMentionIds->merge($commentMentionIds)->unique()->reject(fn ($id) => $id === $actor->id);

        $projectMemberIds = $project->members()->pluck('users.id')->push($project->workspace->owner_id)->unique();
        $users = User::query()->whereIn('id', $mentionIds->intersect($projectMemberIds))->get();

        foreach ($users as $user) {
            $inComment = $commentMentionIds->contains($user->id);
            $place = $inComment ? 'گفتگوی' : 'توضیحات';
            $this->notify(
                $user,
                'task_mentioned',
                "{$actor->full_name} در {$place} وظیفه «{$task->title}» از شما نام برد.",
                $project,
                $task,
            );
        }

        return $users->pluck('id');
    }

    private function extractMentionIds(string $text): Collection
    {
        preg_match_all('/@\[[^\]]+\]\(user:(\d+)\)/u', $text, $matches);

        return collect($matches[1] ?? [])->map(fn ($id) => (int) $id)->unique();
    }

    private function usersForAssigneeNames(Project $project, array $names): Collection
    {
        if ($names === []) {
            return collect();
        }

        return $project->members()
            ->get()
            ->filter(fn (User $user) => in_array($user->full_name, $names, true))
            ->values();
    }

    private function project(Task $task): Project
    {
        return $task->loadMissing('column.project.workspace')->column->project;
    }

    private function notify(User $user, string $kind, string $message, Project $project, Task $task): void
    {
        $user->notify(new ProjectActivityNotification(
            $kind,
            $message,
            route('board', [$project->workspace->slug, $project->slug], false),
            $project->id,
            $task->id,
        ));
    }
}
