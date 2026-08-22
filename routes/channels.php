<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Project;
use App\Models\Workspace;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('project.{projectId}', function ($user, int $projectId) {
    $project = Project::with('workspace')->find($projectId);

    return $project && $project->workspace->hasMember($user) && $project->canUserView($user, $project->workspace);
});

Broadcast::channel('workspace.{workspaceId}.today', function ($user, int $workspaceId) {
    return Workspace::find($workspaceId)?->hasMember($user) ?? false;
});
