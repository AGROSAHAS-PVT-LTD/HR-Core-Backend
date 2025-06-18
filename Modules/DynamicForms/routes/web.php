<?php

use App\Http\Middleware\AddonCheckMiddleware;
use Illuminate\Support\Facades\Route;
use Modules\DynamicForms\App\Http\Controllers\DynamicFormsController;
use Modules\DynamicForms\App\Http\Controllers\FormAssignmentController;
use Modules\DynamicForms\App\Http\Controllers\FormSubmissionController;

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
  $request->headers->set('addon', ModuleConstants::DYNAMIC_FORMS);
  return $next($request);
}], function () {
  Route::middleware([
    'web',
    'auth',
    AddonCheckMiddleware::class,
  ])->group(function () {
    Route::prefix('forms')->name('forms.')->group(function () {
      Route::get('', [DynamicFormsController::class, 'index'])->name('index');
      Route::get('create', [DynamicFormsController::class, 'create'])->name('create');
      Route::post('store', [DynamicFormsController::class, 'store'])->name('store');
      Route::get('show/{id}', [DynamicFormsController::class, 'show'])->name('show');
      Route::get('edit/{id}', [DynamicFormsController::class, 'edit'])->name('edit');
      Route::post('update', [DynamicFormsController::class, 'update'])->name('update');
      Route::delete('destroy/{id}', [DynamicFormsController::class, 'destroy'])->name('destroy');

      Route::post('changeStatus', [DynamicFormsController::class, 'changeStatus'])->name('changeStatus');
      Route::get('addFields/{formId}', [DynamicFormsController::class, 'addFields'])->name('addFields');
      Route::post('storeFields', [DynamicFormsController::class, 'storeFields'])->name('storeFields');
    });

    Route::prefix('formSubmissions')->name('formSubmissions.')->group(function () {
      Route::get('', [FormSubmissionController::class, 'index'])->name('index');
      Route::get('show/{id}', [FormSubmissionController::class, 'show'])->name('show');
      Route::get('destroy/{id}', [FormSubmissionController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('formAssignments')->name('formAssignments.')->group(function () {
      Route::get('', [FormAssignmentController::class, 'index'])->name('index');
      Route::get('assignForm', [FormAssignmentController::class, 'assignForm'])->name('assignForm');
      Route::post('assign', [FormAssignmentController::class, 'assign'])->name('assign');
      Route::get('destroy/{id}', [FormAssignmentController::class, 'destroy'])->name('destroy');
    });

  });
});
