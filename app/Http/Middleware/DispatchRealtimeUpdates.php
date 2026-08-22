<?php

namespace App\Http\Middleware;

use App\Events\ProjectRealtimeChanged;
use App\Events\TodayRealtimeChanged;
use App\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class DispatchRealtimeUpdates
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if ($request->isMethodSafe() || $response->getStatusCode() >= 400 || ! $request->route()) return $response;

        $name = (string) $request->route()->getName();
        $workspace = $request->attributes->get('workspace');
        $project = $request->attributes->get('project');

        if (! $project && $name === 'today.tasks.store') {
            $project = Project::find($request->integer('project_id'));
        }

        $projectMutation = str_starts_with($name, 'board.')
            || str_starts_with($name, 'cycles.')
            || str_starts_with($name, 'task.attachments.')
            || in_array($name, ['today.tasks.store', 'today.task.plan', 'today.task.state'], true);
        $todayMutation = str_starts_with($name, 'today.')
            || str_starts_with($name, 'board.task.')
            || $name === 'board.tasks.bulk'
            || str_starts_with($name, 'board.column')
            || str_starts_with($name, 'board.project.');

        try {
            if ($projectMutation && $project) {
                broadcast(new ProjectRealtimeChanged((int) $project->id, $name, $request->user()?->id))->toOthers();
            }
            if ($todayMutation && $workspace) {
                broadcast(new TodayRealtimeChanged((int) $workspace->id, $name, $request->user()?->id))->toOthers();
            }
        } catch (Throwable $exception) {
            report($exception);
        }

        return $response;
    }
}
