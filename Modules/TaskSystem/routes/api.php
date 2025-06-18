<?php

use Illuminate\Support\Facades\Route;
use Modules\TaskSystem\App\Http\Controllers\Api\TaskSystemApiController;

/*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register API routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | is assigned the "api" middleware group. Enjoy building your API!
    |
*/
Route::middleware([
  'api',
])->group(function () {
  Route::middleware('auth:api')->group(function () {
    Route::group(['prefix' => 'V1'], function () {
      Route::group([
        'middleware' => 'api',
        'as' => 'api.',
      ], function ($router) {
        Route::get('task/GetAll', [TaskSystemApiController::class, 'getTasks'])->name('getAll');
        Route::post('task/startTask', [TaskSystemApiController::class, 'startTask'])->name('startTask');
        Route::post('task/completeTask', [TaskSystemApiController::class, 'completeTask'])->name('completeTask');
        Route::post('task/holdTask', [TaskSystemApiController::class, 'holdTask'])->name('holdTask');
        Route::post('task/resumeTask', [TaskSystemApiController::class, 'resumeTask'])->name('resumeTask');
        Route::get('task/getTaskUpdates', [TaskSystemApiController::class, 'getTaskUpdates'])->name('getTaskUpdates');
        Route::post('task/updateStatus', [TaskSystemApiController::class, 'updateStatus'])->name('updateStatus');
        Route::post('task/updateStatusFile', [TaskSystemApiController::class, 'updateStatusFile'])->name('updateStatusFile');
      });
    });
  });
});
