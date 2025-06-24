<?php

use App\Http\Controllers\Admin\InsuranceSubTypeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LandController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\LandDataManagement;
use App\Http\Controllers\Api\ContactUsController;
use App\Http\Controllers\Api\CropInsuranceController;
use App\Http\Controllers\Api\AuthorizedDealerController;
use App\Http\Controllers\Api\InsuranceClaimController;
use App\Http\Controllers\Api\InsuranceController;
use App\Http\Controllers\Api\InsuranceResultController;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Artisan;

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

Route::get('clear-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    return 'cache cleared';
});

Route::get('debug-post', function () {
    return response()->json(['status' => 'get working']);
});


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

    // ndvi api fetch b8 and b4 value
    Route::get('/fetch-ndvi-data', [InsuranceSubTypeController::class, 'fetchNDVIData']);

    #lands
    Route::get('/getlandrecord', [LandController::class, 'getLandRecord']);
    Route::post('/land', [LandController::class, 'store']);
    Route::post('/land-record', [LandController::class, 'landrecord']);
    Route::get('/area-units', [LandController::class, 'getAreaUnits']);
    Route::get('/getOwnershipLands', [LandController::class, 'showLands'])->middleware('auth:sanctum');

    #crops insurance
    Route::get('/crops', [CropInsuranceController::class, 'getFormOptions']);
    Route::get('insurance/form-options', [CropInsuranceController::class, 'getFormOptions']);
    Route::get('/insurancetype', [CropInsuranceController::class, 'getinsurancetype']);
    Route::get('/companies/{insuranceTypeId}', [CropInsuranceController::class, 'getCompaniesByInsuranceType']);
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

    //delete account
    Route::post('/delete-account', [AuthController::class, 'deleteAccount']);

    // farmer insurances
    Route::post('/submit-insurance', [InsuranceController::class, 'store']);
    Route::get('/insurances', [InsuranceController::class, 'getInsurances']);

    // clain request
    Route::post('/claim/submit', [InsuranceClaimController::class, 'submitClaim']);
    Route::post('/claim/select-product', [InsuranceClaimController::class, 'selectProductForClaim']);
    Route::get('/claims', [InsuranceClaimController::class, 'myClaims']);
    Route::get('/bank-details', [InsuranceClaimController::class, 'getBankDetails']);
    Route::get('/products', [InsuranceClaimController::class, 'getAvailableDealerProductsForClaim']);

    //notifications
    Route::get('/farmer/notifications', [NotificationController::class, 'farmerNotifications']);
    Route::post('/notifications-seen', [NotificationController::class, 'markAsSeen']);

});
