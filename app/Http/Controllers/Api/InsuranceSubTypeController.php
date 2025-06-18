<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyInsuranceType;
use App\Models\InsuranceSubType;
use Illuminate\Http\Request;

class InsuranceSubTypeController extends Controller
{
    public function calculateLatest(Request $request)
{
    $request->validate([
        'name' => 'required', // crop name
        'district_name' => 'required',
        'tehsil_id' => 'required',
        'year' => 'required|numeric',
    ]);

    // Get latest InsuranceSubType record
    $latest = InsuranceSubType::where('name', $request->name)
        ->where('district_name', $request->district_name)
        ->where('tehsil_id', $request->tehsil_id)
        ->where('year', $request->year)
        ->latest()
        ->first();

    if (!$latest) {
        return response()->json(['message' => 'No current yield found'], 404);
    }

    // Get benchmark & insured sum from CropInsurance
    $insurance = CompanyInsuranceType::where('crop', $request->name)
        ->where('district_id', $request->district_name) // if district_name is ID
        ->where('tehsil_id', $request->tehsil_id)
        ->where('year', $request->year)
        ->first();

    if (!$insurance) {
        return response()->json(['message' => 'No benchmark found'], 404);
    }

    // Calculate
    $benchmark = $insurance->benchmark_percent;
    $sumInsuredBase = $insurance->sum_insured_100_percent;
    $area = $insurance->area ?? 1; // default if area not available
    $sumInsured = ($benchmark / 100) * ($sumInsuredBase * $area);

    $lossPercentage = $benchmark - $latest->current_yield;
    $compensation = $lossPercentage > 0 ? ($lossPercentage / 100) * $sumInsured : 0;
    $status = $lossPercentage > 0 ? 'loss' : 'no loss';

    return response()->json([
        'crop' => $latest->name,
        'district' => $latest->district_name,
        'tehsil_id' => $latest->tehsil_id,
        'year' => $latest->year,
        'current_yield' => $latest->current_yield,
        'benchmark' => $benchmark,
        'sum_insured_base' => $sumInsuredBase,
        'sum_insured' => $sumInsured,
        'compensation' => $compensation,
        'status' => $status
    ]);
}

}
