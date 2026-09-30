<?php

namespace App\Http\Middleware;

use App\Http\Controllers\BoardController;
use App\Models\Project;
use App\Models\Task;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureProjectAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $project = $request->attributes->get('project');
        $workspace = $request->attributes->get('workspace');

        if (! $project->canUserView($request->user(), $workspace)) {
            abort(403, 'شما اجازه مشاهده این پروژه را ندارید.');
        }

        if (! $project->is_active && ! $request->isMethodSafe() && ! str_starts_with((string) $request->route()->getName(), 'board.project.')) {
            abort(403, 'این پروژه بایگانی شده است. برای تغییر وظیفه‌ها، ابتدا پروژه را بازگردانید.');
        }

        if ($request->isMethodSafe()) {
            return $next($request);
        }

        // Serialize board mutations per project so version checks and writes,
        // including multi-task ordering changes, share a transaction.
        return DB::transaction(function () use ($request, $next, $project, $workspace) {
            $locked = Project::query()->whereKey($project->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->canUserView($request->user(), $workspace), 403);
            if (! $locked->is_active && ! str_starts_with((string) $request->route()->getName(), 'board.project.')) {
                abort(403, 'این پروژه بایگانی شده است.');
            }
            $request->attributes->set('project', $locked);
            $task = $request->route('task');
            if ($task instanceof Task) {
                $fresh = $task->fresh();
                abort_unless($fresh, 404);
                $request->route()->setParameter('task', $fresh);
            }

            $response = $next($request);
            if ($response instanceof JsonResponse && $response->getStatusCode() < 400) {
                $data = $response->getData(true);
                $data['board'] = app(BoardController::class)->snapshot($request)->getData(true);
                $response->setData($data);
            }

            return $response;
        });
    }
}
