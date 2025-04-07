<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\District;
use App\Models\Tehsil;
use App\Models\Uc;
use App\Models\Village;

class LandDataManagement extends Controller
{
    public function getDistricts()
    {
        return response()->json(District::orderBy('name')->get());
    }

    public function createDistrict(Request $request)
    {
        $request->validate(['name' => 'required|string|unique:districts,name']);
        $district = District::create(['name' => $request->name]);
        return response()->json(['message' => 'District Created', 'data' => $district]);
    }

    public function updateDistrict(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|unique:districts,name,' . $id]);
        $district = District::findOrFail($id);
        $district->update(['name' => $request->name]);
        return response()->json(['message' => 'District Updated', 'data' => $district]);
    }

    public function deleteDistrict($id)
    {
        District::destroy($id);
        return response()->json(['message' => 'District Deleted']);
    }

    // TEHSILS
    public function getTehsils($district_id)
    {
        return response()->json(Tehsil::where('district_id', $district_id)->orderBy('name')->get());
    }

    public function createTehsil(Request $request)
    {
        $request->validate([
            'district_id' => 'required|exists:districts,id',
            'name' => 'required|string|unique:tehsils,name'
        ]);
        $tehsil = Tehsil::create($request->only('district_id', 'name'));
        return response()->json(['message' => 'Tehsil Created', 'data' => $tehsil]);
    }

    public function updateTehsil(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|unique:tehsils,name,' . $id]);
        $tehsil = Tehsil::findOrFail($id);
        $tehsil->update(['name' => $request->name]);
        return response()->json(['message' => 'Tehsil Updated', 'data' => $tehsil]);
    }

    public function deleteTehsil($id)
    {
        Tehsil::destroy($id);
        return response()->json(['message' => 'Tehsil Deleted']);
    }

    // UCs
    public function getUcs($tehsil_id)
    {
        return response()->json(Uc::where('tehsil_id', $tehsil_id)->orderBy('name')->get());
    }

    public function createUc(Request $request)
    {
        $request->validate([
            'tehsil_id' => 'required|exists:tehsils,id',
            'name' => 'required|string|unique:ucs,name'
        ]);
        $uc = Uc::create($request->only('tehsil_id', 'name'));
        return response()->json(['message' => 'UC Created', 'data' => $uc]);
    }

    public function updateUc(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|unique:ucs,name,' . $id]);
        $uc = Uc::findOrFail($id);
        $uc->update(['name' => $request->name]);
        return response()->json(['message' => 'UC Updated', 'data' => $uc]);
    }

    public function deleteUc($id)
    {
        Uc::destroy($id);
        return response()->json(['message' => 'UC Deleted']);
    }

    // VILLAGES
    public function getVillages($uc_id)
    {
        return response()->json(Village::where('uc_id', $uc_id)->orderBy('name')->get());
    }

    public function createVillage(Request $request)
    {
        $request->validate([
            'uc_id' => 'required|exists:ucs,id',
            'name' => 'required|string|unique:villages,name'
        ]);
        $village = Village::create($request->only('uc_id', 'name'));
        return response()->json(['message' => 'Village Created', 'data' => $village]);
    }

    public function updateVillage(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|unique:villages,name,' . $id]);
        $village = Village::findOrFail($id);
        $village->update(['name' => $request->name]);
        return response()->json(['message' => 'Village Updated', 'data' => $village]);
    }

    public function deleteVillage($id)
    {
        Village::destroy($id);
        return response()->json(['message' => 'Village Deleted']);
    }
}
