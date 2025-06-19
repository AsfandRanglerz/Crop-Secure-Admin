<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CropInsurance;
use App\Models\InsuranceSubType;
use Illuminate\Http\Request;

class InsuranceResultController extends Controller
{
    public function user()
    {
        return response()->json([
            'name' => 'ijaz hussain'

        ]);
    }

    public function latestAreaYieldCompensation(Request $request)
    {
        // $request->validate([
        //     'user_id' => 'required|exists:users,id',
        // ]);

        // Get user's latest CropInsurance (assuming 1 record per crop/tehsil/year)
        $insurance = CropInsurance::where('user_id', $request->user_id)
            ->where('insurance_type', 'Area Yield Index')
            ->with('insuranceSubType') // Add relationship if not defined
            ->latest('year')
            ->first();

        if (!$insurance) {
            return response()->json([
                'message' => 'No area yield insurance record found for user.'
            ], 404);
        }

        // Get current yield data
        $subType = InsuranceSubType::where('incurance_type_id', $insurance->insurance_type_id)
            ->where('district_name', $insurance->district_id)
            ->where('tehsil_id', $insurance->tehsil_id)
            ->where('name', $insurance->crop) // optional if using name
            ->where('year', $insurance->year)
            ->latest()
            ->first();

        if (!$subType || !$subType->current_yield) {
            return response()->json([
                'message' => 'No current yield data available yet.',
            ], 404);
        }

        $benchmark = $insurance->benchmark_percent;
        $area = $insurance->area;
        $sumInsuredBase = $insurance->sum_insured_100_percent;
        $sumInsured = ($benchmark / 100) * ($sumInsuredBase * $area);
        $lossPercentage = $benchmark - $subType->current_yield;

        $compensation = 0;
        $status = 'no loss';
        if ($lossPercentage > 0) {
            $compensation = ($lossPercentage / 100) * $sumInsured;
            $status = 'loss';
        }

        return response()->json([
            'user_id' => $request->user_id,
            'crop' => $insurance->crop,
            'year' => $insurance->year,
            'area' => $area,
            'benchmark' => $benchmark,
            'current_yield' => $subType->current_yield,
            'sum_insured' => $sumInsured,
            'compensation' => round($compensation, 2),
            'status' => $status,
        ]);
    }

    public function latestProductionPriceCompensation(Request $request)
    {
        // Validate input
        // $request->validate([
        //     'user_id' => 'required|exists:users,id',
        // ]);

        // Get latest production price insurance record for the user
        $insurance = CropInsurance::where('user_id', $request->user_id)
            ->where('insurance_type', 'Production Price Based')
            ->latest('year')
            ->first();

        if (!$insurance) {
            return response()->json([
                'message' => 'No production price insurance record found for user.'
            ], 404);
        }

        // Fetch related InsuranceSubType data
        $subType = InsuranceSubType::where('incurance_type_id', $insurance->insurance_type_id)
            ->where('district_name', $insurance->district_id)
            ->where('tehsil_id', $insurance->tehsil_id)
            ->where('crop_name_id', $insurance->crop_id)
            ->where('year', $insurance->year)
            ->latest()
            ->first();

        if (!$subType || !$subType->real_time_market_price) {
            return response()->json([
                'message' => 'No real-time price data available yet.'
            ], 404);
        }

        // Calculation logic
        $avgPrice = $subType->historical_average_market_price;
        $realTimePrice = $subType->real_time_market_price;
        $yield = $subType->ensured_yield;
        $area = $insurance->area;

        $priceDiff = $avgPrice - $realTimePrice;
        $compensation = 0;
        $status = 'no loss';

        if ($priceDiff > 0) {
            $compensation = $priceDiff * $yield * $area;
            $status = 'loss';
        }

        return response()->json([
            'user_id' => $request->user_id,
            'crop' => $insurance->crop,
            'year' => $insurance->year,
            'district' => $insurance->district_id,
            'tehsil' => $insurance->tehsil_id,
            'ensured_yield' => $yield,
            'historical_avg_price' => $avgPrice,
            'real_time_price' => $realTimePrice,
            'area' => $area,
            'price_difference' => $priceDiff,
            'compensation' => round($compensation, 2),
            'status' => $status,
        ]);
    }


    public function latestNDVICompensation(Request $request)
    {
        // $request->validate([
        //     'user_id' => 'required|exists:users,id',
        // ]);

        // Step 1: Get user's latest NDVI insurance record
        $insurance = CropInsurance::where('user_id', $request->user_id)
            ->where('insurance_type', 'Satellite Index')
            ->latest('year')
            ->first();

        if (!$insurance) {
            return response()->json([
                'message' => 'No satellite index insurance record found for user.'
            ], 404);
        }

        // Step 2: Fetch latest NDVI record
        $latestNDVI = \App\Models\InsuranceSubTypeSatelliteNDVI::where('insurance_type_id', $insurance->insurance_type_id)
            ->orderBy('date', 'desc')
            ->first();

        if (!$latestNDVI) {
            return response()->json([
                'message' => 'No NDVI data available yet.'
            ], 404);
        }

        // Step 3: Calculate compensation
        $ndviThreshold = 0.4;
        $ndvi = $latestNDVI->ndvi;
        $area = $insurance->area;
        $sumInsuredBase = $insurance->sum_insured_100_percent;

        $status = 'no loss';
        $compensation = 0;

        if ($ndvi < $ndviThreshold) {
            $status = 'loss';
            $compensation = $sumInsuredBase * $area;
        }

        return response()->json([
            'user_id' => $request->user_id,
            'crop' => $insurance->crop,
            'year' => $insurance->year,
            'area' => $area,
            'sum_insured' => $sumInsuredBase,
            'ndvi' => $ndvi,
            'threshold' => $ndviThreshold,
            'compensation' => $compensation,
            'status' => $status,
            'date_of_reading' => $latestNDVI->date,
        ]);
    }
}
