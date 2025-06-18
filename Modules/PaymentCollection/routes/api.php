<?php

use Illuminate\Support\Facades\Route;
use Modules\PaymentCollection\App\Http\Controllers\Api\PaymentCollectionApiController;

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
        Route::get('paymentCollection/getAll', [PaymentCollectionApiController::class, 'getAll'])->name('getAll');
        Route::post('paymentCollection/create', [PaymentCollectionApiController::class, 'create'])->name('create');
      });
    });
  });
});
