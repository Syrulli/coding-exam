<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Illuminate\Foundation\Configuration\Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                $status = match (true) {
                    $e instanceof Illuminate\Database\Eloquent\ModelNotFoundException,
                    $e instanceof Symfony\Component\HttpKernel\Exception\NotFoundHttpException => 404,
                    $e instanceof Illuminate\Auth\AuthenticationException => 401,
                    $e instanceof Illuminate\Validation\ValidationException => 422,
                    default => 500,
                };

                return response()->json([
                    'message' => $status === 404 ? 'Resource not found.' : $e->getMessage(),
                ], $status);
            }
        });
    })->create();
