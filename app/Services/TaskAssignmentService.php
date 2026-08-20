<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;

class TaskAssignmentService
{
    public function eligible(Task $task): Collection
    {
        return $task->loadMissing('column.project.workspace')->column->project->eligibleAssignees();
    }

    public function syncFromNames(Task $task, array $names): void
    {
        $eligible = $this->eligible($task);
        $existingLegacy = collect($task->assignees ?? []);
        $users = collect($names)->map(function (string $name) use ($eligible, $existingLegacy) {
            $matches = $eligible->filter(fn (User $user) => $user->full_name === $name);

            abort_unless($matches->count() === 1 || $existingLegacy->contains($name), 422, 'یک یا چند مسئول انتخاب‌شده معتبر نیستند.');

            return $matches->count() === 1 ? $matches->first() : null;
        })->filter()->unique('id')->values();

        $task->assignedUsers()->sync($users->pluck('id'));
        $task->update(['assignees' => array_values(array_unique($names))]);
    }

    public function assignToMe(Task $task, User $user): void
    {
        abort_unless($this->eligible($task)->contains('id', $user->id), 422, 'شما نمی‌توانید مسئول این وظیفه باشید.');

        $task->assignedUsers()->syncWithoutDetaching([$user->id]);
        $names = $task->assignedUsers()->get()->map->full_name->values()->all();
        $task->update(['assignees' => $names]);
    }

    public function backfill(Task $task): void
    {
        if ($task->assignedUsers()->exists() || empty($task->assignees)) {
            return;
        }

        $eligible = $this->eligible($task);
        $ids = collect($task->assignees)->map(function (string $name) use ($eligible) {
            $matches = $eligible->filter(fn (User $user) => $user->full_name === $name);
            return $matches->count() === 1 ? $matches->first()->id : null;
        })->filter()->unique()->values();

        $task->assignedUsers()->syncWithoutDetaching($ids);
    }
}
