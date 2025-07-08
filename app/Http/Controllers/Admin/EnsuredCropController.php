<?php

namespace App\Http\Controllers\Admin;

use App\Models\Farmer;
use Illuminate\Http\Request;
use App\Models\EnsuredCropName;
use App\Models\EnsuredCropType;
use App\Models\AreaUnit;
use App\Models\InsuranceCompany;
use Illuminate\Support\Facades\Auth;
use App\Models\EnsuredCrop;
use App\Http\Controllers\Controller;

class EnsuredCropController extends Controller
{
    public function index()
    {
        $sideMenuName = [];
        $sideMenuPermissions = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
            $sideMenuPermissions = $subAdminData['sideMenuPermissions'];
        }

        // $farmer = Farmer::find($id);
        // $EnsuredCrops = EnsuredCrop::where('farmer_id', $id)->with('cropName')->latest()->get();

        // $cropNames = EnsuredCropName::orderBy('name', 'asc')->get();
        // $Units = AreaUnit::orderBy('unit', 'asc')->get();
        // $Insurance_companies = InsuranceCompany::orderBy('name', 'asc')->get();
        // dd($EnsuredCrops);

        return view('admin.ensured_crops.index', compact('sideMenuPermissions', 'sideMenuName'));
    }

    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'farmer_id' => 'required',
            'crop_name_id' => 'required',
            'land_area' => 'required|string|max:255',
            'insurance_start_date' => 'required|date',
            'insurance_end_date' => 'required|date',
        ]);
        
        // dd($request);
        // Create the record
        EnsuredCrop::create([
            'farmer_id' => $request->farmer_id,
            'crop_name_id' => $request->crop_name_id,
            'land_area' => $request->land_area,
            'insurance_start_date' => $request->insurance_start_date,
            'insurance_end_date' => $request->insurance_end_date,
        ]);

        // Redirect or return response
        return redirect()->route('ensured.crops.index', ['id' => $request->farmer_id])->with('message', 'Insured Crop Created Successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'crop_name_id' => 'required',
            'land_area' => 'required|string|max:255',
            'insurance_start_date' => 'required|date',
            'insurance_end_date' => 'required|date',
        ]);

        $data = EnsuredCrop::find($id);

        $data->update([
            'crop_name_id' => $request->crop_name_id,
            'land_area' => $request->land_area,
            'insurance_start_date' => $request->insurance_start_date,
            'insurance_end_date' => $request->insurance_end_date,
        ]);

        return redirect()->route('ensured.crops.index', ['id' => $request->farmer_id])->with('message', 'Ensured Crop updated successfully!');
    }

    public function destroy(Request $request, $id)
    {
        EnsuredCrop::destroy($id);
        return redirect()->route('ensured.crops.index', ['id' => $request->farmer_id])->with('message', 'Ensured Crop deleted successfully!');
    }
}
