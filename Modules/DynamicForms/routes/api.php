<?php

use Illuminate\Support\Facades\Route;
use Modules\DynamicForms\App\Http\Controllers\Api\DynamicFormsApiController;

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
  Route::group(['prefix' => 'V1'], function () {
    Route::group([
      'middleware' => 'api',
      'as' => 'api.',
    ], function ($router) {
      Route::middleware('auth:api')->group(function () {
        Route::prefix('forms')->name('forms')->group(function () {
          Route::get('getAssignedForms', [DynamicFormsApiController::class, 'getAssignedForms'])->name('getAssignedForms');
          Route::post('submitForm', [DynamicFormsApiController::class, 'submitForm'])->name('submitForm');
        });
      });
    });
  });
});
