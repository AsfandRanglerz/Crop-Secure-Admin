<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\EnsuredCropName;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class EnsuredCropNameController extends Controller
{
    public function index()
    {
        // dd('doe');
        $sideMenuName = [];
        $sideMenuPermissions = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
            $sideMenuPermissions = $subAdminData['sideMenuPermissions'];
        }

        // dd('done');
        $EnsuredCropNames = EnsuredCropName::latest()->get();
        $currentYear = Carbon::now()->year;
        return view('admin.ensured_crops.name', compact('sideMenuPermissions', 'sideMenuName', 'EnsuredCropNames', 'currentYear'));
    }

    public function store(Request $request)
    {
        // dd($request);
        // Validation
        $request->validate([
            'name' => 'required|string|unique:ensured_crop_name,name',
            'sum' => 'required'

        ]);

        // Create the record
        EnsuredCropName::create([
            'name' => $request->name,
            'sum_insured_value' => $request->sum,
            'harvest_start_time' => $request->harvest_start,
            'harvest_end_time' => $request->harvest_end,
            'insurance_start_time' => $request->insurance_start,
            'insurance_end_time' => $request->insurance_end,
        ]);

        // Redirect or return response
        return redirect()->route('ensured.crop.name.index')->with('message', 'Ensured Crop created successfully!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|unique:ensured_crop_name,name,' . $id,
        ]);

        $data = EnsuredCropName::find($id);

        $data->update([
            'name' => $request->name,
            'sum_insured_value' => $request->sum,
            'harvest_start_time' => $request->harvest_start,
            'harvest_end_time' => $request->harvest_end,
            'insurance_start_time' => $request->insurance_start,
            'insurance_end_time' => $request->insurance_end,
        ]);

        return redirect()->route('ensured.crop.name.index')->with('message', 'Ensured Crop updated successfully!');
    }

    public function destroy($id)
    {
        EnsuredCropName::destroy($id);
        return redirect()->route('ensured.crop.name.index')->with('message', 'Ensured Crop deleted successfully!');
    }
}
