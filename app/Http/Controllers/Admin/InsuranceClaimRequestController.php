<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\InsuranceHistory;
use App\Models\InsuranceProductClaim;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ClaimNotificationHelper;
use App\Helpers\ProductClaimNotificationHelper;
use App\Models\DealerItem;

class InsuranceClaimRequestController extends Controller
{
    public function index()
    {
        $sideMenuName = [];
        $sideMenuPermissions = [];

        // Check for SubAdmin and fetch permissions
        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
            $sideMenuPermissions = $subAdminData['sideMenuPermissions'];
        }

        // Mark unseen insurance claims as seen
        InsuranceHistory::whereNotNull('claimed_at')
            // ->where(function ($q) {
            //     $q->where('is_claim_seen', 0)->orWhereNull('is_claim_seen');
            // })
            ->where('status', 'pending')
            ->update(['is_claim_seen' => 1]);

        // Fetch insurance claims with related models including user bank details
        $insuranceClaims = InsuranceHistory::with([
            'farmer.bankDetail',
            'insuranceType',
            'userBankDetail' // ← include bank details relationship
        ])
            ->whereNotNull('claimed_at')
            ->orderByDesc('claimed_at')
            ->get();

        return view('admin.insurance_claim_request.index', compact('sideMenuPermissions', 'sideMenuName', 'insuranceClaims'));
    }


    public function approve(Request $request, $id)
    {
        $request->validate([
            'bill_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $claim = InsuranceHistory::findOrFail($id);

        if ($request->hasFile('bill_image')) {
            $file = $request->file('bill_image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/bill_screenshots/'), $filename);
            $imagePath = 'uploads/bill_screenshots/' . $filename;
        }

        $claim->status = 'approved';
        $claim->bill_image = $imagePath ?? null;
        $claim->save();

        if ($claim->user) {
            ClaimNotificationHelper::notifyFarmer($claim->user, 'Your claim has been approved.');
        }

        return redirect()->back()->with('success', 'Claim approved with bill image uploaded.');
    }


    public function reject(Request $request, $id)
    {
        // $request->validate([
        //     'description' => 'required|string|max:1000',
        // ]);

        $claim = InsuranceHistory::findOrFail($id);
        $claim->status = 'rejected';
        $claim->rejection_reason = null;
        $claim->save();


        ClaimNotificationHelper::notifyFarmer($claim->user, 'Your claim has been rejected.');

        return redirect()->back()->with('success', 'Claim rejected.');
    }


    public function buyProduct()
    {
        $sideMenuName = [];
        $sideMenuPermissions = [];

        // Check for SubAdmin and fetch permissions
        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
            $sideMenuPermissions = $subAdminData['sideMenuPermissions'];
        }

        \App\Models\InsuranceProductClaim::where('delivery_status', 'pending')
            // ->where('is_seen', false)
            ->update(['is_seen' => true]);


        $claims = InsuranceProductClaim::with(['insurance.user.claimAddress', 'dealer', 'item'])->latest()->paginate(20);

        return view('admin.product_claims.index', compact('sideMenuName', 'sideMenuPermissions', 'claims'));
    }

    // public function approveProduct($id)
    // {
    //     $claim = InsuranceProductClaim::with('insurance.user')->findOrFail($id);
    //     $claim->delivery_status = 'approved';
    //     $claim->save();

    //     $farmer = $claim->user;
    //     if ($farmer) {
    //         ProductClaimNotificationHelper::notifyFarmer($farmer, 'Your product claim has been approved.');
    //     }

    //     return redirect()->back()->with('success', 'Product Claim accepted.');
    // }

    public function approveProduct($id)
    {
        $claim = InsuranceProductClaim::with('insurance.user')->findOrFail($id);
        $claim->delivery_status = 'approved';
        $claim->save();

        // ✅ Step 2: Decode the JSON stored in `products` column
        $productData = json_decode($claim->products, true);

        // ✅ Step 3: Loop through each purchased product
        if (!empty($productData)) {
            foreach ($productData as $product) {
                $dealerId = $product['dealer_id'] ?? null;
                $itemId = $product['id'] ?? null;
                $purchasedQty = $product['quantity'] ?? 0;

                if ($dealerId && $itemId && $purchasedQty > 0) {
                    // ✅ Step 4: Find the dealer item record
                    $dealerItem = DealerItem::where('authorized_dealer_id', $dealerId)
                        ->where('item_id', $itemId)
                        ->first();

                    if ($dealerItem) {
                        // ✅ Step 5: Subtract the quantity
                        $dealerItem->quantity = max(0, $dealerItem->quantity - $purchasedQty);
                        $dealerItem->save();
                    }
                }
            }
        }

        // ✅ Notify Farmer
        $farmer = $claim->insurance->user;
        if ($farmer) {
            ProductClaimNotificationHelper::notifyFarmer($farmer, 'Your product claim has been approved.');
        }

        return redirect()->back()->with('success', 'Product Claim accepted and dealer stock updated.');
    }


    public function rejectProduct($id)
    {
        $claim = InsuranceProductClaim::with('insurance.user')->findOrFail($id);
        $claim->delivery_status = 'rejected';
        $claim->save();

        $farmer = $claim->user;
        if ($farmer) {
            ProductClaimNotificationHelper::notifyFarmer($farmer, 'Your product claim has been rejected.');
        }

        return redirect()->back()->with('success', 'Product Claim rejected.');
    }



    public function destroy() {}
}
