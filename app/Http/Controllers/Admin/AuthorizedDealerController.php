<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\AuthorizedDealer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Admin\AdminController;

class AuthorizedDealerController extends Controller
{
    // public function index()
    // {
    //     $dealers = AuthorizedDealer::latest()->get();
    //     $sideMenuName = [];
    //     $sideMenuPermissions = [];

    //     if (Auth::guard('subadmin')->check()) {
    //         $getSubAdminPermissions = new AdminController();
    //         $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
    //         $sideMenuPermissions = $subAdminData['sideMenuPermissions'];
    //         $sideMenuName = $subAdminData['sideMenuName'];
    //     }
    //     return view('admin.authorized_dealer.index', compact('sideMenuName', 'dealers', 'sideMenuPermissions'));
    // }

    public function index()
    {
        $dealers = AuthorizedDealer::orderBy('status', 'desc')->latest()->get();

        $sideMenuName = [];
        $sideMenuPermissions = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
            $sideMenuPermissions = $subAdminData['sideMenuPermissions'];
        }

        return view('admin.authorized_dealer.index', compact('dealers', 'sideMenuName', 'sideMenuPermissions'));
    }

    public function create()
    {
        $sideMenuName = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();  
            $sideMenuName = $subAdminData['sideMenuName'];
        }

        return view('admin.authorized_dealer.create', compact('sideMenuName'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:authorized_dealers,email',
            'cnic' => 'nullable|string|unique:authorized_dealers,cnic',
            'contact' => 'required|string|unique:authorized_dealers,contact',
            'status' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        // dd($request);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move(public_path('admin/assets/images/authorizedDealer/'), $filename);
            $image = 'public/admin/assets/images/authorizedDealer/' . $filename;
        } else {
            $image = 'public/admin/assets/images/avator.png';
        }

        /**generate random password */
        $password = random_int(10000000, 99999999);

        // Create a new dealer record
        AuthorizedDealer::create([
            'name' => $request->name,
            'father_name' => $request->father_name,
            'email' => $request->email,
            'password' => bcrypt($password),
            'cnic' => $request->cnic,
            'dob' => $request->dob,
            'district' => $request->district,
            'contact' => $request->contact,
            'status' => $request->status,
            'image' => $image
        ]);


        // $message['name'] = $request->name;
        // $message['contact'] = $request->contact;
        // $message['email'] = $request->email;
        // $message['password'] = $password;

        // Mail::to($request->email)->send(new dealerLoginPassword($message));

        // Return success message
        return redirect()->route('dealer.index')->with(['message' => 'Dealer Created Successfully']);
    }

    public function edit($id)
    {
        $dealer = AuthorizedDealer::find($id);
        $sideMenuName = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
        }

        return view('admin.authorized_dealer.edit', compact('sideMenuName', 'dealer'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'cnic' => 'nullable|string',
            'email' => 'nullable|email',
            'contact' => 'nullable|string',
            'status' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        // dd($request);

        $dealer = AuthorizedDealer::findOrFail($id);

        $image = $dealer->image;

        if ($request->hasFile('image')) {
            $destination = 'public/admin/assets/images/dealer/' . $dealer->image;
            if (File::exists($destination)) {
                File::delete($destination);
            }

            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('public/admin/assets/images/dealer', $filename);
            $image = 'public/admin/assets/images/dealer/' . $filename;
            $dealer->image = $image;
        }

        $dealer->update([
            'name' => $request->name,
            'father_name' => $request->father_name,
            'dob' => $request->dob,
            'district' => $request->district,
            'email' => $request->email,
            'cnic' => $request->cnic,
            'contact' => $request->contact,
            'status' => $request->status,
            'image' => $image
        ]);

        return redirect()->route('dealer.index')->with(['message' => 'Dealer Updated Successfully']);
    }

    public function destroy($id)
    {
        AuthorizedDealer::destroy($id);
        return redirect()->route('dealer.index')->with(['message' => 'Dealer Deleted Successfully']);
    }
}
