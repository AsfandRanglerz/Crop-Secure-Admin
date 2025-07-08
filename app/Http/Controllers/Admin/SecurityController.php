<?php

namespace App\Http\Controllers\admin;

use App\Models\AboutUs;
use Illuminate\Http\Request;
use App\Models\PrivacyPolicy;
use App\Models\TermCondition;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AdminController;

class SecurityController extends Controller
{
    public function PrivacyPolicy()
    {
        $data = PrivacyPolicy::first();
        $sideMenuName = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
        }

        return view('admin.privacyPolicy.index', compact('data', 'sideMenuName'));
    }
    public function PrivacyPolicyEdit()
    {
        $data = PrivacyPolicy::first();
        
        return view('admin.privacyPolicy.edit', compact('data'));
    }
    public function PrivacyPolicyUpdate(Request $request)
    {
        $request->validate([
            'description' => 'required'
        ]);

        $data = PrivacyPolicy::first();
        // PrivacyPolicy::find($data->id)->update($request->all());
        if ($data) {
            $data->update($request->all());
        } else {
            PrivacyPolicy::create($request->all());
        }
        return redirect('/admin/privacy-policy')->with(['status' => true, 'message' => 'Privacy Policy Updated Successfully']);
    }
    public function TermCondition()
    {
        $data = TermCondition::first();
        $sideMenuName = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
        }
        
        return view('admin.termCondition.index', compact('data', 'sideMenuName'));
    }
    public function TermConditionEdit()
    {
        $data = TermCondition::first();
        return view('admin.termCondition.edit', compact('data'));
    }
    public function TermConditionUpdate(Request $request)
    {
        $request->validate([
            'description' => 'required'
        ]);
        // dd($request);
        $data = TermCondition::first();
        // TermCondition::find($data->id)->update($request->all());
        if ($data) {
            $data->update($request->all());
        } else {
            TermCondition::create($request->all());
        }
        return redirect('/admin/term-condition')->with(['status' => true, 'message' => 'Terms & Conditions Updated Successfully']);
    }

    public function AboutUs()
    {
        $data = AboutUs::first();
        $sideMenuName = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
        }

        return view('admin.aboutUs.index', compact('data', 'sideMenuName'));
    }
    public function AboutUsEdit()
    {
        $data = AboutUs::first();
        return view('admin.aboutUs.edit', compact('data'));
    }
    public function AboutUsUpdate(Request $request)
    {
        $request->validate([
            'description' => 'required'
        ]);
        

        $data = AboutUs::first();
        // AboutUs::find($data->id)->update($request->all());
        if ($data) {
            $data->update($request->all());
        } else {
            AboutUs::create($request->all());
        }
        return redirect('/admin/about-us')->with(['status' => true, 'message' => 'About Us Updated Successfully']);
    }
}
