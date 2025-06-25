<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\InsuranceHistory;
use Illuminate\Support\Facades\Auth;

class InsuranceClaimRequestController extends Controller
{
    public function index()
    {
        $sideMenuName = [];
        $sideMenuPermissions = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
            $sideMenuPermissions = $subAdminData['sideMenuPermissions'];
        }

        InsuranceHistory::whereNotNull('claimed_at')
            ->where(function ($q) {
                $q->where('is_claim_seen', 0)->orWhereNull('is_claim_seen');
            })
            ->where('status', 'pending')
            ->update(['is_claim_seen' => 1]);

        $insuranceClaims = InsuranceHistory::with(['farmer.bankDetail', 'insuranceType'])
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
        $claim->bill_image = $imagePath; // Assuming you added this column in your DB
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


    public function destroy() {}
}
