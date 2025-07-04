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
use App\Http\Controllers\Admin\ContactUsController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\InsuranceHistoryController;
use App\Http\Controllers\Admin\WeatherController;
use App\Models\AboutUs;
use App\Models\Faq;
use App\Models\PrivacyPolicy;
use App\Models\TermCondition;

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

Route::get('/admin', [AuthController::class, 'getLoginPage'])->name('login');
Route::post('/login', [AuthController::class, 'Login']);
Route::get('/admin-forgot-password', [AdminController::class, 'forgetPassword']);
Route::post('/admin-reset-password-link', [AdminController::class, 'adminResetPasswordLink']);
Route::get('/change_password/{id}', [AdminController::class, 'change_password']);
Route::post('/admin-reset-password', [AdminController::class, 'ResetPassword']);

// webview links
    Route::get('/aboutUs', function () {
        $data = AboutUs::first();
        return view('aboutUs.aboutUs', compact('data'));
    }); 
    Route::get('/privacyPolicy', function () {
        $data = PrivacyPolicy::first();
        return view('privacyPolicy.privacy', compact('data'));
    });
    Route::get('/terms-conditions', function () {
        $data = TermCondition::first();
        return view('terms_and_condition.termsConditions', compact('data'));
    });
    Route::get('/faqs', function () {
        $data = Faq::first();
        return view('faqs.faq', compact('data'));
    });

