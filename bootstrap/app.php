<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            if ($request->is('api/*')) {
                return true;
            }
            return $request->expectsJson();
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            return response()->json([
                'status' => 422,
                'error' => 'Validation Failed',
                'data' => [
                    'message' => Arr::flatten($e->errors())[0],
                    'errors' => $e->errors(),
                ],
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            $message = !empty($e->getMessage()) && $e->getMessage() !== 'Unauthenticated.'
                ? $e->getMessage()
                : __('errors.unauthenticated');

            return response()->json([
                'status' => 401,
                'error' => 'Unauthenticated',
                'data' => ['message' => $message],
            ], 401);
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            $message = !empty($e->getMessage())
                ? $e->getMessage()
                : __('errors.unauthorized');

            return response()->json([
                'status' => 403,
                'error' => 'Forbidden',
                'data' => ['message' => $message],
            ], 403);
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            return response()->json([
                'status' => 404,
                'error' => 'Not Found',
                'data' => ['message' => __('errors.resource_not_found')],
            ], 404);
        });

        $exceptions->renderable(function (Throwable $e, Request $request) {
            if (!app()->isProduction()) {
                return;
            }
            return response()->json([
                'status' => 500,
                'error' => 'Internal Server Error',
                'data' => ['message' => __('errors.internal_server_error')],
            ], 500);
        });
    })->create();
