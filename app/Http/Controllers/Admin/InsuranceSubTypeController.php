<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tehsil;
use App\Models\District;
use Illuminate\Http\Request;
use App\Models\InsuranceType;
use App\Models\EnsuredCropName;
use App\Models\InsuranceSubType;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;

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

        return view('admin.insurance_types_and_sub_types.sub_types', compact('sideMenuPermissions', 'sideMenuName', 'InsuranceSubTypes', 'InsuranceType','ensuredCrops', 'districts', 'tehsils'));
    }
    
    public function store(Request $request) 
    {
        // dd($request);
        $request->validate([
            'incurance_type_id' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            // 'status' => 'nullable'
        ]);
        // dd($request);

        InsuranceSubType::create([
            'incurance_type_id' => $request->incurance_type_id,
            'name' => $request->name,
            'district_name' => $request->district_name,
            'tehsil_id' => $request->tehsil_id,
            'current_yield' => $request->current_yield,
            'year' => $request->year,
            // 'status' => $request->status,
        ]);

        return redirect()->route('insurance.sub.type.index', ['id' => $request->incurance_type_id])->with(['message' => 'Insurance Sub-Type Created Successfully']);
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
        try{
            InsuranceSubType::destroy($id);
            return redirect()->route('insurance.sub.type.index', ['id' => $request->incurance_type_id])->with(['message' => 'Insurance Sub-Type Deleted Successfully']);
        }catch(QueryException $e){
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
        $InsuranceSubTypes = InsuranceSubType::with(['district', 'tehsil'])
        ->where('incurance_type_id', $id)
        ->orderBy('status', 'desc')
        ->latest()
        ->get();

        return view('admin.insurance_types_and_sub_types.sub_types_production_price', compact('sideMenuPermissions', 'sideMenuName', 'InsuranceSubTypes', 'InsuranceType','ensuredCrops', 'districts', 'tehsils'));
    }

    public function production_price_store(Request $request) 
    {
        $request->validate([
            // 'incurance_type_id' => 'required|string|max:255',
            // 'district_name' => 'required',
            // 'tehsil_id' => 'required',
            // 'year' => 'required|numeric',
            'crops' => 'required|array|min:1',
            // 'crops.*.name' => 'required|string',
            // 'crops.*.cost_of_production' => 'nullable|numeric',
            // 'crops.*.average_yield' => 'nullable|numeric',
            // 'crops.*.historical_average_market_price' => 'nullable|numeric',
            // 'crops.*.real_time_market_price' => 'nullable|numeric',
            // 'crops.*.ensured_yield' => 'nullable|numeric',
        ]);

        foreach ($request->crops as $crop) {
            InsuranceSubType::create([
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
        }
        
        // dd($request->toArray());

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
        try{
            InsuranceSubType::destroy($id);
            return redirect()->route('insurance.sub.type.productionPrice', ['id' => $request->incurance_type_id])->with(['message' => 'Insurance Sub-Type Deleted Successfully']);
        }catch(QueryException $e){
            return redirect()->route('insurance.sub.type.productionPrice', ['id' => $request->incurance_type_id])->with(['error' => 'This insurance Sub-type cannot be deleted because it is assigned to insurance companies.']);
        }
    }

}
