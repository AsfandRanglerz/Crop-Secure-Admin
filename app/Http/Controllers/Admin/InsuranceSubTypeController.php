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
use App\Helpers\ProductionPriceNotificationHelper;
use App\Models\InsuranceHistory;
use App\Models\InsuranceSubTypeSatelliteNDVI;
use App\Models\Land;
use App\Models\Village;
use App\Models\VillageWeatherDailySummary;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Jobs\ProcessProductionPriceNotificationJob;
use App\Jobs\SendNDVINotificationJob;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessSatelliteNDVIJob;

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
                // dd($user->fcm_token);
                Log::info('🚀 Dispatching notification job for user: ' . $user->id);
                dispatch(new \App\Jobs\SendAreaYieldNotificationJob(
                    $user,
                    $request->name,
                    $request->year,
                    $request->current_yield
                ));
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

        foreach ($request->crops as $crop) {
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

            // ✅ Dispatch job
            dispatch(new ProcessProductionPriceNotificationJob(
                $subType,
                $request->year,
                $request->incurance_type_id,
                $request->crop_name_id,
                $crop['district_id'],
                $crop['tehsil_id']
            ));
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

        $insuranceLandStrings = DB::table('insurance_histories')
            ->select('user_id', 'land')
            ->whereNotNull('land')
            ->get()
            ->pluck('land');
        $currentYear = now()->year;

        $excludedLandIds = InsuranceSubTypeSatelliteNDVI::whereYear('date', $currentYear)
            ->pluck('land_id')
            ->toArray();

        $records = Land::with('farmer:id,name')
            ->whereIn(DB::raw("CONCAT(location, ' (', area, ' ', area_unit, ')')"), $insuranceLandStrings->values())
            ->whereNotIn('id', $excludedLandIds)
            ->select('id', 'location', 'demarcation', 'user_id', 'area', 'area_unit')
            ->get();



        foreach ($records as $record) {
            // Parse and store original array
            $points = json_decode($record->demarcation, true);
            $record->demarcation_array = is_array($points) ? $points : [];

            // Encode JSON string for Blade dropdown (as string not array)
            $record->demarcation_json = json_encode($points);
        }

        $InsuranceSubTypes = InsuranceSubTypeSatelliteNDVI::with('land.farmer')
            ->where('insurance_type_id', $id)
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
            'villages',
            'records'
        ));
    }


    // Manually store a record from the modal

    // public function satellite_ndvi_store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'date' => 'required|date',
    //         'land_id' => 'required|exists:lands,id',
    //         'demarcation_points' => 'required|json',
    //         'incurance_type_id' => 'nullable|exists:insurance_types,id',
    //     ]);

    //     $points = json_decode($request->demarcation_points, true);

    //     if (!is_array($points) || count($points) < 3) {
    //         return back()->withErrors(['demarcation_points' => 'At least 3 points required.'])->withInput();
    //     }

    //     // Convert to EOS GeoJSON Polygon coordinates
    //     $coordinates = array_map(function ($point) {
    //         return [(float) $point['longitude'], (float) $point['latitude']];
    //     }, $points);

    //     // Ensure polygon is closed (first = last)
    //     if ($coordinates[0] !== end($coordinates)) {
    //         $coordinates[] = $coordinates[0];
    //     }

    //     $apiKey = 'apk.ec114200944764f1f5162bf2efc7cd4ccb9afb90efaa35594cf3058b0244d6da';

    //     try {
    //         // Step 1: Get view_id from EOS API
    //         $response = Http::post("https://api-connect.eos.com/api/lms/search/v2/sentinel2?api_key=$apiKey", [
    //             'search' => [
    //                 'date' => [
    //                     'to' => $request->date,
    //                 ],
    //                 'shape' => [
    //                     'type' => 'Polygon',
    //                     'coordinates' => [$coordinates],
    //                 ],
    //             ]
    //         ]);

    //         if (!$response->ok()) {
    //             return back()->withErrors(['api_error' => 'EOS View ID fetch failed'])->withInput();
    //         }

    //         // dd($response->json());

    //         $viewId = $response->json()['results'][0]['view_id'] ?? null;

    //         if (!$viewId) {
    //             return back()->withErrors(['view_id' => 'No view_id found for this polygon'])->withInput();
    //         }
    //         $lat = array_sum(array_column($points, 'latitude')) / count($points);
    //         $lon = array_sum(array_column($points, 'longitude')) / count($points);
    //         // Explode view_id to get segments
    //         $segments = explode('/', $viewId);

    //         if (count($segments) < 8) {
    //             return back()->withErrors(['view_id' => 'view_id format is invalid'])->withInput();
    //         }

    //         [$satellite, $utm_zone, $latitude_band, $grid_square, $year, $month, $day, $cloud] = $segments;

    //         $ndviResponse = Http::get("https://api-connect.eos.com/api/render/{$satellite}/point/{$utm_zone}/{$latitude_band}/{$grid_square}/{$year}/{$month}/{$day}/{$cloud}/NDVI/{$lat}/{$lon}?api_key=$apiKey");

    //         // $ndviResponse = Http::get("https://api-connect.eos.com/api/render/S2/point/55/G/EP/2016/7/19/0/NDVI/-42.026067/147.824152?api_key=$apiKey");
    //         // $ndviResponse = Http::get("https://api-connect.eos.com/api/render/view/$viewId/ndvi?api_key=$apiKey");
    //         // dd($ndviResponse->json());

    //         if (!$ndviResponse->ok()) {
    //             return back()->withErrors(['ndvi_error' => 'Failed to fetch NDVI'])->withInput();
    //         }

    //         $ndvi = $ndviResponse->json()['index_value'] ?? null;
    //         // dd($ndvi);
    //         if (!is_numeric($ndvi)) {
    //             return back()->withErrors(['ndvi_value' => 'NDVI data not available'])->withInput();
    //         }


    //         // Check if NDVI already exists for the date, village, and type
    //         // $DatealreadyExists = InsuranceSubTypeSatelliteNDVI::where('date', $request->date)
    //         //     ->where('insurance_type_id', $request->incurance_type_id)
    //         //     ->exists();

    //         // if ($DatealreadyExists) {
    //         //     return back()->withErrors(['duplicate' => 'NDVI for the same date already exists.'])->withInput();
    //         // }

    //         $AreaalreadyExists = InsuranceSubTypeSatelliteNDVI::where('land_id', $request->land_id)
    //             ->where('insurance_type_id', $request->incurance_type_id)
    //             ->exists();


    //         if ($AreaalreadyExists) {
    //             return back()->withErrors(['duplicate' => 'NDVI for the same area already exists.'])->withInput();
    //         }

    //         // Save NDVI data
    //         InsuranceSubTypeSatelliteNDVI::create([
    //             'date' => $request->date,
    //             'land_id' => $request->land_id,
    //             'ndvi' => $ndvi,
    //             'insurance_type_id' => $request->incurance_type_id,
    //         ]);

    //         // Find relevant farmers
    //         $farmerIds = Land::where('id', $request->land_id)
    //             ->pluck('user_id')
    //             ->toArray();

    //         if (empty($farmerIds)) {
    //             return back()->withErrors(['info' => 'No farmers found in this area.'])->withInput();
    //         }

    //         $farmers = InsuranceHistory::with('user')
    //             ->where('insurance_type_id', $request->incurance_type_id)
    //             ->where('status', 'unclaimed')
    //             ->whereIn('user_id', $farmerIds)
    //             ->get();

    //         // Compensation logic based on NDVI threshold
    //         $threshold = 0.4;
    //         $notifiedUserIds = [];

    //         foreach ($farmers as $record) {
    //             $user = $record->user;
    //             $isLoss = $ndvi < $threshold;
    //             $comp = $isLoss ? $record->sum_insured : 0;

    //             // Update compensation amount
    //             $record->update([
    //                 'compensation_amount' => $comp,
    //                 'remaining_amount' => $comp,
    //             ]);

    //             // Notify user only once per insurance type
    //             if (
    //                 $isLoss &&
    //                 $user &&
    //                 $user->fcm_token &&
    //                 !in_array($user->id, $notifiedUserIds)
    //             ) {
    //                 NDVINotificationHelper::notifyFarmer(
    //                     $user,
    //                     $ndvi,
    //                     $request->date,
    //                     $request->incurance_type_id // ye insurance type ka specific result
    //                 );

    //                 $notifiedUserIds[] = $user->id;
    //             }
    //         }


    //         return back()->with('success', 'NDVI saved and farmers notified.');
    //     } catch (\Throwable $e) {
    //         return back()->withErrors(['exception' => 'Error: ' . $e->getMessage()])->withInput();
    //     }
    // }


    // public function satellite_ndvi_store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'date' => 'required|date',
    //         'land_id' => 'required|array|min:1',
    //         'land_id.*' => 'exists:lands,id',
    //         'demarcation_points_map' => 'required|json',
    //         'incurance_type_id' => 'nullable|exists:insurance_types,id',
    //     ]);

    //     $demarcationMap = json_decode($request->demarcation_points_map, true);
    //     $apiKey = 'apk.ec114200944764f1f5162bf2efc7cd4ccb9afb90efaa35594cf3058b0244d6da';
    //     $threshold = 0.4;

    //     try {
    //         $notifiedHistories = []; 

    //         foreach ($request->land_id as $landId) {
    //             $points = $demarcationMap[$landId] ?? null;

    //             if (!$points || count($points) < 3) continue;

    //             $coordinates = array_map(function ($point) {
    //                 return [(float) $point['longitude'], (float) $point['latitude']];
    //             }, $points);

    //             if ($coordinates[0] !== end($coordinates)) {
    //                 $coordinates[] = $coordinates[0];
    //             }

    //             $response = Http::post("https://api-connect.eos.com/api/lms/search/v2/sentinel2?api_key=$apiKey", [
    //                 'search' => [
    //                     'date' => ['to' => $request->date],
    //                     'shape' => ['type' => 'Polygon', 'coordinates' => [$coordinates]],
    //                 ]
    //             ]);

    //             if (!$response->ok()) continue;

    //             $viewId = $response->json()['results'][0]['view_id'] ?? null;
    //             if (!$viewId) continue;

    //             $lat = array_sum(array_column($points, 'latitude')) / count($points);
    //             $lon = array_sum(array_column($points, 'longitude')) / count($points);

    //             $segments = explode('/', $viewId);
    //             if (count($segments) < 8) continue;

    //             [$satellite, $utm_zone, $latitude_band, $grid_square, $year, $month, $day, $cloud] = $segments;

    //             $ndviResponse = Http::get("https://api-connect.eos.com/api/render/{$satellite}/point/{$utm_zone}/{$latitude_band}/{$grid_square}/{$year}/{$month}/{$day}/{$cloud}/NDVI/{$lat}/{$lon}?api_key=$apiKey");

    //             if (!$ndviResponse->ok()) continue;

    //             $ndvi = $ndviResponse->json()['index_value'] ?? null;
    //             if (!is_numeric($ndvi)) continue;

    //             $exists = InsuranceSubTypeSatelliteNDVI::where('date', $request->date)
    //                 ->where('land_id', $landId)
    //                 ->where('insurance_type_id', $request->incurance_type_id)
    //                 ->exists();

    //             if ($exists) continue;

    //             InsuranceSubTypeSatelliteNDVI::create([
    //                 'date' => $request->date,
    //                 'land_id' => $landId,
    //                 'ndvi' => $ndvi,
    //                 'insurance_type_id' => $request->incurance_type_id,
    //             ]);

    //             $farmerId = Land::where('id', $landId)->value('user_id');
    //             if (!$farmerId) continue;

    //             $farmers = InsuranceHistory::with('user')
    //                 ->where('insurance_type_id', $request->incurance_type_id)
    //                 ->where('status', 'unclaimed')
    //                 ->where('user_id', $farmerId)
    //                 ->get();

    //             foreach ($farmers as $record) {
    //                 $user = $record->user;
    //                 if (!$user) continue;

    //                 $uniqueKey = $user->id . '_' . $landId;
    //                 if (in_array($uniqueKey, $notifiedHistories)) continue;

    //                 $isLoss = $ndvi < $threshold;
    //                 $comp = $isLoss ? $record->sum_insured : 0;

    //                 $record->update([
    //                     'compensation_amount' => $comp,
    //                     'remaining_amount' => $comp,
    //                 ]);

    //                 if ($isLoss && $user->fcm_token) {
    //                     dispatch(new SendNDVINotificationJob(
    //                         $user,
    //                         $ndvi,
    //                         $request->date,
    //                         $request->incurance_type_id
    //                     ));
    //                 }

    //                 $notifiedHistories[] = $uniqueKey;
    //             }
    //         }

    //         return back()->with('success', 'Insurance Result Announced Successfully');
    //     } catch (\Throwable $e) {
    //         return back()->withErrors(['error' => $e->getMessage()])->withInput();
    //     }
    // }


    public function satellite_ndvi_store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'land_id' => 'required|array|min:1',
            'land_id.*' => 'exists:lands,id',
            'demarcation_points_map' => 'required|json',
            'incurance_type_id' => 'nullable|exists:insurance_types,id',
        ]);

        try {
            dispatch(new ProcessSatelliteNDVIJob(
                $validated['date'],
                $validated['land_id'],
                json_decode($validated['demarcation_points_map'], true),
                $validated['incurance_type_id']
            ));

            return back()->with('success', 'Insurance Result Announced Successfully');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
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
