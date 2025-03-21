<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UcController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ItemController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\FarmerController;
use App\Http\Controllers\Admin\TehsilController;
use App\Http\Controllers\Admin\VillageController;
use App\Http\Controllers\Admin\AreaUnitController;
use App\Http\Controllers\Admin\DistrictController;
use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Admin\SubAdminController;
use App\Http\Controllers\Admin\DealerItemController;
use App\Http\Controllers\Admin\EnsuredCropController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\InsuranceTypeController;
use App\Http\Controllers\Admin\EnsuredCropNameController;
use App\Http\Controllers\Admin\AuthorizedDealerController;
use App\Http\Controllers\Admin\InsuranceCompanyController;
use App\Http\Controllers\Admin\InsuranceSubTypeController;
use App\Http\Controllers\Admin\LandDataManagementController;
use App\Http\Controllers\Admin\CompanyInsuranceTypeController;
use App\Http\Controllers\Admin\InsuranceClaimRequestController;
use App\Http\Controllers\Admin\CompanyInsuranceSubTypeController;

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
/*Admin routes
 * */

Route::get('/admin', [AuthController::class, 'getLoginPage']);
Route::post('/login', [AuthController::class, 'Login']);
Route::get('/admin-forgot-password', [AdminController::class, 'forgetPassword']);
Route::post('/admin-reset-password-link', [AdminController::class, 'adminResetPasswordLink']);
Route::get('/change_password/{id}', [AdminController::class, 'change_password']);
Route::post('/admin-reset-password', [AdminController::class, 'ResetPassword']);

