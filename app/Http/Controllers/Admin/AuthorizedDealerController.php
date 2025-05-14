<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\AuthorizedDealer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Admin\AdminController;
use App\Models\District;

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
        $districts = District::all()->sortBy('name');
        $sideMenuName = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();  
            $sideMenuName = $subAdminData['sideMenuName'];
        }

        return view('admin.authorized_dealer.create', compact('sideMenuName', 'districts'));
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
        $district = District::find($request->district);

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

        // Clean and format CNIC
        $rawCnic = preg_replace('/[^0-9]/', '', $request->cnic);
        $formattedCnic = null;
        if ($rawCnic && strlen($rawCnic)) {
            $formattedCnic = preg_replace("/^(\d{5})(\d{7})(\d{1})$/", "$1-$2-$3", $rawCnic);
        }

        // formated phone number +92
        $formattedContact = null;
        $rawContact = preg_replace('/[^0-9]/', '', $request->contact);

        if (preg_match('/^03\d{9}$/', $rawContact)) {
            $formattedContact = '+92' . substr($rawContact, 1);
        }
        elseif (preg_match('/^923\d{9}$/', $rawContact)) {
            $formattedContact = '+' . $rawContact;
        }
        elseif (preg_match('/^\+923\d{9}$/', $request->contact)) {
            $formattedContact = $request->contact;
        }
        else {
            return back()->withErrors([
                'contact' => 'Please enter a valid mobile number (e.g., 03XXXXXXXXX or +92XXXXXXXXXX).'
            ]);
        }


        // Create a new dealer record
        AuthorizedDealer::create([
            'name' => $request->name,
            'father_name' => $request->father_name,
            'email' => $request->email,
            'password' => bcrypt($password),
            'cnic' => $formattedCnic,
            'dob' => $request->dob,
            'district' => $district->name,
            'contact' => $formattedContact,
            // 'status' => $request->status,
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
        $districts = District::all()->sortBy('name');
        $dealer = AuthorizedDealer::find($id);
        $sideMenuName = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
        }

        return view('admin.authorized_dealer.edit', compact('sideMenuName', 'dealer', 'districts'));
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

        $dealer = AuthorizedDealer::findOrFail($id);
        $district = District::find($request->district);
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

        // Format CNIC
        $rawCnic = preg_replace('/[^0-9]/', '', $request->cnic);
        $formattedCnic = null;
        if ($rawCnic && strlen($rawCnic)) {
            $formattedCnic = preg_replace("/^(\d{5})(\d{7})(\d{1})$/", "$1-$2-$3", $rawCnic);
        }

        // Format Contact Number
        $formattedContact = null;
        $rawContact = preg_replace('/[^0-9]/', '', $request->contact);

        if (preg_match('/^03\d{9}$/', $rawContact)) {
            $formattedContact = '+92' . substr($rawContact, 1);
        }
        elseif (preg_match('/^923\d{9}$/', $rawContact)) {
            $formattedContact = '+' . $rawContact;
        }
        elseif (preg_match('/^\+923\d{9}$/', $request->contact)) {
            $formattedContact = $request->contact;
        }
        else {
            return back()->withErrors([
                'contact' => 'Please enter a valid mobile number (e.g., 03XXXXXXXXX or +92XXXXXXXXXX).'
            ]);
        }

        $dealer->update([
            'name' => $request->name,
            'father_name' => $request->father_name,
            'dob' => $request->dob,
            'district' => $district->name,
            'email' => $request->email,
            'cnic' => $formattedCnic,
            'contact' => $formattedContact,
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
