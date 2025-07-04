<?php

namespace App\Http\Controllers\Admin;

use App\Models\SideMenu;
use App\Models\SubAdmin;
use Illuminate\Http\Request;
use App\Models\SubAdminPermission;
use App\Mail\SubAdminLoginPassword;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class SubAdminController extends Controller
{
    public function index()
    {
        $subAdmins = SubAdmin::with('permissions.side_menu')->orderBy('status', 'desc')->latest()->get();
        $sideMenus = SideMenu::all();

        return view('admin.subadmin.index', compact('subAdmins', 'sideMenus'));
    }

    public function create()
    {
        $sideMenus = SideMenu::all();
        // return $roles;
        return view('admin.subadmin.create', compact('sideMenus'));
    }


    public function store(Request $request)
    {
        // dd($request);
        // Validate the incoming request data

        $request->validate([
            'name' => 'required|string|max:255',

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:sub_admins,email',
                'regex:/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/'
            ],
            'password' => 'required|string|min:8',
            'phone' => 'required|unique:sub_admins,phone',

        ], [
            'name.required' => 'The name field is required.',
            'email.regex' => 'Kindly use the correct email format(e.g., abc123@gmail.com or ABC123@Gmail.com).',
        ]);


        // Format phone number to +92 format
        $formattedPhone = null;
        $rawPhone = preg_replace('/[^0-9]/', '', $request->phone);

        if (preg_match('/^03\d{9}$/', $rawPhone)) {
            $formattedPhone = '+92' . substr($rawPhone, 1);
        } elseif (preg_match('/^923\d{9}$/', $rawPhone)) {
            $formattedPhone = '+' . $rawPhone;
        } elseif (preg_match('/^\+923\d{9}$/', $request->phone)) {
            $formattedPhone = $request->phone;
        } else {
            return back()->withErrors([
                'phone' => 'Please enter a valid mobile number (e.g., 03XXXXXXXXX or +92XXXXXXXXXX).'
            ])->withInput();
        }


        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move(public_path('admin/assets/images/users/'), $filename);
            $image = 'public/admin/assets/images/users/' . $filename;
        } else {
            $image = 'public/admin/assets/images/avator.png';
        }

        // Create a new subadmin record
        SubAdmin::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'status' => $request->status ?? 1,
            'image' => $image
        ]);

        $message['name'] = $request->name;
        $message['email'] = $request->email;
        $message['password'] = $request->password;

        Mail::to($request->email)->send(new SubAdminLoginPassword($message));

        // Return success message
        return redirect()->route('subadmin.index')->with(['message' => 'Subadmin Created Successfully']);
    }

    public function edit($id)
    {
        $subAdmin = SubAdmin::find($id);
        // return $subAdmin;

        return view('admin.subadmin.edit', compact('subAdmin'));
    }

    public function update(Request $request, $id)
    {
        $subAdmin = SubAdmin::findOrFail($id);

        // Validate First
        $request->validate([
            'name' => 'required',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'regex:/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/',
                Rule::unique('sub_admins', 'email')->ignore($subAdmin->id), // ✅ fix duplicate error
            ],
            // 'password' => 'required',
            'phone' => 'required',

        ], [
            'email.regex' => 'Kindly use the correct email format(e.g., abc123@gmail.com or ABC123@Gmail.com).',
            'name.regex' => 'Kindly enter a valid name',
        ]);

        // Format Phone
        $formattedPhone = null;
        $rawPhone = preg_replace('/[^0-9]/', '', $request->phone);

        if (preg_match('/^03\d{9}$/', $rawPhone)) {
            $formattedPhone = '+92' . substr($rawPhone, 1);
        } elseif (preg_match('/^923\d{9}$/', $rawPhone)) {
            $formattedPhone = '+' . $rawPhone;
        } elseif (preg_match('/^\+923\d{9}$/', $request->phone)) {
            $formattedPhone = $request->phone;
        } else {
            return back()->withErrors([
                'phone' => 'Please enter a valid mobile number (e.g., 03XXXXXXXXX or +92XXXXXXXXXX).'
            ])->withInput();
        }
        $image = $subAdmin->image;

        if ($request->hasFile('image')) {
            $destination = 'public/admin/assets/images/users/' . basename($subAdmin->image);
            if (File::exists($destination)) {
                File::delete($destination);
            }

            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('public/admin/assets/images/users', $filename);
            $image = 'public/admin/assets/images/users/' . $filename;
        }



        // Update After All Validations
        $subAdmin->update([
            'name' => $request->name,
            'email' => $request->email,
            // 'password' => $request->password,
            'phone' => $formattedPhone,
            'image' => $image,
        ]);

        return redirect()->route('subadmin.index')->with('message', 'SubAdmin Updated Successfully');
    }



    public function destroy($id)
    {
        // return $id;
        SubAdmin::destroy($id);
        return redirect()->route('subadmin.index')->with(['message' => 'SubAdmin Deleted Successfully']);
    }


    public function updatePermissions(Request $request, $id)
    {
        $data = $request->validate([
            'sub_admin_id' => 'required',
            'side_menu_id' => 'nullable',
        ]);

        // dd($request);
        $permissions = [];
        if (!empty($data['side_menu_id'])) {
            foreach ($data['side_menu_id'] as $sideMenuId => $permissionArray) {
                foreach ($permissionArray as $permission) {
                    $permissions[] = [
                        'sub_admin_id' => $data['sub_admin_id'],
                        'side_menu_id' => $sideMenuId,
                        'permissions' => $permission, // Store each permission separately
                    ];
                }
            }
        }

        SubAdminPermission::where('sub_admin_id', $id)->delete();

        SubAdminPermission::insert($permissions);

        return redirect()->route('subadmin.index')->with('message', 'Permissions Updated Successfully');
    }

    public function StatusChange(Request $request)
    {
        $subAdmin = SubAdmin::find($request->id);
        $subAdmin->update([
            'status' => $request->status
        ]);
        return response()->json(['success' => true]);
    }
}
