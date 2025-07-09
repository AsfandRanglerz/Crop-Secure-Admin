<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EnsuredCrop;
use App\Models\InsuranceCompany;
use App\Models\InsuranceHistory;
use App\Models\InsuranceType;

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

    public function index(Request $request)
    {
        $sideMenuName = [];
        $sideMenuPermissions = [];

        if (Auth::guard('subadmin')->check()) {
            $adminController = new AdminController();
            $subAdminData = $adminController->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
            $sideMenuPermissions = $subAdminData['sideMenuPermissions'];
        }

        InsuranceHistory::where(function ($query) {
            $query->where('is_seen', 0)->orWhereNull('is_seen');
        })->update(['is_seen' => 1]);

        // Filters
        $historiesQuery = InsuranceHistory::with('farmerLands')->orderBy('created_at', 'desc');

        if ($request->filled('year')) {
            $historiesQuery->whereYear('created_at', $request->year);
        }

        if ($request->filled('insurance_type')) {
            $historiesQuery->where('insurance_type', $request->insurance_type);
        }

        if ($request->filled('company')) {
            $historiesQuery->where('company', $request->company);
        }

        $histories = $historiesQuery->get();

    $totalPayableAmount = $histories->sum('payable_amount');

        // Correct filter sources
        $years = InsuranceHistory::selectRaw('YEAR(created_at) as year')->distinct()->pluck('year');
        $insuranceTypes = InsuranceType::pluck('name');
        $companies = InsuranceCompany::pluck('name');

        return view('admin.insurance_histories.index', compact(
            'sideMenuPermissions',
            'sideMenuName',
            'histories',
            'years',
            'insuranceTypes',
            'companies',
            'totalPayableAmount'
        ));
    }



    public function destroy($id)
    {
        InsuranceHistory::destroy($id);
        return redirect()->route('insurance.history.index')->with('message', 'Insurance History deleted successfully!');
    }
}
