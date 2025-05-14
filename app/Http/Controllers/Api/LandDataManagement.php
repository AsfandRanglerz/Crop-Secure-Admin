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
   

    // TEHSILS
    public function getTehsils($district_id)
    {
        return response()->json(Tehsil::where('district_id', $district_id)->orderBy('name')->get());
    }

   
    // UCs
    public function getUcs($tehsil_id)
    {
        return response()->json(Uc::where('tehsil_id', $tehsil_id)->orderBy('name')->get());
    }

    
    // VILLAGES
    public function getVillages($uc_id)
    {
        return response()->json(Village::where('uc_id', $uc_id)->orderBy('name')->get());
    }

    
}