Route::prefix('admin')->middleware('admin')->group(function () {
    Route::get('dashboard', [AdminController::class, 'getdashboard']);
    Route::get('profile', [AdminController::class, 'getProfile']);
    Route::post('update-profile', [AdminController::class, 'update_profile']);

    // ############ Privacy-policy #################
    Route::get('privacy-policy', [SecurityController::class, 'PrivacyPolicy'])->name('privacy.policy');
    Route::get('privacy-policy-edit', [SecurityController::class, 'PrivacyPolicyEdit']);
    Route::post('privacy-policy-update', [SecurityController::class, 'PrivacyPolicyUpdate']);

    // ############ Term & Condition #################
    Route::get('term-condition', [SecurityController::class, 'TermCondition'])->name('term.condition');
    Route::get('term-condition-edit', [SecurityController::class, 'TermConditionEdit']);
    Route::post('term-condition-update', [SecurityController::class, 'TermConditionUpdate']);

    // ############ About Us #################
    Route::get('about-us', [SecurityController::class, 'AboutUs'])->name('about.us');
    Route::get('about-us-edit', [SecurityController::class, 'AboutUsEdit']);
    Route::post('about-us-update', [SecurityController::class, 'AboutUsUpdate']);

    Route::get('logout', [AdminController::class, 'logout']);

    // ############ Sub Admin #################
    Route::controller(SubAdminController::class)->group(function () {
        Route::get('/subadmin',  'index')->name('subadmin.index')->middleware('check.subadmin.permission:Sub Admins,view');
        Route::get('/subadmin-create',  'create')->name('subadmin.create')->middleware('check.subadmin.permission:Sub Admins,create');
        Route::post('/subadmin-store',  'store')->name('subadmin.store')->middleware('check.subadmin.permission:SubAdmin,create');
        Route::get('/subadmin-edit/{id}',  'edit')->name('subadmin.edit')->middleware('check.subadmin.permission:SubAdmin,edit');
        Route::post('/subadmin-update/{id}',  'update')->name('subadmin.update')->middleware('check.subadmin.permission:SubAdmin,edit');
        Route::delete('/subadmin-destroy/{id}',  'destroy')->name('subadmin.destroy')->middleware('check.subadmin.permission:SubAdmin,delete');
        Route::post('/update-permissions/{id}', 'updatePermissions')->name('update.permissions');
        Route::post('/subadmin-StatusChange', 'StatusChange')->name('subadmin.StatusChange');
    });

    // ############ Authorized Dealers #################
    Route::controller(AuthorizedDealerController::class)->group(function () {
        Route::get('/dealer',  'index')->name('dealer.index')->middleware('check.subadmin.permission:Authorized Dealers,view');
        Route::get('/dealer-create',  'create')->name('dealer.create')->middleware('check.subadmin.permission:Authorized Dealers,create');
        Route::post('/dealer-store',  'store')->name('dealer.store')->middleware('check.subadmin.permission:Authorized Dealers,create');
        Route::get('/dealer-edit/{id}',  'edit')->name('dealer.edit')->middleware('check.subadmin.permission:Authorized Dealers,edit');
        Route::post('/dealer-update/{id}',  'update')->name('dealer.update')->middleware('check.subadmin.permission:Authorized Dealers,edit');
        Route::delete('/dealer-destroy/{id}',  'destroy')->name('dealer.destroy')->middleware('check.subadmin.permission:Authorized Dealers,delete');
    });

    // ############ Dealer Items #################
    Route::controller(DealerItemController::class)->group(function () {
        Route::get('/dealer-items/{id}',  'index')->name('dealer.item.index')->middleware('check.subadmin.permission:Dealer Items,view');
        Route::get('/dealer-item-create/{id}',  'create')->name('dealer.item.create')->middleware('check.subadmin.permission:Dealer Items,create');
        Route::post('/dealer-item-store',  'store')->name('dealer.item.store')->middleware('check.subadmin.permission:Dealer Items,create');
        Route::get('/dealer-item-edit/{dealer_id}/{item_id}',  'edit')->name('dealer.item.edit')->middleware('check.subadmin.permission:Dealer Items,edit');
        Route::post('/dealer-item-update/{id}',  'update')->name('dealer.item.update')->middleware('check.subadmin.permission:Dealer Items,edit');
        Route::delete('/dealer-item-destroy/{id}',  'destroy')->name('dealer.item.destroy')->middleware('check.subadmin.permission:Dealer Items,delete');
    });

    // ############ Items for Dealers Selection #################
    Route::controller(ItemController::class)->group(function () {
        Route::get('/items',  'index')->name('items.index')->middleware('check.subadmin.permission:Dealer Items,view');
        Route::post('/item-store',  'store')->name('item.store')->middleware('check.subadmin.permission:Dealer Items,create');
        Route::post('/item-update/{id}',  'update')->name('item.update')->middleware('check.subadmin.permission:Dealer Items,edit');
        Route::delete('/item-destroy/{id}',  'destroy')->name('item.destroy')->middleware('check.subadmin.permission:Dealer Items,delete');
    });

    // ############ Farmers #################
    Route::controller(FarmerController::class)->group(function () {
        Route::get('/farmers',  'index')->name('farmers.index')->middleware('check.subadmin.permission:Farmers,view');
        Route::get('/farmer-create',  'create')->name('farmer.create')->middleware('check.subadmin.permission:Farmers,create');
        Route::post('/farmer-store',  'store')->name('farmer.store')->middleware('check.subadmin.permission:Farmers,create');
        Route::get('/farmer-edit/{id}',  'edit')->name('farmer.edit')->middleware('check.subadmin.permission:Farmers,edit');
        Route::post('/farmer-update/{id}',  'update')->name('farmer.update')->middleware('check.subadmin.permission:Farmers,edit');
        Route::delete('/farmer-destroy/{id}',  'destroy')->name('farmer.destroy')->middleware('check.subadmin.permission:Farmers,delete');
    });

    // ############ Insurance History #################
    Route::controller(InsuranceHistoryController::class)->group(function () {
        Route::get('/insurance-history',  'index')->name('insurance.history.index')->middleware('check.subadmin.permission:Insurance History,view');
        Route::delete('/ensured-crops-destroy/{id}',  'destroy')->name('insurance-history.destroy')->middleware('check.subadmin.permission:Insurance History,delete');
    });

    // ############ Ensured Crops Name #################
    Route::controller(EnsuredCropNameController::class)->group(function () {
        // For Farmer
        Route::get('/ensured-crop-name',  'index')->name('ensured.crop.name.index')->middleware('check.subadmin.permission:Insured Crops,view');
        Route::post('/ensured-crops-name-store',  'store')->name('ensured.crop.name.store')->middleware('check.subadmin.permission:Insured Crops,create');
        Route::post('/ensured-crops-name-update/{id}',  'update')->name('ensured.crop.name.update')->middleware('check.subadmin.permission:Insured Crops,edit');
        Route::delete('/ensured-crops-name-destroy/{id}',  'destroy')->name('ensured.crop.name.destroy')->middleware('check.subadmin.permission:Insured Crops,delete');
    });

    // ############ Land Data Management #################
    Route::controller(LandDataManagementController::class)->group(function () {
        Route::get('/land-data-management',  'index')->name('land.index')->middleware('check.subadmin.permission:Land Data Management,view');
        Route::get('/get-insurance-types/{companyId}','getInsuranceTypes')->middleware('check.subadmin.permission:Land Data Management,view');
        Route::get('/get-insurance-subtypes/{typeId}','getInsuranceSubTypes')->middleware('check.subadmin.permission:Land Data Management,view');
    });

    // ############ Area Units #################
    Route::controller(AreaUnitController::class)->group(function () {
        Route::get('/units',  'index')->name('units.index')->middleware('check.subadmin.permission:Land Data Management,view');
        Route::post('/unit-store',  'store')->name('unit.store')->middleware('check.subadmin.permission:Land Data Management,create');
        Route::put('/unit-update/{id}',  'update')->name('unit.update')->middleware('check.subadmin.permission:Land Data Management,edit');
        Route::delete('/unit-destroy/{id}',  'destroy')->name('unit.destroy')->middleware('check.subadmin.permission:Land Data Management,delete');
    });

    // ############ union council #################
    Route::controller(UcController::class)->group(function () {
        Route::get('/union/{id}',  'index')->name('union.index')->middleware('check.subadmin.permission:Land Data Management,view');
        Route::post('/union-store',  'store')->name('union.store')->middleware('check.subadmin.permission:Land Data Management,create');
        Route::put('/union-update/{id}',  'update')->name('union.update')->middleware('check.subadmin.permission:Land Data Management,edit');
        Route::delete('/union-destroy/{id}',  'destroy')->name('union.destroy')->middleware('check.subadmin.permission:Land Data Management,delete');
    });

    // ############ village council #################
    Route::controller(VillageController::class)->group(function () {
        Route::get('/village/{id}',  'index')->name('village.index')->middleware('check.subadmin.permission:Land Data Management,view');
        Route::post('/village-store',  'store')->name('village.store')->middleware('check.subadmin.permission:Land Data Management,create');
        Route::put('/village-update/{id}',  'update')->name('village.update')->middleware('check.subadmin.permission:Land Data Management,edit');
        Route::delete('/village-destroy/{id}',  'destroy')->name('village.destroy')->middleware('check.subadmin.permission:Land Data Management,delete');
    });

    // ############ Tehsil #################
    Route::controller(TehsilController::class)->group(function () {
        Route::get('/tehsil/{id}',  'index')->name('tehsil.index')->middleware('check.subadmin.permission:Land Data Management,view');
        Route::post('/tehsil-store',  'store')->name('tehsil.store')->middleware('check.subadmin.permission:Land Data Management,create');
        Route::put('/tehsil-update/{id}',  'update')->name('tehsil.update')->middleware('check.subadmin.permission:Land Data Management,edit');
        Route::delete('/tehsil-destroy/{id}',  'destroy')->name('tehsil.destroy')->middleware('check.subadmin.permission:Land Data Management,delete');
    });

    // ############ District Management #################
    Route::controller(DistrictController::class)->group(function () {
        Route::post('/district-store',  'store')->name('district.store')->middleware('check.subadmin.permission:Land Data Management,view');
        Route::post('/district-update/{id}',  'update')->name('district.update')->middleware('check.subadmin.permission:Land Data Management,create');
        Route::delete('/district-destroy/{id}',  'destroy')->name('district.destroy')->middleware('check.subadmin.permission:Land Data Management,delete');
        Route::get('/get-tehsils/{district_id}', 'getTehsils')->name('get.tehsils');
    });

    // ############ Insurance Company #################
    Route::controller(InsuranceCompanyController::class)->group(function () {
        Route::get('/insurance-company',  'index')->name('insurance.company.index')->middleware('check.subadmin.permission:Insurance Companies,view');
        Route::post('/insurance-company-store',  'store')->name('insurance.company.store')->middleware('check.subadmin.permission:Insurance Companies,create');
        Route::post('/insurance-company-update/{id}',  'update')->name('insurance.company.update')->middleware('check.subadmin.permission:Insurance Companies,edit');
        Route::delete('/insurance-company-destroy/{id}',  'destroy')->name('insurance.company.destroy')->middleware('check.subadmin.permission:Insurance Companies,delete');
    });

    // ############ Company Insurance Types #################
    Route::controller(CompanyInsuranceTypeController::class)->group(function () {
        Route::get('/company-insurance-types/{id}',  'index')->name('company.insurance.types.index')->middleware('check.subadmin.permission:Insurance Companies,view');
        Route::post('/company-insurance-types-store',  'store')->name('company.insurance.types.store')->middleware('check.subadmin.permission:Insurance Companies,create');
        Route::post('/company-insurance-types-update/{id}',  'update')->name('company.insurance.types.update')->middleware('check.subadmin.permission:Insurance Companies,edit');
        Route::delete('/company-insurance-types-destroy/{id}',  'destroy')->name('company.insurance.types.destroy')->middleware('check.subadmin.permission:Insurance Companies,delete');
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
        Route::get('/insurance-type',  'index')->name('insurance.type.index')->middleware('check.subadmin.permission:Insurance Types,view');
        Route::post('/insurance-type-store',  'store')->name('insurance.type.store')->middleware('check.subadmin.permission:Insurance Types,create');
        Route::post('/insurance-type-update/{id}',  'update')->name('insurance.type.update')->middleware('check.subadmin.permission:Insurance Types,edit');
        Route::delete('/insurance-type-destroy/{id}',  'destroy')->name('insurance.type.destroy')->middleware('check.subadmin.permission:Insurance Types,delete');
    });

    // ############ Insurance Sub-Types #################
    Route::controller(InsuranceSubTypeController::class)->group(function () {
        Route::get('/insurance-sub-type/{id}',  'index')->name('insurance.sub.type.index')->middleware('check.subadmin.permission:Insurance Types,view');
        Route::post('/insurance-sub-type-store',  'store')->name('insurance.sub.type.store')->middleware('check.subadmin.permission:Insurance Types,create');
        Route::post('/insurance-sub-type-update/{id}',  'update')->name('insurance.sub.type.update')->middleware('check.subadmin.permission:Insurance Types,edit');
        Route::delete('/insurance-sub-type-destroy/{id}',  'destroy')->name('insurance.sub.type.destroy')->middleware('check.subadmin.permission:Insurance Types,delete');
        Route::get('/insurance-sub-type-production-price/{id}',  'production_price')->name('insurance.sub.type.productionPrice')->middleware('check.subadmin.permission:Insurance Types,view');
        Route::post('/insurance-sub-type-production-price-store',  'production_price_store')->name('insurance.sub.type.productionPrice.store')->middleware('check.subadmin.permission:Insurance Types,create');
        Route::post('/insurance-sub-type-production-price-update/{id}',  'production_price_update')->name('insurance.sub.type.productionPrice.update')->middleware('check.subadmin.permission:Insurance Types,edit');
        Route::delete('/insurance-sub-type-production-price-destroy/{id}',  'production_price_destroy')->name('insurance.sub.type.productionPrice.destroy')->middleware('check.subadmin.permission:Insurance Types,delete');
        Route::get('/insurance-sub-type-satellite_ndvi/{id}',  'satellite_ndvi')->name('insurance.sub.type.satelliteNDVI')->middleware('check.subadmin.permission:Insurance Types,view');
        Route::post('/insurance-sub-type-satellite_ndvi-store',  'satellite_ndvi_store')->name('insurance.sub.type.satelliteNDVI.store')->middleware('check.subadmin.permission:Insurance Types,create');
        Route::post('/insurance-sub-type-satellite_ndvi-update/{id}',  'satellite_ndvi_update')->name('insurance.sub.type.satelliteNDVI.update')->middleware('check.subadmin.permission:Insurance Types,edit');
        Route::delete('/insurance-sub-type-satellite_ndvi-destroy/{id}',  'satellite_ndvi_destroy')->name('insurance.sub.type.satelliteNDVI.destroy')->middleware('check.subadmin.permission:Insurance Types,delete');
        Route::get('/insurance-sub-type-weather/{id}',  'weather_index')->name('insurance.sub.type.weatherIndex')->middleware('check.subadmin.permission:Insurance Types,view');
    });
    Route::get('/insurance/result/{id}', [InsuranceSubTypeController::class, 'showVillageResult'])->name('admin.insurance.result');

    Route::get('/insurance-sub-type/{id}/calculate', [InsuranceSubTypeController::class, 'calculateResult'])
    ->name('insurance.sub.type.calculate');
    Route::get('/get-ndvi-data', [InsuranceSubTypeController::class, 'fetchNDVIData'])->name('ndvi.fetch');
    
    // weather api routes 
    Route::get('/weather/fetch/{villageId}', [WeatherController::class, 'fetchLast14DaysWeather'])
        ->name('weather.fetch.14days');


    // ############ Insurance Claim Requests #################
    Route::controller(InsuranceClaimRequestController::class)->group(function () {
        Route::get('/insurance-claim',  'index')->name('insurance.claim.index')->middleware('check.subadmin.permission:Insurance Claim Requests,view');
        Route::delete('/insurance-claim-destroy/{id}',  'destroy')->name('insurance.claim.destroy')->middleware('check.subadmin.permission:Insurance Claim Requests,delete');
       
        Route::post('/insurance-claim/approve/{id}', 'approve')->name('insurance.claim.approve')->middleware('check.subadmin.permission:Insurance Claim Requests,view');
        Route::post('/insurance-claim/reject/{id}', 'reject')->name('insurance.claim.reject')->middleware('check.subadmin.permission:Insurance Claim Requests,view');
        // buy products
        Route::get('/insurance-product-claims', 'buyProduct')->name('insurance.product.claims.index')->middleware('check.subadmin.permission:Claim Product Purchase,view');
        Route::post('/product-claim/approve/{id}', 'approveProduct')->name('product.claim.approve')->middleware('check.subadmin.permission:Insurance Claim Requests,view');
        Route::post('/product-claim/reject/{id}', 'rejectProduct')->name('product.claim.reject')->middleware('check.subadmin.permission:Insurance Claim Requests,view');
    });

    
    // ############ Notifications #################
    Route::controller(NotificationController::class)->group(function () {
        // Route::get('/notification',  'index')->name('notification.index')->middleware('check.subadmin.permission:Notifications,view');
        // Route::post('/notification-store',  'store')->name('notification.store')->middleware('check.subadmin.permission:Notifications,create');
        // Route::delete('/notification-destroy/{id}',  'destroy')->name('notification.destroy')->middleware('check.subadmin.permission:Notifications,delete');
        Route::get('/notification',  'index')->name('notification.index');
        Route::post('/notification-store',  'store')->name('notification.store');
        Route::get('/notification-edit/{id}',  'edit')->name('notification.edit');
        Route::post('/notification-update/{id}',  'update')->name('notification.update');
        Route::delete('/notification-destroy/{id}',  'destroy')->name('notification.destroy');
        Route::delete('admin/notification/delete-all', 'deleteAll')->name('notification.deleteAll');

    });


    //contact controller
    Route::get('/contact-us', [ContactUsController::class, 'index'])->name('contact.index');
    Route::get('/contact-us-create', [ContactUsController::class, 'create'])->name('contact.create');
    Route::post('/contact-us-store', [ContactUsController::class, 'store'])->name('contact.store');
    Route::get('/contact-us-edit/{id}', [ContactUsController::class, 'updateview'])->name('contact.updateview');
    Route::post('/contact-us-update/{id}', [ContactUsController::class, 'update'])->name('contact.update');

      // ############ Faq #################
    Route::get('faqs', [FaqController::class, 'Faq']);
    Route::get('faq-edit/{id}', [FaqController::class, 'FaqsEdit'])->name('faq.edit');
    Route::post('faq-update/{id}', [FaqController::class, 'FaqsUpdate']);
    Route::get('faq-view', [FaqController::class, 'FaqView']);
    Route::get('faq-create', [FaqController::class, 'Faqscreateview']);
    Route::post('faq-store', [FaqController::class, 'Faqsstore']);
    Route::delete('faq-destroy/{id}', [FaqController::class, 'faqdelete'])->name('faq.destroy');
    Route::post('/faqs/reorder', [FaqController::class, 'reorder'])->name('faq.reorder');
});
