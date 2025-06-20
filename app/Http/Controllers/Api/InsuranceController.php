<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InsuranceHistory;
use Illuminate\Support\Facades\Log;

class InsuranceController extends Controller
{

    public function store(Request $request)
    {
        // Retrieve the currently authenticated user
        $user = auth()->user();

        // Validate the incoming request data
        $validatedData = $request->validate([
            'crop' => 'required|string',
            'area_unit' => 'required|string',
            'area' => 'required|numeric',
            'insurance_type' => 'required|string',
            'district' => 'required|string',
            'tehsil' => 'required|string',
            'company' => 'required|string',
            'farmer_name' => 'required|string',
            'premium_price' => 'required|numeric',
            'sum_insured' => 'required|numeric',
            'payable_amount' => 'required|numeric',
            'land' => 'required',
            'benchmark' => 'required',
            'benchmark_price' => 'required'
        ]);

        // Get the current year and format it
        $currentYear = date('y'); // Get the last two digits of the current year

        // Find the last receipt number for the current year
        $lastReceipt = InsuranceHistory::whereYear('created_at', date('Y'))
            ->orderBy('receipt_number', 'desc')
            ->first();

        // Generate the next receipt number
        $nextReceiptNumber = $lastReceipt ? intval(substr($lastReceipt->receipt_number, -2)) + 1 : 1;

        // Prepare the complete receipt number
        $receiptNumber = sprintf('%s-%02d', $currentYear, $nextReceiptNumber); // Format to YY-MM

        // Create a new insurance history entry
        $insurance = InsuranceHistory::create(array_merge($validatedData, [
            'user_id' => $user->id, // Add the user_id to the insurance data 
            'receipt_number' => $receiptNumber // Add the newly generated receipt number
        ]));

        return response()->json([
            'status' => true,
            'message' => 'Insurance history recorded successfully',
            'data' => $insurance,
        ], 201);
    }

    public function getInsurances(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('limit', 10);
        $offset = ($page - 1) * $perPage;

        $query = \App\Models\InsuranceHistory::where('user_id', $user->id);
        $total = $query->count();
        $records = $query->offset($offset)->limit($perPage)->get();

        // 🐞 DEBUG: check records before looping
        // if ($records->isEmpty()) {
        //     return response()->json([
        //         'status' => true,
        //         'message' => 'No insurance history found for this user.',
        //         'total' => 0,
        //         'page' => $page,
        //         'data' => [],
        //     ]);
        // }

        $results = [];

        foreach ($records as $insurance) {
            $type = $insurance->insurance_type;
            $base = $insurance->toArray();
            $compInfo = [
                'status' => 'not calculated',
                'compensation' => 0,
            ];

            // dd($insurance);

            if ($type === 'Area Yield Index') {
                $sub = \App\Models\InsuranceSubType::where('incurance_type_id', $insurance->insurance_type_id)
                    ->where('district_name', $insurance->district_id)
                    ->where('tehsil_id', $insurance->tehsil_id)
                    ->where('name', $insurance->crop)
                    ->where('year', $insurance->year)
                    ->latest()
                    ->first();

                if ($sub && $sub->current_yield !== null) {
                    $benchmark = $insurance->benchmark_percent;
                    $area = $insurance->area;
                    $sumInsured = ($benchmark / 100) * ($insurance->sum_insured_100_percent * $area);
                    $loss = $benchmark - $sub->current_yield;

                    $comp = $loss > 0 ? ($loss / 100) * $sumInsured : 0;

                    $compInfo = [
                        'type' => 'Area Yield Index',
                        'current_yield' => $sub->current_yield,
                        'benchmark' => $benchmark,
                        'sum_insured' => $sumInsured,
                        'compensation' => round($comp, 2),
                        'status' => $comp > 0 ? 'loss' : 'no loss',
                    ];
                }
            } elseif ($type === 'Production Price Based') {
                $sub = \App\Models\InsuranceSubType::where('incurance_type_id', $insurance->insurance_type_id)
                    ->where('district_name', $insurance->district_id)
                    ->where('tehsil_id', $insurance->tehsil_id)
                    ->where('crop_name_id', $insurance->crop_id)
                    ->where('year', $insurance->year)
                    ->latest()
                    ->first();

                if ($sub && $sub->real_time_market_price !== null) {
                    $diff = $sub->historical_average_market_price - $sub->real_time_market_price;
                    $comp = $diff > 0 ? $diff * $sub->ensured_yield * $insurance->area : 0;

                    $compInfo = [
                        'type' => 'Production Price Based',
                        'historical_price' => $sub->historical_average_market_price,
                        'real_time_price' => $sub->real_time_market_price,
                        'ensured_yield' => $sub->ensured_yield,
                        'price_difference' => $diff,
                        'compensation' => round($comp, 2),
                        'status' => $comp > 0 ? 'loss' : 'no loss',
                    ];
                }
            } elseif ($type === 'Satellite Index') {
                $ndvi = \App\Models\InsuranceSubTypeSatelliteNDVI::where('insurance_type_id', $insurance->insurance_type_id)
                    ->latest('date')
                    ->first();

                if ($ndvi) {
                    $threshold = 0.4;
                    $comp = $ndvi->ndvi < $threshold
                        ? $insurance->sum_insured_100_percent * $insurance->area
                        : 0;

                    $compInfo = [
                        'type' => 'Satellite Index',
                        'ndvi' => $ndvi->ndvi,
                        'ndvi_date' => $ndvi->date,
                        'threshold' => $threshold,
                        'compensation' => round($comp, 2),
                        'status' => $comp > 0 ? 'loss' : 'no loss',
                    ];
                }
            }

            $base['compensation_info'] = $compInfo;
            $results[] = $base;
        }

        return response()->json([
            'total' => $total,
            'page' => $page,
            'data' => $results,
        ]);
    }
}
