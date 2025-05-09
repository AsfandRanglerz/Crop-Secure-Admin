<?php

namespace App\Http\Controllers\Api;

use App\Models\Uc;
use App\Models\Land;
use App\Models\Tehsil;
use App\Models\Village;
use App\Models\AreaUnit;
use App\Models\District;
use Illuminate\Http\Request;
use App\Models\CropInsurance;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LandController extends Controller
{
    public function store(Request $request)
{
    $userid = Auth::user();
    // $request->validate([
    //     'location' => 'required|string',
    //     'area' => 'required|numeric',
    //     'area_unit' => 'required|exists:area_units,id',
    //     'license_file' => 'required|file|mimes:pdf,jpg,jpeg,png',
    //     'ownership_certificate' => 'required|file|mimes:pdf,jpg,jpeg,png',
    // ]);

    if ($request->hasFile('image')) {
    $file = $request->file('image');
    $filename = time() . '_image_' . $file->getClientOriginalName();
    $file->move(public_path('admin/assets/images/users'), $filename);
    $image = 'public/admin/assets/images/users/' . $filename;
}

if ($request->hasFile('certificate')) {
    $file = $request->file('certificate');
    $filename = time() . '_certificate_' . $file->getClientOriginalName();
    $file->move(public_path('admin/assets/images/users'), $filename);
    $certificate = 'public/admin/assets/images/users/' . $filename;
}

    $land = Land::create([
        // 'user_id' => auth()->id(),
        'location' => $request->location,
        'area' => $request->area,
        'area_unit' => $request->area_unit, // Store as ID
        'image' => $image,
        'certificate' => $certificate,
    ]);

    return response()->json(['message' => 'Land added successfully', 'land' => $land], 200);
}

public function getAreaUnits()
{
    $user = Auth::user();
    return response()->json(AreaUnit::all());
}

public function showlands()
{
    $user = Auth::user();

    $lands = Land::
        select('id', 'location','area_unit', 'image', 'certificate')
        ->get()
        ->map(function ($land) {
            return [
                'id' => $land->id,
                'location' => $land->location,
                'area_unit' => $land->area_unit,
                'image' => $land->image,
                'certificate' => $land->certificate,
            ];
        });

        return response()->json(['message' => 'Land retrieved successfully', 'lands' => $lands], 200);
}

public function landrecord(Request $request){
    $user = Auth::user();
    $land = CropInsurance::create([
        'district_id' => $request->district_id,
        'tehsil_id' => $request->tehsil_id,
        'uc' => $request->uc,
        'village' => $request->village,
        'other' => $request->other,
    ]);

    return response()->json([
        'message' => 'Land record saved successfully',
        'data' => $land
    ], 200);
}
public function getDistricts()
    {
        $districts = District::select('id', 'name')->get();
        return response()->json($districts);
    }

    public function getTehsils($district_id)
    {
        $tehsils = Tehsil::where('district_id', $district_id)->select('id', 'name')->get();
        return response()->json($tehsils);
    }

    public function getUCs($tehsil_id)
    {
        $ucs = Uc::where('tehsil_id', $tehsil_id)->select('id', 'name')->get();
        return response()->json($ucs);
    }

    public function getVillages($uc_id)
    {
        $villages = Village::where('uc_id', $uc_id)->select('id', 'name')->get();
        return response()->json($villages);
    }
}
