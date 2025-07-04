<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contactus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ContactUsController extends Controller
{
    public function Index()
    {
        $contacts = ContactUs::orderby('id', 'desc')->get();

        $sideMenuName = [];
        $sideMenuPermissions = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
            $sideMenuPermissions = $subAdminData['sideMenuPermissions'];
        }

        return view('admin.contact.index', compact('contacts', 'sideMenuPermissions', 'sideMenuName'));
    }

    public function createview()
    {
        return view('contact.create');
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
        ]);

        // If validation fails
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Save data
        Contactus::create([
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        // Success response
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Contact information saved successfully.',
            ]);
        }

        // return redirect()->route('contact.index')->with('success', 'Contact added successfully.');
    }


    public function updateview($id)
    {
        $find = ContactUs::find($id);
        return view('admin.contact.edit', compact('find'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
        ]);

        $contact = ContactUs::findOrFail($id); 

        $contact->email = $request->input('email');
        $contact->phone = $request->input('phone');

        $contact->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Contact information updated successfully!'
        ]);
    }
}
