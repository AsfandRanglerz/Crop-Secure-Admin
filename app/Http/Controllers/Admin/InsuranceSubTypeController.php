<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AreaYieldNotificationHelper;
use App\Helpers\NDVINotificationHelper;
use App\Models\Tehsil;
use App\Models\District;
use Illuminate\Http\Request;
use App\Models\InsuranceType;
use App\Models\EnsuredCropName;
use App\Models\InsuranceSubType;
use App\Http\Controllers\Controller;
use App\Models\CropInsurance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use App\Notifications\InsuranceYieldUpdated;
use App\Helpers\NotificationHelper;
use App\Helpers\ProductionPriceNotificationHelper;
use App\Helpers\WeatherNotificationHelper;
use App\Models\InsuranceHistory;
use App\Models\InsuranceSubTypeSatelliteNDVI;
use App\Models\Village;
use App\Models\VillageWeatherDailySummary;
use App\Models\VillageWeatherHistory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InsuranceSubTypeController extends Controller
{
    // public function index($id)
    // {
    //     $sideMenuName = [];
    //     $sideMenuPermissions = [];

    //     if (Auth::guard('subadmin')->check()) {
    //         $getSubAdminPermissions = new AdminController();
    //         $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
    //         $sideMenuName = $subAdminData['sideMenuName'];
    //         $sideMenuPermissions = $subAdminData['sideMenuPermissions'];
    //     }
    //     $ensuredCrops = EnsuredCropName::all();  // Fetch all crops
    //     $districts = District::all();         // Fetch all districts
    //     $tehsils = Tehsil::all();             // Fetch all tehsils
    //     $InsuranceType = InsuranceType::find($id);
    //     $InsuranceSubTypes = InsuranceSubType::with(['district', 'tehsil'])->where('incurance_type_id', $id)->orderBy('status', 'desc')->latest()->get();

    //     return view('admin.insurance_types_and_sub_types.sub_types', compact('sideMenuPermissions', 'sideMenuName', 'InsuranceSubTypes', 'InsuranceType', 'ensuredCrops', 'districts', 'tehsils'));
    // }

    public function index($id)
    {
        $sideMenuName = [];
        $sideMenuPermissions = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
            $sideMenuPermissions = $subAdminData['sideMenuPermissions'];
        }

        $ensuredCrops = EnsuredCropName::all();
        $districts = District::all();
        $tehsils = Tehsil::all();
        $InsuranceType = InsuranceType::find($id);
        $InsuranceSubTypes = InsuranceSubType::with(['district', 'tehsil'])
            ->where('incurance_type_id', $id)
            ->orderBy('status', 'desc')
            ->latest()
            ->get();

        return view('admin.insurance_types_and_sub_types.sub_types', compact(
            'sideMenuPermissions',
            'sideMenuName',
            'InsuranceType',
            'InsuranceSubTypes',
            'ensuredCrops',
            'districts',
            'tehsils'
        ));
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'district_id' => 'required',
            'tehsil_id' => 'required',
            'current_yield' => 'required|numeric',
            'year' => 'required|integer',
        ]);

        $exists = InsuranceSubType::where('name', $request->name)
            ->where('district_id', $request->district_id)
            ->where('tehsil_id', $request->tehsil_id)
            ->where('year', $request->year)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withErrors(['duplicate' => 'This crop already exists for the selected district, tehsil, and year.'])
                ->withInput();
        }

        $insuranceSubType = InsuranceSubType::create([
            'incurance_type_id' => $request->incurance_type_id,
            'name' => $request->name,
            'district_id' => $request->district_id,
            'tehsil_id' => $request->tehsil_id,
            'current_yield' => $request->current_yield,
            'year' => $request->year,
        ]);

        $farmers = InsuranceHistory::with('user')
            ->where('insurance_type_id', $request->incurance_type_id)
            ->where('crop', $request->name)
            ->where('district_id', $request->district_id)
            ->where('tehsil_id', $request->tehsil_id)
            ->whereYear('created_at', $request->year)
            ->get();

        foreach ($farmers as $record) {
            $user = $record->user;
            // dd($user);
            $benchmark = $record->benchmark;
            $sumInsured = $record->sum_insured;
            $loss = $benchmark - $request->current_yield;

            $comp = $loss > 0 ? ($loss / 100) * $sumInsured : 0;

            $record->update([
                'compensation_amount' => round($comp, 2),
                'remaining_amount' => round($comp, 2),
            ]);

            if ($user && $user->fcm_token) {
                AreaYieldNotificationHelper::notifyFarmer(
                    $user,
                    $request->name,
                    $request->year,
                    $request->current_yield
                );
            }
        }



        return redirect()
            ->route('insurance.sub.type.index', ['id' => $request->incurance_type_id])
            ->with(['message' => 'Insurance Result Announced Successfully']);
    }


    public function update(Request $request, $id)
    {
        // dd($request);

        $request->validate([
            'name' => 'required',
            'district_id' => 'required',
            'tehsil_id' => 'required',
            'current_yield' => 'required|numeric',
            'year' => 'required|integer',
        ]);

        $data = InsuranceSubType::findOrFail($id);

        // Check for duplicate before updating
        $exists = InsuranceSubType::where('id', '!=', $id)
            ->where('name', $request->name)
            ->where('district_id', $request->district_id)
            ->where('tehsil_id', $request->tehsil_id)
            ->where('year', $request->year)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withErrors(['duplicate' => 'Another record with the same crop, district and tehsil already exists.'])
                ->withInput();
        }

        $data->update([
            'name' => $request->name,
            'district_id' => $request->district_id,
            'tehsil_id' => $request->tehsil_id,
            'current_yield' => $request->current_yield,
            'year' => $request->year,
            // 'status' => $request->status,
        ]);

        return redirect()->route('insurance.sub.type.index', ['id' => $request->incurance_type_id])
            ->with(['message' => 'Insurance Result Updated Successfully']);
    }



    public function destroy(Request $request, $id)
    {
        // dd($request);
        try {
            InsuranceSubType::destroy($id);
            return redirect()->route('insurance.sub.type.index', ['id' => $request->incurance_type_id])->with(['message' => 'Insurance Sub-Type Deleted Successfully']);
        } catch (QueryException $e) {
            return redirect()->route('insurance.sub.type.index', ['id' => $request->incurance_type_id])->with(['error' => 'This insurance Sub-type cannot be deleted because it is assigned to insurance companies.']);
        }
    }

    public function production_price($id)
    {
        $sideMenuName = [];
        $sideMenuPermissions = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
            $sideMenuPermissions = $subAdminData['sideMenuPermissions'];
        }
        $ensuredCrops = EnsuredCropName::all();
        $districts = District::all();
        $tehsils = Tehsil::all();
        $InsuranceType = InsuranceType::find($id);
        $InsuranceSubTypes = InsuranceSubType::with(['district', 'tehsil', 'crop'])
            ->where('incurance_type_id', $id)
            ->orderBy('status', 'desc')
            ->latest()
            ->get();

        return view('admin.insurance_types_and_sub_types.sub_types_production_price', compact('sideMenuPermissions', 'sideMenuName', 'InsuranceSubTypes', 'InsuranceType', 'ensuredCrops', 'districts', 'tehsils'));
    }


    public function production_price_store(Request $request)
    {
        $request->validate([
            'incurance_type_id' => 'required|integer',
            'crop_name_id' => 'required|integer',
            'year' => 'required|integer',
            'crops' => 'required|array',
            'crops.*.district_id' => 'required|integer',
            'crops.*.tehsil_id' => 'required|integer',
        ]);

        foreach ($request->crops as $index => $crop) {
            // ✅ Step 0: Check if record already exists
            $exists = InsuranceSubType::where('crop_name_id', $request->crop_name_id)
                ->where('incurance_type_id', $request->incurance_type_id)
                ->where('district_id', $crop['district_id'])
                ->where('tehsil_id', $crop['tehsil_id'])
                ->where('year', $request->year)
                ->exists();

            if ($exists) {
                return redirect()->back()
                    ->withErrors(['duplicate' => "This crop already exists for the selected district, tehsil, and year"])
                    ->withInput();
            }

            // ✅ Step 1: Store production price
            $subType = InsuranceSubType::create([
                'crop_name_id' => $request->crop_name_id,
                'incurance_type_id' => $request->incurance_type_id,
                'district_id' => $crop['district_id'],
                'tehsil_id' => $crop['tehsil_id'],
                'cost_of_production' => $crop['cost_of_production'] ?? null,
                'average_yield' => $crop['average_yield'] ?? null,
                'historical_average_market_price' => $crop['historical_average_market_price'] ?? null,
                'real_time_market_price' => $crop['real_time_market_price'] ?? null,
                'ensured_yield' => $crop['ensured_yield'] ?? null,
                'year' => $request->year,
            ]);

            // ✅ Step 2: Get relevant farmers
            $farmers = InsuranceHistory::with('user')
                ->where('insurance_type_id', $request->incurance_type_id)
                ->where('crop_id', $request->crop_name_id)
                ->where('district_id', $crop['district_id'])
                ->where('tehsil_id', $crop['tehsil_id'])
                ->whereYear('created_at', $request->year)
                ->get();


            // ✅ Step 3: Notify farmers
            // Step 3: Notify farmers & calculate compensation
            foreach ($farmers as $record) {
                $user = $record->user;

                if (!$user) continue;

                $comp = 0;
                if (
                    $subType->cost_of_production !== null &&
                    $subType->average_yield !== null &&
                    $subType->real_time_market_price !== null &&
                    $subType->ensured_yield !== null &&
                    $record->benchmark !== null
                ) {
                    $bep = $subType->cost_of_production / $subType->average_yield;
                    $triggerPrice = $record->benchmark;
                    $marketPrice = $subType->real_time_market_price;

                    if ($marketPrice < $triggerPrice) {
                        $comp = $subType->ensured_yield * ($triggerPrice - $marketPrice) * $record->area;
                    }
                }

                // Save compensation and remaining amount in InsuranceHistory
                $record->update([
                    'compensation_amount' => round($comp, 2),
                    'remaining_amount' => round($comp, 2),
                ]);

                // Send FCM Notification
                if ($user->fcm_token) {
                    ProductionPriceNotificationHelper::notifyFarmer(
                        $user,
                        $request->year,
                        $request->incurance_type_id,
                        $crop['district_id'],
                        $crop['tehsil_id']
                    );
                }
            }
        }

        return redirect()->route('insurance.sub.type.productionPrice', ['id' => $request->incurance_type_id])
            ->with(['message' => 'Insurance Result Announced Successfully']);
    }


    public function production_price_update(Request $request, $id)
    {
        $request->validate([
            'crop_name_id' => 'required|exists:insurance_types,id',
            'crops.0.district_id' => 'required|exists:districts,id',
            'crops.0.tehsil_id' => 'required|exists:tehsils,id',
            'cost_of_production' => 'nullable|numeric',
        ]);

        // Check for duplicate
        $duplicateExists = InsuranceSubType::where('crop_name_id', $request->crop_name_id)
            ->where('district_id', $request->input('crops.0.district_id'))
            ->where('tehsil_id', $request->input('crops.0.tehsil_id'))
            ->where('year', $request->input('year'))
            ->where('id', '!=', $id)
            ->exists();

        if ($duplicateExists) {
            return redirect()->back()
                ->withErrors(['duplicate' => 'This crop already exists for the selected district, tehsil, and year.'])
                ->withInput();
        }

        $insuranceSubType = InsuranceSubType::findOrFail($id);

        $insuranceSubType->crop_name_id = $request->input('crop_name_id');
        $insuranceSubType->district_id = $request->input('crops.0.district_id');
        $insuranceSubType->tehsil_id = $request->input('crops.0.tehsil_id');
        $insuranceSubType->cost_of_production = $request->input('cost_of_production');
        $insuranceSubType->average_yield = $request->input('average_yield');
        $insuranceSubType->historical_average_market_price = $request->input('historical_average_market_price');
        $insuranceSubType->real_time_market_price = $request->input('real_time_market_price');
        $insuranceSubType->ensured_yield = $request->input('ensured_yield');
        $insuranceSubType->year = $request->input('year');

        $insuranceSubType->save();

        return redirect()
            ->route('insurance.sub.type.productionPrice', ['id' => $request->incurance_type_id])
            ->with(['message' => 'Insurance Result Updated Successfully']);
    }


    public function production_price_destroy(Request $request, $id)
    {
        // dd($request->toArray());
        try {
            InsuranceSubType::destroy($id);
            return redirect()->route('insurance.sub.type.productionPrice', ['id' => $request->incurance_type_id])->with(['message' => 'Insurance Sub-Type Deleted Successfully']);
        } catch (QueryException $e) {
            return redirect()->route('insurance.sub.type.productionPrice', ['id' => $request->incurance_type_id])->with(['error' => 'This insurance Sub-type cannot be deleted because it is assigned to insurance companies.']);
        }
    }


    public function satellite_ndvi($id)
    {
        $sideMenuName = [];
        $sideMenuPermissions = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
            $sideMenuPermissions = $subAdminData['sideMenuPermissions'];
        }

        $ensuredCrops = EnsuredCropName::all();
        $districts = District::all();
        $tehsils = Tehsil::all();
        $InsuranceType = InsuranceType::find($id);
        $villages = Village::with('uc.tehsil.district')->get();

        // 🟢 Fetch NDVI records, not subtypes
        $InsuranceSubTypes = InsuranceSubTypeSatelliteNDVI::with('village')->where('insurance_type_id', $id)
            ->orderBy('date', 'desc')
            ->get();

        return view('admin.insurance_types_and_sub_types.sub_types_satelliteNDVI', compact(
            'sideMenuPermissions',
            'sideMenuName',
            'InsuranceSubTypes',
            'InsuranceType',
            'ensuredCrops',
            'districts',
            'tehsils',
            'villages'
        ));
    }


    // public function fetchNDVIData(Request $request)
    // {
    //     // Log initial request input
    //     Log::info('NDVI fetch request received', $request->all());

    //     // Validate input
    //     $request->validate([
    //         'date' => 'required|date',
    //     ]);

    //     try {
    //         $apiKey = 'apk.ec114200944764f1f5162bf2efc7cd4ccb9afb90efaa35594cf3058b0244d6da';
    //         $apiUrl = 'https://api-connect.eos.com/user-dashboard/statistics';

    //         // Send API request
    //         $response = Http::withHeaders([
    //             'Authorization' => 'Bearer ' . $apiKey,
    //             'Accept' => 'application/json',
    //         ])->get($apiUrl, [
    //             'date' => $request->date,
    //         ]);

    //         // Log raw response body
    //         Log::info('EOS NDVI raw response', [
    //             'status' => $response->status(),
    //             'body' => $response->body(),
    //         ]);

    //         // If successful, process data
    //         if ($response->successful()) {
    //             $data = $response->json();

    //             $b8 = isset($data['B8']) ? floatval($data['B8']) : 0.0;
    //             $b4 = isset($data['B4']) ? floatval($data['B4']) : 0.0;

    //             // Log band values
    //             Log::info('NDVI band values', [
    //                 'B8' => $b8,
    //                 'B4' => $b4,
    //                 'B8 + B4' => $b8 + $b4,
    //             ]);

    //             // Prevent division by zero
    //             if (($b8 + $b4) == 0.0) {
    //                 Log::warning('NDVI calculation skipped due to division by zero', [
    //                     'B8' => $b8,
    //                     'B4' => $b4
    //                 ]);

    //                 return response()->json([
    //                     'error' => 'Invalid B8/B4 values (division by zero)',
    //                     'b8' => $b8,
    //                     'b4' => $b4,
    //                     'ndvi' => null
    //                 ], 422);
    //             }

    //             $ndvi = ($b8 - $b4) / ($b8 + $b4);

    //             return response()->json([
    //                 'b8' => $b8,
    //                 'b4' => $b4,
    //                 'ndvi' => round($ndvi, 4)
    //             ]);
    //         }

    //         // Handle unsuccessful EOS API response
    //         Log::error('EOS API request failed', [
    //             'status' => $response->status(),
    //             'body' => $response->body()
    //         ]);

    //         return response()->json(['error' => 'Data not found or EOS request failed'], $response->status());
    //     } catch (\Exception $e) {
    //         // Log internal error
    //         Log::error('NDVI API Exception', [
    //             'message' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         return response()->json(['error' => 'Internal server error'], 500);
    //     }
    // }

    // Manually store a record from the modal
    public function satellite_ndvi_store(Request $request)
    {
        // Step 0: Validate input
        $validated = $request->validate([
            'date' => 'required|date',
            'village_id' => 'required|exists:villages,id',
            'b8' => 'required|numeric|min:0|max:999999999999999',
            'b4' => 'required|numeric|min:0|max:999999999999999',
            'ndvi' => 'required|numeric',
            'incurance_type_id' => 'nullable|exists:insurance_types,id',
        ]);

        // Step 1: Check for duplicate NDVI record
        $alreadyExists = InsuranceSubTypeSatelliteNDVI::where('date', $request->date)
            ->where('village_id', $request->village_id)
            ->where('insurance_type_id', $request->incurance_type_id)
            ->exists();

        if ($alreadyExists) {
            return back()
                ->withErrors(['duplicate' => 'NDVI for the same date already exists.'])
                ->withInput();
        }

        // Step 2: Create NDVI record
        $ndviRecord = InsuranceSubTypeSatelliteNDVI::create([
            'date' => $request->date,
            'village_id' => $request->village_id,
            'b8' => $request->b8,
            'b4' => $request->b4,
            'ndvi' => $request->ndvi,
            'insurance_type_id' => $request->incurance_type_id,
        ]);

        // Step 3: Get Farmer IDs from Crop Insurance for this village
        $farmerIds = CropInsurance::where('village_id', $request->village_id)
            ->pluck('user_id')
            ->toArray();

        if (empty($farmerIds)) {
            return back()->withErrors('info', 'No farmers found in this village.')->withInput();
        }

        // Step 4: Fetch all affected insurance histories for those farmers
        $farmers = InsuranceHistory::with('user')
            ->where('insurance_type_id', $request->incurance_type_id)
            ->where('status', 'unclaimed')
            ->whereIn('user_id', $farmerIds)
            ->get();

        // Step 5: Threshold check and Compensation + Notification
        $threshold = 0.4;

        foreach ($farmers as $record) {
            $user = $record->user;
            $isLoss = $ndviRecord->ndvi < $threshold;
            $comp = $isLoss ? $record->sum_insured : 0;

            $record->update([
                'compensation_amount' => $comp,
                'remaining_amount' => $comp,
            ]);

            if ($isLoss && $user && $user->fcm_token) {
                NDVINotificationHelper::notifyFarmer(
                    $user,
                    $ndviRecord->ndvi,
                    $ndviRecord->date,
                    $ndviRecord->insurance_type_id
                );
            }
        }

        return back()->with('success', 'Insurance Result Announced Successfully');
    }




    // Delete an entry
    public function satellite_ndvi_destroy($id)
    {
        InsuranceSubTypeSatelliteNDVI::findOrFail($id)->delete();
        return back()->with('success', 'NDVI record deleted successfully.');
    }

    public function weather_index($id)
    {
        $sideMenuName = [];
        $sideMenuPermissions = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
            $sideMenuPermissions = $subAdminData['sideMenuPermissions'];
        }
        $InsuranceType = InsuranceType::find($id);
        $InsuranceSubTypes = Village::get();


        return view('admin.insurance_types_and_sub_types.sub_types_weather_index', compact('sideMenuPermissions', 'sideMenuName', 'InsuranceSubTypes', 'InsuranceType'));
    }


    public function showVillageResult($id)
    {
        $village = Village::findOrFail($id);

        $villageWeathers = VillageWeatherDailySummary::with('village')
            ->where('village_id', $id)
            ->orderBy('date', 'desc')
            // ->orderBy('time', 'desc')
            ->get();

        $cropData = $village->villageCrops()->first();

        return view('admin.insurance_types_and_sub_types.weather_index_result', compact(
            'village',
            'villageWeathers',
            'cropData'
        ));
    }
}
