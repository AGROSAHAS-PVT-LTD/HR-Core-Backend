<?php

use App\Http\Middleware\LoadSettings;
use App\Http\Middleware\LocaleMiddleware;
use App\Http\Middleware\TransformApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    api: __DIR__ . '/../routes/api.php',
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware) {
    $middleware->web(LocaleMiddleware::class);
    $middleware->web(LoadSettings::class);
    $middleware->appendToGroup('api', [
      TransformApiResponse::class,
    ]);
    $middleware->alias([
      'role' => RoleMiddleware::class,
      'permission' => PermissionMiddleware::class,
      'role_or_permission' => RoleOrPermissionMiddleware::class,
    ]);
  })
  ->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (AuthenticationException $e, Request $request) {
      if ($request->is('api/*')) {
        return response()->json([
          'statusCode' => 401,
          'status' => 'failed',
          'data' => 'Unauthorized'
        ], 401);
      } else {
        return redirect()->guest('auth/login');
      }
    });

    //Validation exception of api response
    $exceptions->render(function (ValidationException $e, Request $request) {
      if ($request->is('api/*')) {
        return response()->json([
          'status' => 'failed',
          'message' => $e->getMessage(),
          'data' => $e->errors()
        ], 422);
      }
    });

    /* $exceptions->renderable(function (Throwable $e) {
       return response()->json([
         'statusCode' => 500,
         'status' => 'Something went wrong',
         'data' => $e->getMessage()
       ], 500);
     });*/
  })->create();
