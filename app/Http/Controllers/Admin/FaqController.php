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
        $faqs = Faq::orderBy('position', 'asc')->get();

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
        $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required',
        ]);

        Faq::create([
            'question' => $request->question,
    'answer' => strip_tags($request->answer),
        ]);

        return redirect()->route('faqs')->with('success', 'FAQ Created Successfully');
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
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
        ]);

        $faq = Faq::findOrFail($id);
        $faq->update([
            'question' => $request->question,
            'answer' => $request->answer,
        ]);
        return redirect('/admin/faqs')->with('success', 'FAQ Updated Successfully');
    }


    public function faqdelete($id)
    {
        $faq = Faq::find($id);
        if ($faq) {
            $faq->delete();
            return redirect('/admin/faqs')->with('success', 'FAQ Deleted Successfully');
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
