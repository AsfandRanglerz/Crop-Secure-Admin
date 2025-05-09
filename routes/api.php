<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LandController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\LandDataManagement;
use App\Http\Controllers\Api\ContactUsController;
use App\Http\Controllers\Api\CropInsuranceController;
use App\Http\Controllers\Api\AuthorizedDealerController;

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



Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/dealer', [AuthorizedDealerController::class, 'authorizeDealerRegister']);

// Route::get('user', [AuthController::class, 'user']);

//get land record
Route::get('/landdistricts', [LandController::class, 'getDistricts']);  
Route::get('/tehsils/{district_id}', [LandController::class, 'getTehsils']);
Route::get('/ucs/{tehsil_id}', [LandController::class, 'getUCs']);
Route::get('/villages/{uc_id}', [LandController::class, 'getVillages']);

// password reset
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/password-reset', [AuthController::class, 'passwordReset']);

//land data management
Route::get('/districts', [LandDataManagement::class, 'getDistricts']);  

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('get-profile', [AuthController::class, 'getProfile']); 
    Route::post('/update-profiles', [AuthController::class, 'updateProfile']); 

   
    Route::prefix('land')->group(function () {

        Route::get('districts/{id}/tehsils', [LandDataManagement::class, 'getTehsils']);   
        Route::get('tehsils/{id}/ucs', [LandDataManagement::class, 'getUcs']);   
        Route::get('ucs/{id}/villages', [LandDataManagement::class, 'getVillages']);
       
    });

    #lands
    Route::post('/land', [LandController::class, 'store']);
    Route::post('/land-record', [LandController::class, 'landrecord']);
    Route::get('/showlands', [LandController::class, 'showlands']);
    Route::get('/area-units', [LandController::class, 'getAreaUnits']);

    #crops insurance
    Route::get('insurance/form-options', [CropInsuranceController::class, 'getFormOptions']);
    Route::get('insurance/insurancetype', [CropInsuranceController::class, 'getinsurancetype']);
    Route::get('insurance/companies/{insuranceTypeId}', [CropInsuranceController::class, 'getCompaniesByInsuranceType']);
    Route::get('insurance/benchmarks/{insuranceTypeId}', [CropInsuranceController::class, 'getBenchmarksByInsuranceType']);
    Route::get('/getinsurances', [CropInsuranceController::class, 'getinsurance']);
    Route::get('/claims', [CropInsuranceController::class, 'claim']);
    Route::get('/getclaims', [CropInsuranceController::class, 'getclaim']);
    Route::post('/postclaims', [CropInsuranceController::class, 'postclaim']);
    Route::post('insurance/store', [CropInsuranceController::class, 'store']);

    #contact us
    Route::get('/contact', [ContactUsController::class, 'getContact']);
    Route::post('/contact-email', [ContactUsController::class, 'sendEmail']);

    #dealer products
    Route::get('/dealer-products/{dealerId}', [ProductController::class, 'getDealerProducts']);
    Route::post('/add-list', [ProductController::class, 'addToList']);
    Route::get('/getlist', [ProductController::class, 'getAddedList']);
    Route::post('/deletelist/{id}', [ProductController::class, 'deleteFromList']);

    
});






