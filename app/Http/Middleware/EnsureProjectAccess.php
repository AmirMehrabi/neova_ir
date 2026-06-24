<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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

        return $next($request);
    }
}
