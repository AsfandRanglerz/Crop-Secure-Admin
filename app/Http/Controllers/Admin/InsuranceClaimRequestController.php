<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\InsuranceHistory;
use App\Models\InsuranceProductClaim;
use Illuminate\Support\Facades\Auth;

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
            ->where(function ($q) {
                $q->where('is_claim_seen', 0)->orWhereNull('is_claim_seen');
            })
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

        $imagePath = $request->file('bill_image')->store('bill_screenshots', 'public');

        $claim->status = 'approved';
        $claim->bill_image = $imagePath;
        $claim->save();

        return redirect()->back()->with('success', 'Claim approved with bill image uploaded.');
    }

    public function reject($id)
    {
        $claim = InsuranceHistory::findOrFail($id);
        $claim->status = 'rejected';
        $claim->save();

        return redirect()->back()->with('success', 'Claim rejected.');
    }

    public function buyProduct()
    {
        InsuranceProductClaim::where('is_seen', false)
            ->update(['is_seen' => true]);

        $claims = InsuranceProductClaim::with(['insurance.user', 'dealer', 'item'])->latest()->paginate(20);

        return view('admin.product_claims.index', compact('claims'));
    }


    public function destroy() {}
}
