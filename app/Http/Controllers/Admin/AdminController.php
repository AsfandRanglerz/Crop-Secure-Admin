<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\Admin;
use App\Models\AuthorizedDealer;
use App\Models\EnsuredCrop;
use App\Models\Farmer;
use App\Models\SideMenu;
use App\Models\SubAdmin;
use App\Models\SubAdminPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AdminController extends Controller
{

    public function getdashboard()
    {
        $totalFarmers = Farmer::all()->count();
        $totalDealers = AuthorizedDealer::all()->count();
        // $totalInsuranceCrops = EnsuredCrop::all()->count();
        // dd($totalFarmers);
        $sideMenuName = [];
        $sideMenuPermissions = [];

        if (Auth::guard('subadmin')->check()) {
            $subAdminData = $this->getSubAdminPermissions();
            $sideMenuPermissions = $subAdminData['sideMenuPermissions'];
            $sideMenuName = $subAdminData['sideMenuName'];
        }
        // dd($sideMenuName);
        return view('admin.index', compact('sideMenuName', 'totalFarmers', 'totalDealers'));
    }

    public function getProfile()
    {
        $data = Admin::find(Auth::guard('admin')->id());

        if (Auth::guard('subadmin')->check()) {
            $data = SubAdmin::find(Auth::guard('subadmin')->id());
        }

        $sideMenuName = [];
        $sideMenuPermissions = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
            $sideMenuPermissions = $subAdminData['sideMenuPermissions'];
        }

        return view('admin.auth.profile', compact('data', 'sideMenuPermissions', 'sideMenuName'));
    }

    public function update_profile(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required'
        ]);
        $data = $request->only(['name', 'email', 'phone']);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('public/admin/assets/images/admin', $filename);
            $data['image'] = 'public/admin/assets/images/admin/' . $filename;
        }
        if (Auth::guard('admin')->check()) {
            Admin::find(Auth::guard('admin')->id())->update($data);
        } else {
            SubAdmin::find(Auth::guard('subadmin')->id())->update($data);
        }
        return back()->with(['status' => true, 'message' => 'Profile Updated Successfully']);
    }
    public function forgetPassword()
    {
        return view('admin.auth.forgetPassword');
    }
    public function adminResetPasswordLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $adminExists = DB::table('admins')->where('email', $request->email)->first();

        if (!$adminExists) {
            $subAdminExists = DB::table('sub_admins')->where('email', $request->email)->first();
        }

        if (!$adminExists && !$subAdminExists) {
            return back()->withErrors(['email' => 'The email address is not registered with any admin or subadmin.']);
        }

        $emailToUse = $adminExists ? $adminExists->email : $subAdminExists->email;

        $exists = DB::table('password_resets')->where('email', $emailToUse)->first();

        // $exists = DB::table('password_resets')->where('email', $request->email)->first();
        if ($exists) {
            return back()->with('message', 'Reset Password link has been already sent');
            // dd($subAdminExists);
        } else {
            $token = Str::random(30);
            DB::table('password_resets')->insert([
                'email' => $emailToUse,
                'token' => $token,
            ]);

            $data['url'] = url('change_password', $token);
            Mail::to($emailToUse)->send(new ResetPasswordMail($data));
            return back()->with('message', 'Reset Password Link Send Successfully');
        }
    }
    public function change_password($id)
    {

        $user = DB::table('password_resets')->where('token', $id)->first();

        if (isset($user)) {
            return view('admin.auth.chnagePassword', compact('user'));
        }
    }

    public function resetPassword(Request $request)
    {

        $request->validate([
            'password' => 'required|min:8',
            'confirmed' => 'required',

        ]);
        if ($request->password != $request->confirmed) {

            return back()->with(['error_message' => 'Password not matched']);
        }
        $password = bcrypt($request->password);
        $adminExists = Admin::where('email', $request->email)->first();

        if (!$adminExists) {
            $subAdminExists = SubAdmin::where('email', $request->email)->first();
        }

        if (!$adminExists && !$subAdminExists) {
            return back()->with(['error_message' => 'Email not registered in any admin or subadmin account']);
        }

        if ($adminExists) {
            $adminExists->update(['password' => $password]);
        } elseif ($subAdminExists) {
            $subAdminExists->update(['password' => $password]);
        }

        DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect('admin')->with('message', 'Password Reset Successfully!');
    }
    public function logout()
    {
        $adminExists = Auth::guard('admin')->logout();
        // dd($adminExists);
        if (!$adminExists) {
            Auth::guard('subadmin')->logout();
        }
        return redirect('admin')->with('message', 'Log Out Successfully');
    }


    public function getSubAdminPermissions()
    {
        $subadmin = Auth::guard('subadmin')->user();

        // Fetch sub-admin permissions with associated side menus
        $sidemenu_permission = SubAdminPermission::where('sub_admin_id', $subadmin->id)
            ->whereIn('permissions', ['view', 'create', 'edit', 'delete'])
            ->with('side_menu')
            ->get();

        // Extract unique side menu names
        $sideMenuName = $sidemenu_permission->pluck('side_menu.name')->unique();

        // Group and map permissions by side menu name
        $sideMenuPermissions = $sidemenu_permission
            ->groupBy(fn($permission) => $permission->side_menu->name) // Group by side menu name
            ->map(function ($group, $sideMenuName) {
                return [
                    'side_menu_name' => $sideMenuName,
                    'permissions' => $group->pluck('permissions')->unique(),
                ];
            });

        return [
            'sideMenuPermissions' => $sideMenuPermissions,
            'sideMenuName' => $sideMenuName,
        ];
    }
}