Route::prefix('admin')->middleware('admin')->group(function () {
    Route::get('dashboard', [AdminController::class, 'getdashboard']);
    Route::get('profile', [AdminController::class, 'getProfile']);
    Route::post('update-profile', [AdminController::class, 'update_profile']);

    // ############ Privacy-policy #################
    Route::get('privacy-policy', [SecurityController::class, 'PrivacyPolicy']);
    Route::get('privacy-policy-edit', [SecurityController::class, 'PrivacyPolicyEdit']);
    Route::post('privacy-policy-update', [SecurityController::class, 'PrivacyPolicyUpdate']);

    // ############ Term & Condition #################
    Route::get('term-condition', [SecurityController::class, 'TermCondition']);
    Route::get('term-condition-edit', [SecurityController::class, 'TermConditionEdit']);
    Route::post('term-condition-update', [SecurityController::class, 'TermConditionUpdate']);

    // ############ About Us #################
    Route::get('about-us', [SecurityController::class, 'AboutUs']);
    Route::get('about-us-edit', [SecurityController::class, 'AboutUsEdit']);
    Route::post('about-us-update', [SecurityController::class, 'AboutUsUpdate']);

    Route::get('logout', [AdminController::class, 'logout']);

    // ############ Sub Admin #################
    Route::controller(SubAdminController::class)->group(function () {
        Route::get('/subadmin',  'index')->name('subadmin.index');
        Route::get('/subadmin-create',  'create')->name('subadmin.create');
        Route::post('/subadmin-store',  'store')->name('subadmin.store');
        Route::get('/subadmin-edit/{id}',  'edit')->name('subadmin.edit');
        Route::post('/subadmin-update/{id}',  'update')->name('subadmin.update');
        Route::delete('/subadmin-destroy/{id}',  'destroy')->name('subadmin.destroy');

        Route::post('/update-permissions/{id}', 'updatePermissions')->name('update.permissions');

        Route::post('/subadmin-StatusChange', 'StatusChange')->name('subadmin.StatusChange');
    });

    // ############ Authorized Dealers #################
    Route::controller(AuthorizedDealerController::class)->group(function () {
        Route::get('/dealer',  'index')->name('dealer.index');
        Route::get('/dealer-create',  'create')->name('dealer.create');
        Route::post('/dealer-store',  'store')->name('dealer.store');
        Route::get('/dealer-edit/{id}',  'edit')->name('dealer.edit');
        Route::post('/dealer-update/{id}',  'update')->name('dealer.update');
        Route::delete('/dealer-destroy/{id}',  'destroy')->name('dealer.destroy');
    });

    // ############ Dealer Items #################
    Route::controller(DealerItemController::class)->group(function () {
        Route::get('/dealer-items/{id}',  'index')->name('dealer.item.index');
        Route::get('/dealer-item-create/{id}',  'create')->name('dealer.item.create');
        Route::post('/dealer-item-store',  'store')->name('dealer.item.store');
        Route::get('/dealer-item-edit/{dealer_id}/{item_id}',  'edit')->name('dealer.item.edit');
        Route::post('/dealer-item-update/{id}',  'update')->name('dealer.item.update');
        Route::delete('/dealer-item-destroy/{id}',  'destroy')->name('dealer.item.destroy');
    });

    // ############ Items for Dealers Selection #################
    Route::controller(ItemController::class)->group(function () {
        Route::get('/items',  'index')->name('items.index');
        Route::post('/item-store',  'store')->name('item.store');
        Route::post('/item-update/{id}',  'update')->name('item.update');
        Route::delete('/item-destroy/{id}',  'destroy')->name('item.destroy');
    });

    // ############ Farmers #################
    Route::controller(FarmerController::class)->group(function () {
        Route::get('/farmers',  'index')->name('farmers.index');
        Route::get('/farmer-create',  'create')->name('farmer.create');
        Route::post('/farmer-store',  'store')->name('farmer.store');
        Route::get('/farmer-edit/{id}',  'edit')->name('farmer.edit');
        Route::post('/farmer-update/{id}',  'update')->name('farmer.update');
        Route::delete('/farmer-destroy/{id}',  'destroy')->name('farmer.destroy');
    });

    // ############ Ensured Crops For Farmer #################
    Route::controller(EnsuredCropController::class)->group(function () {
        Route::get('/ensured-crops',  'index')->name('ensured.crops.index');
        Route::post('/ensured-crops-store',  'store')->name('ensured.crops.store');
        Route::post('/ensured-crops-update/{id}',  'update')->name('ensured.crops.update');
        Route::delete('/ensured-crops-destroy/{id}',  'destroy')->name('ensured.crops.destroy');
    });

    // ############ Ensured Crops Name #################
    Route::controller(EnsuredCropNameController::class)->group(function () {
        // For Farmer
        Route::get('/ensured-crop-name',  'index')->name('ensured.crop.name.index');
        Route::post('/ensured-crops-name-store',  'store')->name('ensured.crop.name.store');
        Route::post('/ensured-crops-name-update/{id}',  'update')->name('ensured.crop.name.update');
        Route::delete('/ensured-crops-name-destroy/{id}',  'destroy')->name('ensured.crop.name.destroy');
    });

    // ############ Land Data Management #################
    Route::controller(LandDataManagementController::class)->group(function () {
        Route::get('/land-data-management',  'index')->name('land.index');

        Route::get('/get-insurance-types/{companyId}','getInsuranceTypes');
        Route::get('/get-insurance-subtypes/{typeId}','getInsuranceSubTypes');
    });

    // ############ Area Units #################
    Route::controller(AreaUnitController::class)->group(function () {
        Route::get('/units',  'index')->name('units.index');
        Route::post('/unit-store',  'store')->name('unit.store');
        Route::put('/unit-update/{id}',  'update')->name('unit.update');
        Route::delete('/unit-destroy/{id}',  'destroy')->name('unit.destroy');
    });

    // ############ union council #################
    Route::controller(UcController::class)->group(function () {
        Route::get('/union/{id}',  'index')->name('union.index');
        Route::post('/union-store',  'store')->name('union.store');
        Route::put('/union-update/{id}',  'update')->name('union.update');
        Route::delete('/union-destroy/{id}',  'destroy')->name('union.destroy');
    });

    // ############ village council #################
    Route::controller(VillageController::class)->group(function () {
        Route::get('/village/{id}',  'index')->name('village.index');
        Route::post('/village-store',  'store')->name('village.store');
        Route::put('/village-update/{id}',  'update')->name('village.update');
        Route::delete('/village-destroy/{id}',  'destroy')->name('village.destroy');
    });

    // ############ Tehsil #################
    Route::controller(TehsilController::class)->group(function () {
        Route::get('/tehsil/{id}',  'index')->name('tehsil.index');
        Route::post('/tehsil-store',  'store')->name('tehsil.store');
        Route::put('/tehsil-update/{id}',  'update')->name('tehsil.update');
        Route::delete('/tehsil-destroy/{id}',  'destroy')->name('tehsil.destroy');
    });

    // ############ District Management #################
    Route::controller(DistrictController::class)->group(function () {
        Route::post('/district-store',  'store')->name('district.store');
        Route::post('/district-update/{id}',  'update')->name('district.update');
        Route::delete('/district-destroy/{id}',  'destroy')->name('district.destroy');
        Route::get('/get-tehsils/{district_id}', 'getTehsils')->name('get.tehsils');
    });

    // ############ Insurance Company #################
    Route::controller(InsuranceCompanyController::class)->group(function () {
        Route::get('/insurance-company',  'index')->name('insurance.company.index');
        Route::post('/insurance-company-store',  'store')->name('insurance.company.store');
        Route::post('/insurance-company-update/{id}',  'update')->name('insurance.company.update');
        Route::delete('/insurance-company-destroy/{id}',  'destroy')->name('insurance.company.destroy');
    });

    // ############ Company Insurance Types #################
    Route::controller(CompanyInsuranceTypeController::class)->group(function () {
        Route::get('/company-insurance-types/{id}',  'index')->name('company.insurance.types.index');
        Route::post('/company-insurance-types-store',  'store')->name('company.insurance.types.store');
        Route::post('/company-insurance-types-update/{id}',  'update')->name('company.insurance.types.update');
        Route::delete('/company-insurance-types-destroy/{id}',  'destroy')->name('company.insurance.types.destroy');
    });
    
    // ############ Company Insurance Sub-Types #################
    Route::controller(CompanyInsuranceSubTypeController::class)->group(function () {
        Route::get('/company-insurance-sub-types/{id}',  'index')->name('company.insurance.sub.types.index');
        Route::post('/company-insurance-sub-types-store',  'store')->name('company.insurance.sub.types.store');
        Route::post('/company-insurance-sub-types-update/{id}',  'update')->name('company.insurance.sub.types.update');
        Route::delete('/company-insurance-sub-types-destroy/{id}',  'destroy')->name('company.insurance.sub.types.destroy');
    });

    // ############ Insurance Types #################
    Route::controller(InsuranceTypeController::class)->group(function () {
        Route::get('/insurance-type',  'index')->name('insurance.type.index');
        Route::post('/insurance-type-store',  'store')->name('insurance.type.store');
        Route::post('/insurance-type-update/{id}',  'update')->name('insurance.type.update');
        Route::delete('/insurance-type-destroy/{id}',  'destroy')->name('insurance.type.destroy');
    });

    // ############ Insurance Sub-Types #################
    Route::controller(InsuranceSubTypeController::class)->group(function () {
        Route::get('/insurance-sub-type/{id}',  'index')->name('insurance.sub.type.index');
        Route::post('/insurance-sub-type-store',  'store')->name('insurance.sub.type.store');
        Route::post('/insurance-sub-type-update/{id}',  'update')->name('insurance.sub.type.update');
        Route::delete('/insurance-sub-type-destroy/{id}',  'destroy')->name('insurance.sub.type.destroy');
        Route::get('/insurance-sub-type-production-price/{id}',  'production_price')->name('insurance.sub.type.productionPrice');
        Route::post('/insurance-sub-type-production-price-store',  'production_price_store')->name('insurance.sub.type.productionPrice.store');
        Route::post('/insurance-sub-type-production-price-update/{id}',  'production_price_update')->name('insurance.sub.type.productionPrice.update');
        Route::delete('/insurance-sub-type-production-price-destroy/{id}',  'production_price_destroy')->name('insurance.sub.type.productionPrice.destroy');
    });

    // ############ Insurance Claim Requests #################
    Route::controller(InsuranceClaimRequestController::class)->group(function () {
        Route::get('/insurance-claim',  'index')->name('insurance.claim.index');
        Route::get('/insurance-claim-create',  'create')->name('insurance.claim.create');
        Route::post('/insurance-claim-store',  'store')->name('insurance.claim.store');
        Route::get('/insurance-claim-edit/{id}',  'edit')->name('insurance.claim.edit');
        Route::post('/insurance-claim-update/{id}',  'update')->name('insurance.claim.update');
        Route::delete('/insurance-claim-destroy/{id}',  'destroy')->name('insurance.claim.destroy');
    });

    // ############ Notifications #################
    Route::controller(NotificationController::class)->group(function () {
        Route::get('/notification',  'index')->name('notification.index');
        Route::post('/notification-store',  'store')->name('notification.store');
        Route::delete('/notification-destroy/{id}',  'destroy')->name('notification.destroy');
    });
});
