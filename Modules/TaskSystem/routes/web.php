<?php

use App\Http\Middleware\AddonCheckMiddleware;
use Illuminate\Support\Facades\Route;
use Modules\TaskSystem\App\Http\Controllers\TaskSystemController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::group(['middleware' => function ($request, $next) {
  $request->headers->set('addon', ModuleConstants::PAYMENT_COLLECTION);
  return $next($request);
}], function () {
  Route::middleware([
    'web',
    'auth',
    AddonCheckMiddleware::class,
  ])->group(function () {
    Route::group([], function () {
      Route::get('/task', [TaskSystemController::class, 'index'])->name('task.index');
      Route::get('taskView', [TaskSystemController::class, 'taskView'])->name('taskView');
      Route::get('task/create', [TaskSystemController::class, 'create'])->name('task.create');
      Route::post('task/store', [TaskSystemController::class, 'store'])->name('task.store');
      Route::get('task/activity/{id}', [TaskSystemController::class, 'activity'])->name('task.activity');
      Route::get('task/show/{id}', [TaskSystemController::class, 'show'])->name('task.show');
      Route::delete('task/destroy/{id}', [TaskSystemController::class, 'destroy'])->name('task.destroy');
      Route::post('task/addTaskUpdate', [TaskSystemController::class, 'addTaskUpdate'])->name('task.addTaskUpdate');
      Route::post('task/getClientLocation', [TaskSystemController::class, 'getClientLocation'])->name('task.getClientLocation');
    });
  });
});
