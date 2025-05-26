<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Faq;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\UserRolePermission;

class FaqController extends Controller
{
    //
    public function Faq()
    {
        $faqs = Faq::orderBy('created_at', 'asc')->get();

        $sideMenuName = [];
        $sideMenuPermissions = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
            $sideMenuPermissions = $subAdminData['sideMenuPermissions'];
        }

        return view('admin.faq.index', compact('faqs', 'sideMenuName', 'sideMenuPermissions'));
    }


    public function Faqscreateview()
    {
        return view('admin.faq.create');
    }


    public function Faqsstore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required',
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
        Faq::create($request->all());
        return redirect('/admin/faqs')->with('success', 'FAQs created successfully');
    }



    public function FaqView($id)
    {
        $data = Faq::find($id);
        return view('admin.faq.faq', compact('data'));
    }


    public function FaqsEdit($id)
    {
        $data = Faq::find($id);
        return view('admin.faq.edit', compact('data'));
    }
    public function FaqsUpdate(Request $request, $id)
    {
        $request->validate([
            'description' => 'required'
        ]);


        $data = Faq::find($id);
        // AboutUs::find($data->id)->update($request->all());
        if (!$data) {
            return ('data not found.');
        } else {
            $data->update($request->all());
        }
        return redirect('/admin/faqs')->with('success', 'FAQs updated successfully');
    }


    public function faqdelete($id)
    {
        $faq = Faq::find($id);
        if ($faq) {
            $faq->delete();
            return redirect('/admin/faqs')->with('success', 'FAQs deleted successfully');
        } else {
            return redirect('/admin/faqs')->with('error', 'FAQs not found');
        }
    }


    public function reorder(Request $request)
    {
        foreach ($request->order as $item) {
            Faq::where('id', $item['id'])->update(['position' => $item['position']]);
        }

        return response()->json(['status' => 'success']);
    }
}