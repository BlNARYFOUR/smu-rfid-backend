<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TestController;
use Illuminate\Http\Request;

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

/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/

Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);

Route::middleware('auth', 'admin')->group(function() {
    Route::post('register', [AuthController::class, 'register']);
});

Route::prefix('test')->group(function() {
    Route::get('/', [TestController::class, 'get']);

    Route::middleware('auth')->group(function() {
        Route::get('auth', [TestController::class, 'getAuth']);

        Route::middleware('admin')->group(function() {
            Route::get('admin', [TestController::class, 'getAdmin']);
        });
    });
});
