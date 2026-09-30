<?php

namespace App\Http\Middleware;

use App\Events\ProjectRealtimeChanged;
use App\Events\TodayRealtimeChanged;
use App\Http\Controllers\BoardController;
use App\Models\Project;
use App\Models\Task;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class DispatchRealtimeUpdates
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if ($request->isMethodSafe() || $response->getStatusCode() >= 400 || ! $request->route()) {
            return $response;
        }

        $name = (string) $request->route()->getName();
        $workspace = $request->attributes->get('workspace');
        $project = $request->attributes->get('project');

        if (! $project && $name === 'today.tasks.store') {
            $project = Project::find($request->integer('project_id'));
            $request->attributes->set('project', $project);
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

        // The originating socket is excluded from broadcasts. Return the same
        // canonical board data to that client, including reordered siblings.
        if ($projectMutation && $project && $workspace && $response instanceof JsonResponse) {
            $data = $response->getData(true);
            $data['board'] ??= app(BoardController::class)->snapshot($request)->getData(true);
            $task = $request->route('task');
            $data['board']['originTaskIds'] = $task ? [(int) ($task instanceof Task ? $task->id : $task)] : array_map('intval', $request->input('task_ids', []));
            $response->setData($data);
        }

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
