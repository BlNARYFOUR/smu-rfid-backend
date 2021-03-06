<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleOwnerController;
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

Route::prefix('auth')->group(function() {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('verify', [AuthController::class, 'verify']);

    Route::middleware('auth', 'jwt.refresh')->group(function() {
        Route::get('logged-in', [AuthController::class, 'getLoggedIn']);

        Route::middleware('admin')->group(function() {
            Route::post('register', [AuthController::class, 'register']);
        });
    });
});

Route::get('/', [TestController::class, 'get']);

Route::prefix('test')->group(function() {
    Route::get('/', [TestController::class, 'get']);

    Route::middleware('auth', 'jwt.refresh')->group(function() {
        Route::get('auth', [TestController::class, 'getAuth']);

        Route::middleware('admin')->group(function() {
            Route::get('admin', [TestController::class, 'getAdmin']);
        });
    });
});

Route::get('vehicles/owners/{id}/picture', [VehicleOwnerController::class, 'getVehicleOwnerImage']);

Route::middleware('auth', 'jwt.refresh')->group(function() {
    Route::prefix('vehicles')->group(function() {
        Route::prefix('rfid-tags')->group(function () {
            Route::get('{rfidTag}', [VehicleController::class, 'getByRfidTag']);
        });

        Route::prefix('owners')->group(function () {
            Route::get('/', [VehicleOwnerController::class, 'get']);
            Route::post('/', [VehicleOwnerController::class, 'newVehicleOwner']);
            Route::prefix('{id}')->group(function () {
                Route::get('/', [VehicleOwnerController::class, 'getById']);
            });
        });
    });

    Route::middleware('admin')->group(function() {
        Route::get('audits', [AuditController::class, 'get']);

        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'get']);
            Route::get('{id}', [UserController::class, 'getById']);
            Route::delete('{id}', [UserController::class, 'delete']);
            Route::put('{id}', [UserController::class, 'update']);
        });
    });
});
