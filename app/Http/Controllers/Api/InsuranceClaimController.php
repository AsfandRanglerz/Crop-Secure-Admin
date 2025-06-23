<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InsuranceClaimController extends Controller
{
    public function submitClaim(Request $request)
    {
        // $request->validate([
        //     'insurance_id' => 'required|exists:insurance_histories,id',
        //     'bank_holder_name' => 'required|string|max:255',
        //     'account_name' => 'required|string|max:255',
        //     'account_number' => 'required|string|max:50',
        // ]);

        $user = auth()->user();

        $insurance = \App\Models\InsuranceHistory::where('user_id', $user->id)
            ->firstOrFail();

        if ($insurance->claimed_at !== null) {
            return response()->json([
                'message' => 'This insurance has already been claimed.',
            ], 400);
        }

        // Recalculate full compensation
        $maxCompensation = 0;

        if ((int) $insurance->insurance_type_id === 11) {
            $sub = \App\Models\InsuranceSubType::where('incurance_type_id', 11)->latest()->first();
            if ($sub && $sub->current_yield !== null) {
                $loss = $insurance->benchmark - $sub->current_yield;
                $maxCompensation = $loss > 0 ? ($loss / 100) * $insurance->sum_insured : 0;
            }
        } elseif ((int) $insurance->insurance_type_id === 12) {
            $sub = \App\Models\InsuranceSubType::where('incurance_type_id', 12)->latest()->first();
            if (
                $sub &&
                $sub->cost_of_production !== null &&
                $sub->average_yield !== null &&
                $sub->real_time_market_price !== null &&
                $sub->ensured_yield !== null &&
                $insurance->benchmark !== null
            ) {
                $triggerPrice = $insurance->benchmark;
                if ($sub->real_time_market_price < $triggerPrice) {
                    $maxCompensation = $sub->ensured_yield * ($triggerPrice - $sub->real_time_market_price) * $insurance->area;
                }
            }
        } elseif ((int) $insurance->insurance_type_id === 13) {
            $ndvi = \App\Models\InsuranceSubTypeSatelliteNDVI::where('insurance_type_id', 13)->latest('date')->first();
            if ($ndvi && $ndvi->ndvi < 0.4) {
                $maxCompensation = $insurance->sum_insured * $insurance->area;
            }
        }

        if ($maxCompensation <= 0) {
            return response()->json([
                'message' => 'You are not eligible to claim. No compensation available.',
            ], 400);
        }

        // Save full claim
        $insurance->update([
            'bank_holder_name' => $request->bank_holder_name,
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'claimed_at' => now(),
            'claimed_amount' => round($maxCompensation, 2),  // Full amount claimed
            'compensation_amount' => round($maxCompensation, 2),
        ]);

        return response()->json([
            'message' => 'Claim submitted successfully.',
            'data' => [
                'claimed_amount' => round($maxCompensation, 2),
                'claimed_at' => now(),
                'insurance_type_id' => $insurance->insurance_type_id,
            ],
        ]);
    }

    public function selectProductForClaim(Request $request)
    {
        // $request->validate([
        //     'insurance_id' => 'required|exists:insurance_histories,id',
        //     'product_id' => 'required|exists:products,id',
        // ]);

        $user = auth()->user();

        $insurance = \App\Models\InsuranceHistory::where('user_id', $user->id)
            ->firstOrFail();

        // Prevent duplicate claim
        if ($insurance->claimed_at !== null) {
            return response()->json([
                'message' => 'This insurance has already been claimed.',
            ], 400);
        }

        // Calculate compensation amount again
        $maxCompensation = 0;

        if ((int) $insurance->insurance_type_id === 11) {
            $sub = \App\Models\InsuranceSubType::where('incurance_type_id', 11)->latest()->first();
            if ($sub && $sub->current_yield !== null) {
                $loss = $insurance->benchmark - $sub->current_yield;
                $maxCompensation = $loss > 0 ? ($loss / 100) * $insurance->sum_insured : 0;
            }
        } elseif ((int) $insurance->insurance_type_id === 12) {
            $sub = \App\Models\InsuranceSubType::where('incurance_type_id', 12)->latest()->first();
            if (
                $sub &&
                $sub->cost_of_production !== null &&
                $sub->average_yield !== null &&
                $sub->real_time_market_price !== null &&
                $sub->ensured_yield !== null &&
                $insurance->benchmark !== null
            ) {
                $triggerPrice = $insurance->benchmark;
                if ($sub->real_time_market_price < $triggerPrice) {
                    $maxCompensation = $sub->ensured_yield * ($triggerPrice - $sub->real_time_market_price) * $insurance->area;
                }
            }
        } elseif ((int) $insurance->insurance_type_id === 13) {
            $ndvi = \App\Models\InsuranceSubTypeSatelliteNDVI::where('insurance_type_id', 13)->latest('date')->first();
            if ($ndvi && $ndvi->ndvi < 0.4) {
                $maxCompensation = $insurance->sum_insured * $insurance->area;
            }
        }

        if ($maxCompensation <= 0) {
            return response()->json([
                'message' => 'You are not eligible to claim. No compensation available.',
            ], 400);
        }

        // Fetch product
        $product = \App\Models\Product::findOrFail($request->product_id);

        $remainingAmount = $maxCompensation - $product->price;
        $remainingAmount = $remainingAmount > 0 ? round($remainingAmount, 2) : 0;

        // Update insurance with product claim
        $insurance->update([
            'product_id' => $product->id,
            'claimed_at' => now(),
            'claimed_amount' => $product->price,
            'compensation_amount' => round($maxCompensation, 2),
            'remaining_amount' => $remainingAmount,
        ]);

        return response()->json([
            'message' => 'Product selected successfully.',
            'data' => [
                'claimed_amount' => $product->price,
                'product_name' => $product->name,
                'remaining_amount' => $remainingAmount,
                'insurance_type_id' => $insurance->insurance_type_id,
            ],
        ]);
    }
}
