<?php

namespace App\Services;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Collection;

class WorkspaceContext
{
    public function all(User $user): Collection
    {
        return $user->ownedWorkspaces()->orderBy('name')->get()
            ->merge($user->workspaces()->orderBy('name')->get())
            ->unique('id')
            ->values();
    }

    public function resolve(User $user): ?Workspace
    {
        $workspaces = $this->all($user);

        return $workspaces->firstWhere('id', $user->last_workspace_id) ?? $workspaces->first();
    }

    public function remember(User $user, Workspace $workspace): void
    {
        if ($user->last_workspace_id !== $workspace->id) {
            $user->forceFill(['last_workspace_id' => $workspace->id])->saveQuietly();
        }
    }

    public function visibleProjects(Workspace $workspace, User $user): Collection
    {
        return $workspace->projects()->where('is_active', true)->orderBy('name')->get()
            ->filter(fn ($project) => $project->canUserView($user, $workspace))
            ->values();
    }
}
