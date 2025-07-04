<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EnsuredCrop;
use App\Models\InsuranceHistory;

class InsuranceHistoryController extends Controller
{
    // public function index()
    // {
    //     $sideMenuName = [];
    //     $sideMenuPermissions = [];

    //     if (Auth::guard('subadmin')->check()) {
    //         $getSubAdminPermissions = new AdminController();
    //         $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
    //         $sideMenuName = $subAdminData['sideMenuName'];
    //         $sideMenuPermissions = $subAdminData['sideMenuPermissions'];
    //     }

    //     $histories = InsuranceHistory::orderBy('created_at', 'desc')->get();

    //     return view('admin.insurance_histories.index', compact('sideMenuPermissions', 'sideMenuName', 'histories'));
    // }

     public function index()
    {
        $sideMenuName = [];
        $sideMenuPermissions = [];

        // Handle SubAdmin permissions
        if (Auth::guard('subadmin')->check()) {
            $adminController = new AdminController();
            $subAdminData = $adminController->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
            $sideMenuPermissions = $subAdminData['sideMenuPermissions'];
        }

        // Mark all unseen insurance history entries as seen
        InsuranceHistory::where(function ($query) {
            $query->where('is_seen', 0)->orWhereNull('is_seen');
        })->update(['is_seen' => 1]);

        // Get insurance history records
        $histories = InsuranceHistory::with('farmerLands')->orderBy('created_at', 'desc')->get();

        return view('admin.insurance_histories.index', compact('sideMenuPermissions', 'sideMenuName', 'histories'));
    }


    public function destroy($id)
    {
        InsuranceHistory::destroy($id);
        return redirect()->route('insurance.history.index')->with('message', 'Insurance History deleted successfully!');
    }
}
