<?php

namespace App\Http\Controllers\Admin;

use App\Models\Farmer;
use Illuminate\Http\Request;
use App\Mail\FarmerLoginPassword;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class FarmerController extends Controller
{
    public function index()
    {
        $farmers =  Farmer::orderBy('created_at', 'desc')->latest()->get();

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
        $request->validate(
            [
                'name' => 'required|string|max:255',
                'fname' => 'nullable|string|max:255',
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    'unique:farmers,email',

                    'regex:/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/'
                ],
                'password' => 'required|string|min:8',
                'cnic' => 'required|string|unique:farmers,cnic',
                'contact' => 'required|string|unique:farmers,contact',
                'dob' => 'nullable|date',
                'status' => 'nullable',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ],
            [
                'email.regex' => 'Kindly use the correct email format(e.g., abc123@gmail.com or ABC123@Gmail.com).',
            ]
        );
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
        } elseif (preg_match('/^923\d{9}$/', $rawContact)) {
            $formattedContact = '+' . $rawContact;
        } elseif (preg_match('/^\+923\d{9}$/', $request->contact)) {
            $formattedContact = $request->contact;
        } else {
            return back()->withErrors([
                'contact' => 'Please enter a valid mobile number (e.g., 03XXXXXXXXX or +92XXXXXXXXXX).'
            ])->withInput();
        }


        // Create a new farmer record
        Farmer::create([
            'name' => $request->name,
            'fname' => $request->fname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'cnic' => $formattedCnic,
            'contact' => $formattedContact,
            'dob' => $request->dob,
            'status' => $request->status,
            'image' => $image
        ]);


        $message['name'] = $request->name;
        $message['contact'] = $request->contact;
        $message['email'] = $request->email;
        $message['password'] = $request->password;

        Mail::to($request->email)->send(new FarmerLoginPassword($message));

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
            'name' => 'required|string|max:255',
            'fname' => 'nullable|string|max:255',
            'cnic' => 'required|string',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('farmers', 'email')->ignore($id),
                'regex:/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/'
            ],
            // 'password' => 'nullable|string|min:8',
            'contact' => 'nullable|string',
            'dob' => 'nullable|date',
            'status' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'email.regex' => 'Kindly use the correct email format (e.g., abc123@gmail.com or ABC123@Gmail.com).',
        ]);

        $farmer = Farmer::findOrFail($id);
        $imagePath = $farmer->image;

        // Handle image upload
        if ($request->hasFile('image')) {
            $destination = public_path($farmer->image);
            if (File::exists($destination)) {
                File::delete($destination);
            }

            $file = $request->file('image');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('admin/assets/images/farmer'), $filename);
            $imagePath = 'public/admin/assets/images/farmer/' . $filename;
        }

        // Format CNIC
        $formattedCnic = null;
        $rawCnic = preg_replace('/[^0-9]/', '', $request->cnic);
        if ($rawCnic && strlen($rawCnic) === 13) {
            $formattedCnic = preg_replace("/^(\d{5})(\d{7})(\d{1})$/", "$1-$2-$3", $rawCnic);
        }

        // Format Contact Number
        $formattedContact = null;
        $rawContact = preg_replace('/[^0-9]/', '', $request->contact);
        if (preg_match('/^03\d{9}$/', $rawContact)) {
            $formattedContact = '+92' . substr($rawContact, 1);
        } elseif (preg_match('/^923\d{9}$/', $rawContact)) {
            $formattedContact = '+' . $rawContact;
        } elseif (preg_match('/^\+923\d{9}$/', $request->contact)) {
            $formattedContact = $request->contact;
        } else {
            return back()->withErrors([
                'contact' => 'Please enter a valid mobile number (e.g., 03XXXXXXXXX or +92XXXXXXXXXX).'
            ]);
        }

        // Update fields
        $farmer->name = $request->name;
        $farmer->fname = $request->fname;
        $farmer->email = $request->email;
        $farmer->cnic = $request->cnic;
        $farmer->contact = $request->contact;
        $farmer->dob = $request->dob;
        $farmer->status = $request->status;
        $farmer->image = $imagePath;

        // Only update password if provided
        // if (!empty($request->password)) {
        //     $farmer->password = Hash::make($request->password);
        // }

        $farmer->save();

        return redirect()->route('farmers.index')->with('message', 'Farmer updated successfully.');
    }


    public function destroy($id)
    {
        Farmer::destroy($id);
        return redirect()->route('farmers.index')->with(['message' => 'Farmer Deleted Successfully']);
    }
}
