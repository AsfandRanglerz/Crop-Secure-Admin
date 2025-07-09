<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\AuthorizedDealer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Admin\AdminController;
use App\Models\District;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Mail\WelcomeDealerMail;
use App\Models\Contactus;
use Illuminate\Support\Facades\Mail;

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
        AuthorizedDealer::where('is_seen', false)->update(['is_seen' => true]);

        $dealers = AuthorizedDealer::with('district')
            ->orderBy('status', 'desc')
            ->latest()
            ->get();

        $sideMenuName = [];
        $sideMenuPermissions = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
            $sideMenuPermissions = $subAdminData['sideMenuPermissions'];
        }

        $districts = District::all();

        return view('admin.authorized_dealer.index', compact('dealers', 'sideMenuName', 'sideMenuPermissions', 'districts'));
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
        $request->validate(
            [
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    'unique:authorized_dealers,email',

                    'regex:/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/'
                ],
                'password' => 'required|string|min:8',
                'cnic' => 'required|string|unique:authorized_dealers,cnic',
                'contact' => 'required|string|unique:authorized_dealers,contact',
                'district_id' => 'required|exists:districts,id',
                'status' => 'nullable',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ],
            [
                'email.regex' => 'Kindly use the correct email format(e.g., abc123@gmail.com or ABC123@Gmail.com).',
            ]
        );
        // dd($request);
        $district = District::find($request->district_id);

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
        } elseif (preg_match('/^923\d{9}$/', $rawContact)) {
            $formattedContact = '+' . $rawContact;
        } elseif (preg_match('/^\+923\d{9}$/', $request->contact)) {
            $formattedContact = $request->contact;
        } else {
            return back()->withErrors([
                'contact' => 'Please enter a valid mobile number (e.g., 03XXXXXXXXX or +92XXXXXXXXXX).'
            ]);
        }


        // Create a new dealer record
        AuthorizedDealer::create([
            'name' => $request->name,
            'father_name' => $request->father_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'cnic' => $formattedCnic,
            'dob' => $request->dob,
            'district_id' => $district->id,
            'contact' => $formattedContact,
            // 'status' => $request->status,
            'image' => $image
        ]);

        $contact = Contactus::first(); 

        $data['name'] = $request->name;
        $data['contact'] = $request->contact;
        $data['email'] = $request->email;
        $data['password'] = $request->password;
        $data['logo'] = 'https://ranglerzbeta.in/cropssecure/public/admin/assets/img/logo.png';
        $data['admin_email'] = $contact->email;
        $data['admin_phone'] = $contact->phone;

        Mail::to($request->email)->send(new WelcomeDealerMail($data));


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
            'name' => 'required|string|max:255',
            'cnic' => 'required|string',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('authorized_dealers', 'email')->ignore($id),
                'regex:/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/'
            ],
            // 'password' => 'nullable|string|min:8',
            'contact' => 'required|string',
            'status' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'email.regex' => 'Kindly use the correct email format(e.g., abc123@gmail.com or ABC123@Gmail.com).',

        ]);

        $dealer = AuthorizedDealer::findOrFail($id);
        $district = District::find($request->district_id);
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
        } elseif (preg_match('/^923\d{9}$/', $rawContact)) {
            $formattedContact = '+' . $rawContact;
        } elseif (preg_match('/^\+923\d{9}$/', $request->contact)) {
            $formattedContact = $request->contact;
        } else {
            return back()->withErrors([
                'contact' => 'Please enter a valid mobile number (e.g., 03XXXXXXXXX or +92XXXXXXXXXX).'
            ]);
        }

        $updateData = [
            'name' => $request->name,
            'father_name' => $request->father_name,
            'dob' => $request->dob,
            'district_id' => $district->id,
            'email' => $request->email,
            'cnic' => $formattedCnic,
            'contact' => $formattedContact,
            'image' => $image,
        ];

        // if ($request->filled('password')) {
        //     $updateData['password'] = Hash::make($request->password);
        // }

        $dealer->update($updateData);


        return redirect()->route('dealer.index')->with(['message' => 'Dealer Updated Successfully']);
    }


    public function destroy($id)
    {
        AuthorizedDealer::destroy($id);
        return redirect()->route('dealer.index')->with(['message' => 'Dealer Deleted Successfully']);
    }
}
