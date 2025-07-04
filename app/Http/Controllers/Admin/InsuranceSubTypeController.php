<?php

namespace App\Http\Controllers\Admin;

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
use App\Models\InsuranceSubTypeSatelliteNDVI;
use App\Models\Village;
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

        //  Check for duplicate entry before insert
        $exists = InsuranceSubType::where('name', $request->name)
            ->where('district_id', $request->district_id)
            ->where('tehsil_id', $request->tehsil_id)

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

        // ✅ Fetch insured farmers from InsuranceHistory
        $farmers = \App\Models\InsuranceHistory::with('user')
            ->where('insurance_type_id', $request->incurance_type_id)
            ->where('district_id', $request->district_id)
            ->where('tehsil_id', $request->tehsil_id)
            ->whereYear('created_at', $request->year)
            ->get();

        foreach ($farmers as $record) {
            $user = $record->user;

            if (!$user || !$user->fcm_token) {
                continue;
            }

            $benchmark = $record->benchmark_percent ?? 0;
            $area = $record->area ?? 0;
            $sumInsuredBase = $record->sum_insured_100_percent ?? 0;

            $sumInsured = ($benchmark / 100) * ($sumInsuredBase * $area);
            $lossPercentage = $benchmark - $request->current_yield;

            $compensation = 0;
            $status = 'no loss';

            if ($lossPercentage > 0) {
                $compensation = ($lossPercentage / 100) * $sumInsured;
                $status = 'loss';
            }

            NotificationHelper::sendFcmNotification(
                $user->fcm_token,
                'Area Yield Update',
                $compensation > 0
                    ? 'You are eligible for Rs. ' . number_format($compensation)
                    : 'No compensation. Yield met or exceeded benchmark.',
                [
                    'type' => 'yield_result',
                    'crop' => $request->name,
                    'year' => (string) $request->year,
                    'area' => (string) $area,
                    'benchmark' => (string) $benchmark,
                    'current_yield' => (string) $request->current_yield,
                    'sum_insured' => (string) $sumInsured,
                    'sum_insured_base' => (string) $sumInsuredBase,
                    'status' => $status,
                    'district_id' => (string) $record->district_id,
                    'tehsil_id' => (string) $record->tehsil_id,
                ]
            );
        }


        return redirect()
            ->route('insurance.sub.type.index', ['id' => $request->incurance_type_id])
            ->with(['message' => 'Yield recorded and farmers notified successfully']);
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
            ->with(['message' => 'Insurance Sub-Type Updated Successfully']);
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

        foreach ($request->crops as $crop) {
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

            // ✅ Now fetch farmers from InsuranceHistory table
            $farmers = \App\Models\InsuranceHistory::with('user')
                ->where('insurance_type_id', $request->incurance_type_id)
                ->where('district_id', $crop['district_id'])
                ->where('tehsil_id', $crop['tehsil_id'])
                ->whereYear('created_at', $request->year)
                ->get();

            foreach ($farmers as $record) {
                $user = $record->user;
                if ($user && $user->fcm_token) {
                    \App\Helpers\NotificationHelper::sendFcmNotification(
                        $user->fcm_token,
                        'Production Price Update',
                        [
                            'type' => 'production_price',
                            'district_id' => (string) $record->district_id,
                            'tehsil_id' => (string) $record->tehsil_id,
                            'insurance_type_id' => (string) $record->insurance_type_id,
                            'year' => (string) $request->year,
                        ]
                    );
                }
            }
        }

        return redirect()->route('insurance.sub.type.productionPrice', ['id' => $request->incurance_type_id])
            ->with(['message' => 'Production prices saved and farmers notified successfully']);
    }



    public function production_price_update(Request $request, $id)
    {
        //dd($request);

        $request->validate([
            'crop_name_id' => 'required|exists:insurance_types,id',
            'crops.0.district_id' => 'required|exists:districts,id',
            'crops.0.tehsil_id' => 'required|exists:tehsils,id',
            'cost_of_production' => 'nullable|numeric',
            // 'average_yield' => 'nullable|numeric',
            // 'historical_average_market_price' => 'nullable|numeric',
            // 'real_time_market_price' => 'nullable|numeric',
            //'ensured_yield' => 'nullable|numeric',
            //'year' => 'required',
        ]);

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



        return redirect()->route('insurance.sub.type.productionPrice', ['id' => $request->incurance_type_id])->with(['message' => 'Insurance Sub-Type Updated Successfully']);
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

        // 🟢 Fetch NDVI records, not subtypes
        $InsuranceSubTypes = InsuranceSubTypeSatelliteNDVI::where('insurance_type_id', $id)
            ->orderBy('date', 'desc')
            ->get();

        return view('admin.insurance_types_and_sub_types.sub_types_satelliteNDVI', compact(
            'sideMenuPermissions',
            'sideMenuName',
            'InsuranceSubTypes',
            'InsuranceType',
            'ensuredCrops',
            'districts',
            'tehsils'
        ));
    }


    public function fetchNDVIData(Request $request)
    {
        // Log initial request input
        Log::info('NDVI fetch request received', $request->all());

        // Validate input
        $request->validate([
            'date' => 'required|date',
        ]);

        try {
            $apiKey = 'apk.ec114200944764f1f5162bf2efc7cd4ccb9afb90efaa35594cf3058b0244d6da';
            $apiUrl = 'https://api-connect.eos.com/user-dashboard/statistics';

            // Send API request
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Accept' => 'application/json',
            ])->get($apiUrl, [
                'date' => $request->date,
            ]);

            // Log raw response body
            Log::info('EOS NDVI raw response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            // If successful, process data
            if ($response->successful()) {
                $data = $response->json();

                $b8 = isset($data['B8']) ? floatval($data['B8']) : 0.0;
                $b4 = isset($data['B4']) ? floatval($data['B4']) : 0.0;

                // Log band values
                Log::info('NDVI band values', [
                    'B8' => $b8,
                    'B4' => $b4,
                    'B8 + B4' => $b8 + $b4,
                ]);

                // Prevent division by zero
                if (($b8 + $b4) == 0.0) {
                    Log::warning('NDVI calculation skipped due to division by zero', [
                        'B8' => $b8,
                        'B4' => $b4
                    ]);

                    return response()->json([
                        'error' => 'Invalid B8/B4 values (division by zero)',
                        'b8' => $b8,
                        'b4' => $b4,
                        'ndvi' => null
                    ], 422);
                }

                $ndvi = ($b8 - $b4) / ($b8 + $b4);

                return response()->json([
                    'b8' => $b8,
                    'b4' => $b4,
                    'ndvi' => round($ndvi, 4)
                ]);
            }

            // Handle unsuccessful EOS API response
            Log::error('EOS API request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return response()->json(['error' => 'Data not found or EOS request failed'], $response->status());
        } catch (\Exception $e) {
            // Log internal error
            Log::error('NDVI API Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    // Manually store a record from the modal
    public function satellite_ndvi_store(Request $request)
    {
        // Step 0: Validate
        $validated = $request->validate([
            'date' => 'required|date',
            'b8' => 'required|numeric|min:0|max:999999999999999',
            'b4' => 'required|numeric|min:0|max:999999999999999',
            'ndvi' => 'required|numeric',
            'incurance_type_id' => 'nullable|exists:insurance_types,id',
        ]);

        // Step 1: Create NDVI record
        $ndviRecord = InsuranceSubTypeSatelliteNDVI::create([
            'date' => $request->date,
            'b8' => $request->b8,
            'b4' => $request->b4,
            'ndvi' => $request->ndvi,
            'insurance_type_id' => $request->incurance_type_id,
        ]);

        // Step 2: Get farmers
        $farmers = \App\Models\InsuranceHistory::with('user')
            ->where('insurance_type_id', $request->incurance_type_id)
            ->get();

        // Step 3: Loop through and send notification if possible
        $results = [];

        foreach ($farmers as $record) {
            $user = $record->user;

            if (!$user) {
                $results[] = ['record_id' => $record->id, 'status' => '❌ User not found'];
                continue;
            }

            if (!$user->fcm_token) {
                $results[] = ['user_id' => $user->id, 'status' => '⚠️ No FCM token'];
                continue;
            }

            \App\Helpers\NotificationHelper::sendFcmNotification(
                $user->fcm_token,
                'Satellite NDVI Update',
                'NDVI: ' . $ndviRecord->ndvi .
                    ', Date: ' . $ndviRecord->date .
                    ', Type ID: ' . $ndviRecord->insurance_type_id,
                [
                    'ndvi' => (string) $ndviRecord->ndvi,
                    'date' => (string) $ndviRecord->date,
                    'type_id' => (string) $ndviRecord->insurance_type_id,
                ]
            );

        }

        // dd($results, '📊 Notification Results');

        // Final (only reached if dd is removed)
        return back()->with('success', 'NDVI saved. Notifications sent where applicable.');
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
        // ✅ Fetch weather
        $weatherController = new \App\Http\Controllers\Admin\WeatherController();
        $weatherController->fetchLast14DaysWeather($id);

        // ✅ Notification trigger
        $village = Village::findOrFail($id);

        // Ye logic command wali class se uthao aur yahan paste karo
        $cropData = $village->villageCrops()->first();
        if ($cropData) {
            $avgTemp = $cropData->avg_temp;
            $avgRain = $cropData->avg_rainfall;

            $highTempDays = VillageWeatherHistory::where('village_id', $village->id)
                ->where('date', '>=', now()->subDays(13)->toDateString())
                ->where('temperature', '>=', $avgTemp * 1.2)
                ->count();

            $rainfall = VillageWeatherHistory::where('village_id', $village->id)
                ->where('date', '>=', now()->subDays(13)->toDateString())
                ->sum('rainfall');

            $farmerIds = \App\Models\CropInsurance::where('village_id', $village->id)->pluck('user_id')->unique();
            $farmers = \App\Models\Farmer::whereIn('id', $farmerIds)->whereNotNull('fcm_token')->get();

            if ($highTempDays === 14) {
                foreach ($farmers as $farmer) {
                    \App\Helpers\NotificationHelper::sendFcmNotification(
                        $farmer->fcm_token,
                        'Weather Insurance Update',
                        'Your village had 14 days of high temperature above normal.'
                    );
                }
            }

            if ($rainfall >= $avgRain * 1.5 || $rainfall <= $avgRain * 0.5) {
                foreach ($farmers as $farmer) {
                    \App\Helpers\NotificationHelper::sendFcmNotification(
                        $farmer->fcm_token,
                        'Weather Insurance Update',
                        'Rainfall in your village is 50% more or less than normal.'
                    );
                }
            }
        }

        // ✅ Show to admin
        $villageWeathers = VillageWeatherHistory::with('village')
            ->where('village_id', $id)
            ->orderBy('date', 'desc')
            ->limit(14)
            ->get();

        return view('admin.insurance_types_and_sub_types.weather_index_result', compact('village', 'villageWeathers'));
    }
}
