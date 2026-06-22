<?php

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\EnsureProject;
use App\Http\Middleware\EnsureWorkspace;
use App\Http\Middleware\EnsureWorkspaceEditor;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => Authenticate::class,
            'workspace' => EnsureWorkspace::class,
            'project' => EnsureProject::class,
            'workspace.editor' => EnsureWorkspaceEditor::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
