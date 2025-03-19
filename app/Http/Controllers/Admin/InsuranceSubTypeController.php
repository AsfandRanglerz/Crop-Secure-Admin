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
        $InsuranceSubTypes = InsuranceSubType::where('incurance_type_id', $id)->orderBy('status', 'desc')->latest()->get();

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
            'tehsil' => $request->tehsil,
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
            'tehsil' => $request->tehsil,
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
}
