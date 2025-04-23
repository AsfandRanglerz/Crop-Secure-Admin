<?php

namespace App\Http\Controllers\Api;

use App\Models\Land;
use App\Models\AreaUnit;
use Illuminate\Http\Request;
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

}
