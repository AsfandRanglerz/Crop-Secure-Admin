<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AuthorizedDealerController;
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



Route::post('farmer/login', [AuthController::class, 'login']);
Route::post('farmer/register', [AuthController::class, 'register']);
Route::post('/dealer', [AuthorizedDealerController::class, 'authorizeDealerRegister']);

// Route::get('user', [AuthController::class, 'user']);


// password reset
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/password-reset', [AuthController::class, 'passwordReset']);


Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('get-profile', [AuthController::class, 'getProfile']); 
    Route::post('/update-profiles', [AuthController::class, 'updateProfile']); 

   
    Route::prefix('land')->group(function () {

        Route::get('districts', [LandDataManagement::class, 'getDistricts']);  
        Route::get('districts/{id}/tehsils', [LandDataManagement::class, 'getTehsils']);   
        Route::get('tehsils/{id}/ucs', [LandDataManagement::class, 'getUcs']);   
        Route::get('ucs/{id}/villages', [LandDataManagement::class, 'getVillages']);
       
    });
    
});

