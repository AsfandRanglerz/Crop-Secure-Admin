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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InsuranceSubTypeController extends Controller
{
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
        $ensuredCrops = EnsuredCropName::all();  // Fetch all crops
        $districts = District::all();         // Fetch all districts
        $tehsils = Tehsil::all();             // Fetch all tehsils
        $InsuranceType = InsuranceType::find($id);
        $InsuranceSubTypes = InsuranceSubType::with(['district', 'tehsil'])->where('incurance_type_id', $id)->orderBy('status', 'desc')->latest()->get();

        return view('admin.insurance_types_and_sub_types.sub_types', compact('sideMenuPermissions', 'sideMenuName', 'InsuranceSubTypes', 'InsuranceType', 'ensuredCrops', 'districts', 'tehsils'));
    }


    public function store(Request $request)
    {
        $insuranceSubType = InsuranceSubType::create([
            'incurance_type_id' => $request->incurance_type_id,
            'name' => $request->name,
            'district_name' => $request->district_name,
            'tehsil_id' => $request->tehsil_id,
            'current_yield' => $request->current_yield,
            'year' => $request->year,
        ]);

        $farmers = CropInsurance::with('user')
            ->where('crop', $request->name)
            ->where('district_id', $request->district_id)
            ->where('tehsil_id', $request->tehsil_id)
            ->where('year', $request->year)
            ->get();

        foreach ($farmers as $record) {
            $benchmark = $record->benchmark_percent;
            $area = $record->area;
            $sumInsuredBase = $record->sum_insured_100_percent;
            $sumInsured = ($benchmark / 100) * ($sumInsuredBase * $area);
            $lossPercentage = $benchmark - $request->current_yield;

            $compensation = 0;
            $status = 'no loss';
            if ($lossPercentage > 0) {
                $compensation = ($lossPercentage / 100) * $sumInsured;
                $status = 'loss';
            }

            $record->update([
                'compensation' => $compensation,
                'status' => $status,
            ]);

            $user = $record->user;
            if ($user && $user->fcm_token) {
                NotificationHelper::sendFcmNotification(
                    $user->fcm_token,
                    'Insurance Update: ' . $record->crop,
                    $compensation > 0
                        ? 'You are eligible for Rs. ' . number_format($compensation)
                        : 'No compensation. Yield met or exceeded benchmark.',
                    [
                        'compensation' => $compensation,
                        'status' => $status,
                        'crop' => $record->crop,
                        'year' => $record->year,
                        'area' => $area,
                        'benchmark' => $benchmark,
                        'current_yield' => $request->current_yield,
                        'sum_insured' => $sumInsured,
                        'sum_insured_base' => $sumInsuredBase,
                    ]
                );
            }
        }

        return redirect()
            ->route('insurance.sub.type.index', ['id' => $request->incurance_type_id])
            ->with(['message' => 'Yield recorded and farmers notified successfully']);
    }



    public function update(Request $request, $id)
    {
        // dd($request);
        $request->validate([
            'incurance_type_id' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            // 'status' => 'nullable'
        ]);
        $data = InsuranceSubType::findOrFail($id);
        // dd($data);
        $data->update([
            'name' => $request->name,
            'district_name' => $request->district_name,
            'tehsil_id' => $request->tehsil_id,
            'current_yield' => $request->current_yield,
            'year' => $request->year,
            // 'status' => $request->status,
        ]);

        return redirect()->route('insurance.sub.type.index', ['id' => $request->incurance_type_id])->with(['message' => 'Insurance Sub-Type Updated Successfully']);
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
        $ensuredCrops = EnsuredCropName::all();  // Fetch all crops
        $districts = District::all();         // Fetch all districts
        $tehsils = Tehsil::all();             // Fetch all tehsils
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
        foreach ($request->crops as $crop) {
            $subType = InsuranceSubType::create([
                'crop_name_id' => $request->crop_name_id,
                'incurance_type_id' => $request->incurance_type_id,
                'district_name' => $crop['district_name'] ?? null,
                'tehsil_id' => $crop['tehsil_id'] ?? null,
                'cost_of_production' => $crop['cost_of_production'],
                'average_yield' => $crop['average_yield'] ?? null,
                'historical_average_market_price' => $crop['historical_average_market_price'] ?? null,
                'real_time_market_price' => $crop['real_time_market_price'] ?? null,
                'ensured_yield' => $crop['ensured_yield'] ?? null,
                'year' => $request->year,
            ]);

            // 🔔 Notify relevant farmers
            $farmers = CropInsurance::with('user')
                ->where('crop', $subType->name)
                ->where('district_id', $subType->district_name)
                ->where('tehsil_id', $subType->tehsil_id)
                ->where('year', $subType->year)
                ->get();

            foreach ($farmers as $record) {
                $user = $record->user;
                if ($user && $user->fcm_token) {
                    NotificationHelper::sendFcmNotification(
                        $user->fcm_token,
                        'Production Price Insurance Update',
                        'New insured yield and price details are available for your crop.',
                        [
                            'crop' => $record->crop,
                            'year' => $record->year,
                            'district' => $subType->district_name,
                            'tehsil' => $subType->tehsil_id,
                            'ensured_yield' => $subType->ensured_yield,
                            'real_time_price' => $subType->real_time_market_price,
                            'avg_price' => $subType->historical_average_market_price,
                        ]
                    );
                }
            }
        }

        return redirect()->route('insurance.sub.type.productionPrice', ['id' => $request->incurance_type_id])
            ->with(['message' => 'Insurance Sub-Types Created Successfully']);
    }


    public function production_price_update(Request $request, $id)
    {
        // dd($request);
        $request->validate([
            'incurance_type_id' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            // 'status' => 'nullable'
        ]);
        $data = InsuranceSubType::findOrFail($id);
        // dd($data);
        $data->update([
            'name' => $request->name,
            'incurance_type_id' => $request->incurance_type_id,
            'district_name' => $request->district_name,
            'tehsil_id' => $request->tehsil_id,
            'cost_of_production' => $request->cost_of_production,
            'average_yield' => $request->average_yield,
            'historical_average_market_price' => $request->historical_average_market_price,
            'real_time_market_price' => $request->real_time_market_price,
            'ensured_yield' => $request->ensured_yield,
            'year' => $request->year,
            // 'status' => $request->status,
        ]);

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
        $request->validate([
            'date' => 'required|date',
            'b8' => 'required|numeric|min:0|max:999999999999999',
            'b4' => 'required|numeric|min:0|max:999999999999999',
            'ndvi' => 'required|numeric',
            'incurance_type_id' => 'nullable|exists:insurance_types,id',
        ]);

        // Step 1: Save the NDVI record
        $ndviRecord = InsuranceSubTypeSatelliteNDVI::create([
            'date' => $request->date,
            'b8' => $request->b8,
            'b4' => $request->b4,
            'ndvi' => $request->ndvi,
            'insurance_type_id' => $request->incurance_type_id,
        ]);

        // Step 2: Notify all farmers who purchased Satellite NDVI insurance
        $farmers = CropInsurance::with('user')
            ->where('insurance_type', 'Satellite Index')
            ->get();

        foreach ($farmers as $record) {
            $user = $record->user;
            if ($user && $user->fcm_token) {
                \App\Helpers\NotificationHelper::sendFcmNotification(
                    $user->fcm_token,
                    'Satellite NDVI Update',
                    'New NDVI data is available for your insured crop.',
                    [
                        'date' => $ndviRecord->date,
                        'b8' => $ndviRecord->b8,
                        'b4' => $ndviRecord->b4,
                        'ndvi' => $ndviRecord->ndvi,
                    ]
                );
            }
        }

        return back()->with('success', 'NDVI entry saved and farmers notified.');
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
        $villageCrops = \App\Models\VillageCrop::with('village')
            ->get();
            $village = Village::findOrFail($id);
        return view('admin.insurance_types_and_sub_types.weather_index_result', compact('villageCrops','village'));
    }
    
}
