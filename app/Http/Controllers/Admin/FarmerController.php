<?php

namespace App\Http\Controllers\Admin;

use App\Models\Farmer;
use Illuminate\Http\Request;
use App\Mail\FarmerLoginPassword;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;

class FarmerController extends Controller
{
    public function index()
    {
        $farmers =  Farmer::orderBy('status', 'desc')->latest()->get();
        
        $sideMenuName = [];
        $sideMenuPermissions = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
            $sideMenuPermissions = $subAdminData['sideMenuPermissions'];
        }

        return view('admin.farmer.index', compact('farmers', 'sideMenuPermissions', 'sideMenuName'));
    }
    public function create()
    {
        $sideMenuName = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
        }
        return view('admin.farmer.create', compact('sideMenuName'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'fname' => 'required|string|max:255',
            'email' => 'required|email|unique:farmers,email',
            'cnic' => 'required|string|unique:farmers,cnic',
            'contact' => 'required|string|unique:farmers,contact',
            'dob' => 'nullable|date',
            'status' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        // dd($request);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move(public_path('admin/assets/images/farmer/'), $filename);
            $image = 'public/admin/assets/images/farmer/' . $filename;
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


        // Create a new farmer record
        Farmer::create([
            'name' => $request->name,
            'fname' => $request->fname,
            'email' => $request->email,
            'password' => bcrypt($password),
            'cnic' => $formattedCnic,
            'contact' => $formattedContact,
            'dob' => $request->dob,
            'status' => $request->status,
            'image' => $image
        ]);


        // $message['name'] = $request->name;
        // $message['contact'] = $request->contact;
        // $message['email'] = $request->email;
        // $message['password'] = $password;

        // Mail::to($request->email)->send(new FarmerLoginPassword($message));

        // Return success message
        return redirect()->route('farmers.index')->with(['message' => 'Farmer Created Successfully']);
    }
    public function edit($id)
    {
        $farmer = Farmer::find($id);

        $permission_subAdmin = [];
        $sideMenuName = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
        }
        return view('admin.farmer.edit', compact('farmer', 'sideMenuName'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'fname' => 'nullable|string|max:255',
            'cnic' => 'nullable|string',
            'email' => 'nullable|email',
            'contact' => 'nullable|string',
            'dob' => 'nullable|date',
            'status' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        // dd($request);

        $farmer = Farmer::findOrFail($id);

        $image = $farmer->image;

        if ($request->hasFile('image')) {
            $destination = 'public/admin/assets/images/farmer/' . $farmer->image;
            if (File::exists($destination)) {
                File::delete($destination);
            }

            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('public/admin/assets/images/farmer', $filename);
            $image = 'public/admin/assets/images/farmer/' . $filename;
            $farmer->image = $image;
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

        $farmer->update([
            'name' => $request->name,
            'fname' => $request->fname,
            'email' => $request->email,
            'cnic' => $formattedCnic,
            'contact' => $formattedContact,
            'dob' => $request->dob,
            'status' => $request->status,
            'image' => $image
        ]);

        return redirect()->route('farmers.index')->with(['message' => 'Farmer Updated Successfully']);
    }
    public function destroy($id)
    {
        Farmer::destroy($id);
        return redirect()->route('farmers.index')->with(['message' => 'Farmer Deleted Successfully']);
    }
}
