<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InsuranceHistory;
use Illuminate\Support\Facades\Log;

class InsuranceController extends Controller
{
    public function user()
    {
        return response()->json([
            'name' => 'ijaz hussain'

        ]);
    }

    public function store(Request $request)
    {

        $user = auth()->user();

        // Validate incoming data (all foreign keys should be IDs now)
        $validatedData = $request->validate([
            'crop' => 'nullable',
            'crop_id' => 'required|integer',
            'area_unit' => 'required|string',
            'area' => 'required|numeric',
            'insurance_type' => 'nullable',
            'insurance_type_id' => 'required|integer',
            'district_id' => 'required|integer',
            'tehsil_id' => 'required|integer',
            'company' => 'required|string',
            'farmer_name' => 'required|string',
            'premium_price' => 'required|numeric',
            'sum_insured' => 'required|numeric',
            'payable_amount' => 'required|numeric',
            'land' => 'required|string',
            'benchmark' => 'required|numeric',
            'benchmark_price' => 'required|numeric'
        ]);

        // Generate receipt number
        $currentYear = date('y');
        $lastReceipt = InsuranceHistory::whereYear('created_at', date('Y'))
            ->orderBy('receipt_number', 'desc')
            ->first();

        $nextReceiptNumber = $lastReceipt ? intval(substr($lastReceipt->receipt_number, -2)) + 1 : 1;
        $receiptNumber = sprintf('%s-%02d', $currentYear, $nextReceiptNumber);

        // Store the insurance history
        $insurance = InsuranceHistory::create(array_merge($validatedData, [
            'user_id' => $user->id,
            'receipt_number' => $receiptNumber
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

        $query = \App\Models\InsuranceHistory::where('user_id', $user->id)->orderByDesc('created_at');;
        $total = $query->count();
        $records = $query->offset($offset)->limit($perPage)->get();

        $results = [];

        foreach ($records as $insurance) {
            $base = $insurance->toArray();
            $compInfo = [
                'status' => 'not calculated',
                'compensation' => 0,
            ];

            // 🔹 Add insurance time period from EnsuredCropName
            $ensuredCrop = \App\Models\EnsuredCropName::find($insurance->crop_id);
            $base['insurance_start_time'] = $ensuredCrop?->insurance_start_time;
            $base['insurance_end_time']   = $ensuredCrop?->insurance_end_time;

            // ✅ Area Yield Index
            if ((int) $insurance->insurance_type_id === 11) {
                $sub = \App\Models\InsuranceSubType::where('incurance_type_id', $insurance->insurance_type_id)
                    ->latest()
                    ->first();

                if ($sub && $sub->current_yield !== null) {
                    $benchmark = $insurance->benchmark;
                    $area = $insurance->area;
                    $sumInsured = $insurance->sum_insured;
                    $loss = $benchmark - $sub->current_yield;

                    $comp = $loss > 0 ? ($loss / 100) * $sumInsured : 0;

                    $compInfo = [
                        'type' => 'Area Yield Index',
                        'current_yield' => $sub->current_yield,
                        'benchmark' => $benchmark,
                        'sum_insured' => $sumInsured,
                        'compensation' => round($comp, 2),
                        'remaining_amount' => round($comp, 2),
                        'status' => $comp > 0 ? 'loss' : 'no loss',
                    ];
                }
            }

            // ✅ Production Price Based
            elseif ((int) $insurance->insurance_type_id === 12) {
                $sub = \App\Models\InsuranceSubType::where('incurance_type_id', $insurance->insurance_type_id)
                    ->latest()
                    ->first();

                if (
                    $sub &&
                    $sub->cost_of_production !== null &&
                    $sub->average_yield !== null &&
                    $sub->real_time_market_price !== null &&
                    $sub->ensured_yield !== null &&
                    $insurance->benchmark !== null // ← benchmark/trigger price from insurance history
                ) {
                    // Step 1: Calculate Break-even Price (BEP)
                    $bep = $sub->cost_of_production / $sub->average_yield;

                    // Step 2: Use farmer-selected trigger price (benchmark)
                    $triggerPrice = $insurance->benchmark;

                    // Step 3: Calculate PPI
                    $ppi = ($sub->real_time_market_price / $bep) * 100;

                    // Step 4: Check for trigger event
                    $comp = 0;
                    if ($sub->real_time_market_price < $triggerPrice) {
                        $comp = $sub->ensured_yield * ($triggerPrice - $sub->real_time_market_price) * $insurance->area;
                    }

                    $compInfo = [
                        'type' => 'Production Price Index',
                        'cost_of_production' => $sub->cost_of_production,
                        'average_yield' => $sub->average_yield,
                        'break_even_price' => round($bep, 2),
                        'trigger_price' => round($triggerPrice, 2),
                        'real_time_price' => $sub->real_time_market_price,
                        'ppi' => round($ppi, 2) . '%',
                        'ensured_yield' => $sub->ensured_yield,
                        'compensation' => round($comp, 2),
                        'remaining_amount' => round($comp, 2),
                        'status' => $sub->real_time_market_price < $triggerPrice ? 'loss' : 'no loss',
                    ];
                }
            }


            // ✅ Satellite Index
            elseif ((int) $insurance->insurance_type_id === 13) {
                $ndvi = \App\Models\InsuranceSubTypeSatelliteNDVI::where('insurance_type_id', $insurance->insurance_type_id)
                    ->latest('date')
                    ->first();

                if ($ndvi) {
                    $threshold = 0.4;
                    $comp = $ndvi->ndvi < $threshold
                        ? $insurance->sum_insured * $insurance->area
                        : 0;

                    $compInfo = [
                        'type' => 'Satellite Index',
                        'ndvi' => $ndvi->ndvi,
                        'ndvi_date' => $ndvi->date,
                        'threshold' => $threshold,
                        'compensation' => round($comp, 2),
                        'remaining_amount' => round($comp, 2),
                        'status' => $comp > 0 ? 'loss' : 'no loss',
                    ];
                }
            }

            $base['compensation_info'] = $compInfo;
            $results[] = $base;
        }

        return response()->json([
            'data' => $results,
        ]);
    }
}
