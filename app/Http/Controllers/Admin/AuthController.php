<?php

namespace App\Http\Controllers\Admin;

use Hash;
use App\Models\SubAdmin;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function getLoginPage()
    {
        return view('admin.auth.login');
    }
    public function Login(Request $request)
    {
        // dd($request);
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
        $remember_me = ($request->remember_me) ? true : false;

        if (Auth()->guard('admin')->attempt(
            [
                'email' => $request->email,
                'password' => $request->password
            ],
            $remember_me
        )) {
            return redirect('admin/dashboard')->with('message', 'Logged In Successfully');
        }

        $subAdmin = SubAdmin::where('email', $request->email)->first();

        if ($subAdmin && Hash::check($request->password, $subAdmin->password)) {

            if ($subAdmin->status == 1) {
                auth()->guard('subadmin')->login($subAdmin, $remember_me);

                return redirect('admin/dashboard')->with('message', 'Logged In Successfully');
            } else {
                return back()->with(['alert' => 'error', 'message' => 'Account Deactivated']);
            }
        }

        return back()->with(['alert' => 'error', 'message' => 'Invalid Email or Password']);
    }
}
