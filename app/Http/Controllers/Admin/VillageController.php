<?php

namespace App\Http\Controllers\Admin;

use App\Models\Uc;
use App\Models\Village;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\EnsuredCropName;
use Illuminate\Support\Facades\Auth;

class VillageController extends Controller
{
    public function index($id)
    {
        $uc = Uc::find($id);

        $sideMenuName = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
        }

        $villages = Village::with('crops')->where('uc_id', $id)->orderBy('name', 'asc')->get();
        $crops = EnsuredCropName::all();

    return view('admin.land.village.index', compact('uc', 'villages', 'sideMenuName', 'crops'))->with('n', $uc->tehsil_id);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'uc_id' => 'required|exists:ucs,id',
            'name' => 'required|string|unique:villages,name',
            'crops' => 'required|array|min:1',
            'crops.*.crop_name_id' => 'required|exists:ensured_crop_name,id',
            'crops.*.avg_temp' => 'required|numeric',
            'crops.*.avg_rainfall' => 'required|numeric',
        ]);    

        // add village
        $village = Village::create([
            'uc_id' => $request->uc_id,
            'name' => $request->name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);
        
        // add crops along with village
        foreach ($request->crops as $crop) {
            $village->crops()->create([
                'crop_name_id' => $crop['crop_name_id'],
                'avg_temp' => $crop['avg_temp'],
                'avg_rainfall' => $crop['avg_rainfall'],
            ]);
        }        

        return redirect()->route('village.index', ['id' => $request->uc_id])
            ->with(['message' => 'Village Created Successfully']);
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|unique:villages,name,' . $id,
            'crops' => 'nullable|array',
            'crops.*.crop_name_id' => 'required|exists:ensured_crop_name,id',
            'crops.*.avg_temp' => 'required|numeric',
            'crops.*.avg_rainfall' => 'required|numeric',
        ]);

        $village = Village::findOrFail($id);

        // Update village name
        $village->update([
            'name' => $request->name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        // delete old crop data
        $village->crops()->delete(); 

        // Re-insert new crop data
        if ($request->has('crops')) {
            foreach ($request->crops as $crop) {
                $village->crops()->create([
                    'crop_name_id' => $crop['crop_name_id'],
                    'avg_temp' => $crop['avg_temp'],
                    'avg_rainfall' => $crop['avg_rainfall'],
                ]);
            }
        }

        return redirect()->route('village.index', ['id' => $request->uc_id])
            ->with(['message' => 'Village Updated Successfully']);
    }


    public function destroy(Request $request, $id)
    {
        Village::destroy($id);
        return redirect()->route('village.index', ['id' => $request->uc_id])->with(['message' => 'Village Deleted Successfully']);
    }
}
