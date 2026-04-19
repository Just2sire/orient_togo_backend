<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\LogRequestMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (\Illuminate\Http\Response|\Illuminate\Http\JsonResponse $response, \Throwable $exception, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                if ($exception instanceof \Illuminate\Validation\ValidationException) {
                    return response()->json([
                        'success' => false,
                        'message' => $exception->getMessage(),
                        'errors' => $exception->errors(),
                    ], 422);
                }

                if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ressource introuvable.',
                    ], 404);
                }
            }

            return $response;
        });
    })->create();
