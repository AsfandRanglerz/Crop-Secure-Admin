<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuthorizedDealer;
use App\Models\DealerItem;
use App\Models\InsuranceClaimAddress;
use App\Models\InsuranceHistory;
use App\Models\InsuranceProductClaim;
use App\Models\Item;
use Illuminate\Http\Request;

class InsuranceClaimController extends Controller
{
    public function submitClaim(Request $request)
    {
        $farmer = auth()->user();
        // return response()->json([
        //                 'message' => $farmer
        //             ], 404);
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
        } elseif ((int) $insurance->insurance_type_id === 8) {
            if ($insurance->compensation_amount) {
                $maxCompensation = $insurance->compensation_amount;
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

    public function claimProducts(Request $request)
    {
        $farmer = auth()->user();

        if (!$farmer || !\App\Models\Farmer::find($farmer->id)) {
            return response()->json(['message' => 'Authenticated user not found in farmers table'], 401);
        }

        // Get insurance
        $insurance = InsuranceHistory::where('id', $request->insurance_id)
            ->where('user_id', $farmer->id)
            ->first();

        if (!$insurance) {
            return response()->json(['message' => 'Insurance not found'], 404);
        }

        if ($insurance->claimed_at !== null) {
            return response()->json(['message' => 'This insurance has already been claimed.'], 400);
        }

        // Get and decode products
        $products = $request->input('products');
        if (is_string($products)) {
            $products = json_decode($products, true);
        }

        if (!is_array($products)) {
            return response()->json(['message' => 'Invalid products format. Expected array.'], 422);
        }

        // Calculate total cost of current claim
        $productTotal = collect($products)->sum(function ($product) {
            return $product['price'] * $product['quantity'];
        });

        // Check existing claims
        $alreadyClaimed = InsuranceProductClaim::where('insurance_id', $insurance->id)->sum('price');
        $availableAmount = $insurance->compensation_amount - $alreadyClaimed;

        // Save or update address
        InsuranceClaimAddress::updateOrCreate(
            ['user_id' => $farmer->id],
            [
                'state' => $request->state,
                'city' => $request->city,
                'address' => $request->address
            ]
        );

        // Create claim record
        InsuranceProductClaim::create([
            'insurance_id'     => $insurance->id,
            'user_id'          => $farmer->id,
            'products'         => json_encode($products, JSON_UNESCAPED_UNICODE),
            'state'            => $request->state,
            'address'          => $request->address,
            'city'             => $request->city,
            'delivery_status'  => 'pending',
            'price'            => $productTotal,
        ]);

        // Update remaining amount
        $totalClaimed = $alreadyClaimed + $productTotal;
        $newRemaining = max(0, $insurance->compensation_amount - $totalClaimed);

        $insurance->update(['remaining_amount' => $newRemaining]);

        return response()->json([
            'message' => 'Product claim recorded successfully',
            'product_total' => $productTotal,
            'remaining_amount' => $newRemaining,
        ]);
    }


    public function getAvailableDealerProductsForClaim(Request $request)
    {
        $farmer = auth()->user();

        $insurance = \App\Models\InsuranceHistory::where('user_id', $farmer->id)
            ->latest()
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

        $districtId = $insurance->district_id;

        $dealerIds = \App\Models\AuthorizedDealer::where('district_id', $districtId)
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
            ->where('status', 1)
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
                'status',
                'bill_image'
            ])
            ->map(function ($claim) {
                // Convert status to human-readable form
                switch ($claim->status) {
                    case 'completed':
                        $claim->status_label = 'Completed';
                        break;
                    case 'rejected':
                        $claim->status_label = 'Rejected';
                        break;
                    case 'pending':
                    default:
                        $claim->status_label = 'Pending';
                        break;
                }

                // Append full bill_image URL if needed (optional)
                if ($claim->bill_image) {
                    $claim->bill_image_url = 'public/' . $claim->bill_image;
                } else {
                    $claim->bill_image_url = null;
                }

                return $claim;
            });

        return response()->json([
            'data' => $myClaims,
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

    public function getClaimedProductAddresses(Request $request)
    {
        $farmer = auth()->user();

        $address = \App\Models\InsuranceClaimAddress::where('user_id', $farmer->id)->first();

        if (!$address) {
            return response()->json([
                'message' => 'No address found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Claim address retrieved successfully.',
            'data' => $address
        ]);
    }



    public function myOrders(Request $request)
    {
        $farmer = auth()->user();

        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('limit', 10);
        $offset = ($page - 1) * $perPage;

        $total = \App\Models\InsuranceProductClaim::whereHas('insurance', function ($query) use ($farmer) {
            $query->where('user_id', $farmer->id);
        })->count();

        $orders = \App\Models\InsuranceProductClaim::whereHas('insurance', function ($query) use ($farmer) {
            $query->where('user_id', $farmer->id);
        })
            ->latest()
            ->skip($offset)
            ->take($perPage)
            ->get();

        // Fetch address for user once
        $address = \App\Models\InsuranceClaimAddress::where('user_id', $farmer->id)->first();

        $response = $orders->map(function ($order) use ($address) {
            $insurance = $order->insurance;

            return [
                'id' => $order->id,
                'crop' => $insurance->crop ?? 'N/A',
                'insurance_type' => $insurance->insurance_type ?? 'N/A',
                'claim_amount' => $insurance->remaining_amount ?? $insurance->compensation_amount,
                'land' => $insurance->land,
                'products' => json_decode($order->products, true),
                'Total_price' => $order->price,
                'delivery_status' => $order->delivery_status,
                'city' => $address->city ?? 'N/A',
                'address' => $address->address ?? 'N/A',
                'state' => $address->state ?? 'N/A',
                'created_at' => $order->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'data' => $response,
        ]);
    }
}
