<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuthorizedDealer;
use App\Models\DealerItem;
use App\Models\InsuranceHistory;
use App\Models\Item;
use Illuminate\Http\Request;

class InsuranceClaimController extends Controller
{
    public function submitClaim(Request $request)
    {
        $farmer = auth()->user(); // ya jo bhi model use kar rahe ho

        $insurance = \App\Models\InsuranceHistory::where('id', $request->insurance_id)
            ->where('user_id', $farmer->id)
            ->first();

        if (!$insurance) {
            return response()->json([
                'message' => 'Insurance not found',
            ], 404);
        }

        if ($insurance->claimed_at !== null) {
            return response()->json([
                'message' => 'This insurance has already been claimed.',
            ], 400);
        }

        // ✅ Get or save bank details
        $bankDetails = \App\Models\UserBankDetail::where('user_id', $farmer->id)->first();

        if ($request->has(['bank_holder_name', 'bank_name', 'account_number'])) {
            $request->validate([
                'bank_holder_name' => 'required|string|max:255',
                'bank_name' => 'required|string|max:255',
                'account_number' => 'required|string|max:50',
            ]);

            if ($bankDetails) {
                $bankDetails->update([
                    'bank_holder_name' => $request->bank_holder_name,
                    'bank_name' => $request->bank_name,
                    'account_number' => $request->account_number,
                ]);
            } else {
                $bankDetails = \App\Models\UserBankDetail::create([
                    'user_id' => $farmer->id,
                    'bank_holder_name' => $request->bank_holder_name,
                    'bank_name' => $request->bank_name,
                    'account_number' => $request->account_number,
                ]);
            }
        }

        // ✅ Recalculate max compensation
        $maxCompensation = 0;

        if ((int) $insurance->insurance_type_id === 11) {
            $sub = \App\Models\InsuranceSubType::where('incurance_type_id', 11)->latest()->first();
            if ($sub && $sub->current_yield !== null) {
                $loss = $insurance->benchmark - $sub->current_yield;
                $maxCompensation = $loss > 0 ? ($loss / 100) * $insurance->sum_insured : 0;
            }
        } elseif ((int) $insurance->insurance_type_id === 12) {
            $sub = \App\Models\InsuranceSubType::where('incurance_type_id', 12)->latest()->first();
            if ($sub && $sub->real_time_market_price < $insurance->benchmark) {
                $maxCompensation = $sub->ensured_yield * ($insurance->benchmark - $sub->real_time_market_price) * $insurance->area;
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

        // ✅ Save claim
        $insurance->update([
            'claimed_at' => now(),
            'claimed_amount' => round($maxCompensation, 2),
            'compensation_amount' => round($maxCompensation, 2),
            'remaining_amount' => 0,
            'status' => 'pending',

        ]);

        return response()->json([
            'message' => 'Claim submitted successfully.',
            'data' => [
                'claimed_amount' => round($maxCompensation, 2),
                'claimed_at' => now(),
                'insurance_type_id' => $insurance->insurance_type_id,
                'bank_details' => $bankDetails,
            ],
        ]);
    }

    public function getAvailableDealerProductsForClaim(Request $request)
    {
        $farmer = auth()->user();

        $insurance = \App\Models\InsuranceHistory::where('user_id', $farmer->id)
            ->latest()
            ->first();

        if (!$insurance || !$insurance->district_id) {
            return response()->json([
                'status' => false,
                'message' => 'District information not found in your insurance.',
            ], 400);
        }

        $districtId = $insurance->district_id;

        $dealerIds = \App\Models\AuthorizedDealer::where('district', $districtId)
            ->pluck('id');

        if ($dealerIds->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No dealers found in your selected district.',
            ], 404);
        }

        // Load related item and dealer
        $authorizedItems = \App\Models\DealerItem::with(['item', 'authorizedDealer'])
            ->whereIn('authorized_dealer_id', $dealerIds)
            ->get();

        if ($authorizedItems->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No authorized products found for your district.',
            ], 404);
        }

        // Pagination
        $page = (int)$request->input('page', 1);
        $perPage = (int)$request->input('limit', 10);
        $offset = ($page - 1) * $perPage;

        $paginated = $authorizedItems->slice($offset, $perPage)->values();
        $total = $authorizedItems->count();

        // Format output
        $products = $paginated->map(function ($dealerItem) {
            return [
                'id' => $dealerItem->item->id,
                'name' => $dealerItem->item->name,
                'description' => $dealerItem->item->description,
                'image' => 'public/' . ($dealerItem->item->image ?? 'uploads/items/default.png'),
                'price' => $dealerItem->price,
                'dealer_id' => $dealerItem->authorizedDealer->id ?? 'N/A',
                'dealer_name' => $dealerItem->authorizedDealer->name ?? 'N/A',
            ];
        });

        return response()->json([
            'data' => $products,
            'total' => $total,
            'page' => $page,
            'limit' => $perPage,
        ]);
    }




    public function myClaims(Request $request)
    {
        $user = auth()->user();

        $page = (int)$request->input('page', 1);
        $perPage = (int)$request->input('limit', 10);
        $offset = ($page - 1) * $perPage;

        $query = \App\Models\InsuranceHistory::where('user_id', $user->id)
            ->whereNotNull('claimed_at')
            ->orderByDesc('claimed_at');

        $total = $query->count();

        $myClaims = $query->offset($offset)
            ->limit($perPage)
            ->get([
                'id',
                'insurance_type_id',
                'claimed_amount',
                'compensation_amount',
                'remaining_amount',
                'claimed_at',
                'crop_id',
                'crop',
                'insurance_type',
                'land',
                'sum_insured',
                'status'
            ]);

        return response()->json([
            'data' => $myClaims,
            'total' => $total,
            'page' => $page,
            'limit' => $perPage,
        ]);
    }


    public function getBankDetails()
    {
        $farmer = auth()->user();

        $bankDetails = \App\Models\UserBankDetail::where('user_id', $farmer->id)->first();

        if (!$bankDetails) {
            return response()->json([
                'message' => 'No bank details found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'data' => [
                'bank_holder_name' => $bankDetails->bank_holder_name,
                'bank_name'        => $bankDetails->bank_name,
                'account_number'   => $bankDetails->account_number,
            ],
        ]);
    }
}
