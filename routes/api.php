<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LandDataManagement;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::post('farmer/login', [AuthController::class, 'login']);

// Route::get('user', [AuthController::class, 'user']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('get-profile', [AuthController::class, 'getProfile']); 
    Route::post('/update-profiles', [AuthController::class, 'updateProfile']); 

    // Password reset for Admin & SubAdmin via API
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink']);
    Route::get('/verify-reset-token/{token}', [AuthController::class, 'verifyResetToken']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);


    Route::prefix('land')->group(function () {
        // Districts
        Route::get('districts', [LandDataManagement::class, 'getDistricts']);
        Route::post('districts', [LandDataManagement::class, 'createDistrict']);
        Route::put('districts/{id}', [LandDataManagement::class, 'updateDistrict']);
        Route::delete('districts/{id}', [LandDataManagement::class, 'deleteDistrict']);

        // Tehsils
        Route::get('districts/{id}/tehsils', [LandDataManagement::class, 'getTehsils']);
        Route::post('tehsils', [LandDataManagement::class, 'createTehsil']);
        Route::put('tehsils/{id}', [LandDataManagement::class, 'updateTehsil']);
        Route::delete('tehsils/{id}', [LandDataManagement::class, 'deleteTehsil']);

        // UCs
        Route::get('tehsils/{id}/ucs', [LandDataManagement::class, 'getUcs']);
        Route::post('ucs', [LandDataManagement::class, 'createUc']);
        Route::put('ucs/{id}', [LandDataManagement::class, 'updateUc']);
        Route::delete('ucs/{id}', [LandDataManagement::class, 'deleteUc']);

        // Villages
        Route::get('ucs/{id}/villages', [LandDataManagement::class, 'getVillages']);
        Route::post('villages', [LandDataManagement::class, 'createVillage']);
        Route::put('villages/{id}', [LandDataManagement::class, 'updateVillage']);
        Route::delete('villages/{id}', [LandDataManagement::class, 'deleteVillage']);
    });

});

