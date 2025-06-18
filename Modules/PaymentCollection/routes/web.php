<?php

use App\Http\Middleware\AddonCheckMiddleware;
use Illuminate\Support\Facades\Route;
use Modules\PaymentCollection\App\Http\Controllers\PaymentCollectionController;
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
    'api',
    'auth',
    AddonCheckMiddleware::class,
  ])->group(function () {
    Route::group([], function () {
      Route::resource('paymentcollection', PaymentCollectionController::class)->names('paymentcollection');
    });
  });
});
