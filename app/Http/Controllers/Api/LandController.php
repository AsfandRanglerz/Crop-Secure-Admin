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
            'user_id' => auth()->id(),
            'location' => $request->location,
            'area' => $request->area,
            'area_unit' => $request->area_unit, // Store as ID
            'image' => $image,
            'certificate' => $certificate,
            'demarcation' => is_array($request->demarcation)
                ? json_encode($request->demarcation)
                : $request->demarcation,
        ]);

        return response()->json(['message' => 'Land added successfully', 'land' => $land], 200);
    }

    public function getAreaUnits()
    {
        $user = Auth::user();
        return response()->json(AreaUnit::all());
    }

    public function showLands(Request $request)
    {
        $user = Auth::user();

        $page = $request->input('page', 1);
        $perPage = $request->input('limit', 10); // Default: 10 items per page
        $offSet = ($page - 1) * $perPage;

        $lands = Land::where('user_id', $user->id)
            ->select('id', 'location', 'area_unit', 'image', 'area', 'certificate', 'demarcation')
            ->offset($offSet)
            ->limit($perPage)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($land) {
                return [
                    'id' => $land->id,
                    'location' => $land->location,
                    'area_unit' => $land->area_unit,
                    'image' => $land->image,
                    'area' => $land->area,
                    'certificate' => $land->certificate,
                    'demarcation' => json_decode($land->demarcation, true),
                ];
            });

        return response()->json([
            'data' => $lands,
        ], 200);
    }

    public function landrecord(Request $request)
    {
        $user = Auth::user();
        $land = CropInsurance::create([
            'user_id' => Auth::id(),
            'district_id' => $request->district_id,
            'tehsil_id' => $request->tehsil_id,
            'uc' => $request->uc,
            'uc_id' => $request->uc_id,
            'village' => $request->village,
            'village_id' => $request->village_id,
            'village_latitude' => $request->village_latitude,
            'village_longitude' => $request->village_longitude,
            'other' => $request->other,
        ]);

        return response()->json([
            'message' => 'Land record saved successfully',
            'data' => $land
        ], 200);
    }

    public function getLandRecord()
    {
        $user = Auth::user();

        // If you're saving user_id in CropInsurance, filter by user
        $records = CropInsurance::with([
            'district:id,name',
            'tehsil:id,name'
        ])
            ->select('district_id', 'tehsil_id', 'uc', 'village', 'village_latitude', 'village_longitude', 'other')
            ->where('user_id', $user->id) 
            ->get();

        if ($records->isEmpty()) {
            return response()->json([
                'message' => 'No land records found',
            ], 404);
        }
        $formatted = $records->map(function ($record) {
            return [
                'district_id' => $record->district_id,
                'tehsil_id' => $record->tehsil_id,
                'district_name' => $record->district->name ?? null,
                'tehsil_name' => $record->tehsil->name ?? null,
                'uc' => $record->uc,
                'village' => $record->village,
                'village_latitude' => $record->village_latitude,
                'village_longitude' => $record->village_longitude,
                'other' => $record->other,
            ];
        });

        return response()->json([
            'message' => 'Land records retrieved successfully',
            'data' => $formatted,
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
        if ($tehsils->isEmpty()) {
            return response()->json([
                'message' => 'No tehsil found.',
            ], 404);
        }
        return response()->json($tehsils);
    }

    public function getUCs($tehsil_id)
    {
        $ucs = Uc::where('tehsil_id', $tehsil_id)->select('id', 'name')->get();

        if ($ucs->isEmpty()) {
            return response()->json([
                'message' => 'No UC found.',
            ], 404);
        }
        return response()->json($ucs);
    }

    public function getVillages($uc_id)
    {
        $villages = Village::where('uc_id', $uc_id)->select('id', 'name', 'latitude', 'longitude')->get();

        if ($villages->isEmpty()) {
            return response()->json([
                'message' => 'No village found.',
            ], 404);
        }

        return response()->json($villages);
    }
}
