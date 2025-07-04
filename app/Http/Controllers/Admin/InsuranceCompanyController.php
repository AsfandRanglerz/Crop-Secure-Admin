<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\InsuranceCompany;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class InsuranceCompanyController extends Controller
{
    public function index()
    {
        $sideMenuName = [];
        $sideMenuPermissions = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
            $sideMenuPermissions = $subAdminData['sideMenuPermissions'];
        }

        $insuranceCompanies = InsuranceCompany::orderBy('status', 'desc')->latest()->get();

        return view('admin.insurance_company.index', compact('sideMenuPermissions', 'sideMenuName', 'insuranceCompanies'));
    }

    public function store(Request $request)
    {
        // dd($request);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:insurance_companies,email',

                'regex:/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/'
            ],
            'status' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'email.regex' => 'Kindly use the correct email format(e.g., abc123@gmail.com or ABC123@Gmail.com).',
        ]);
        // dd($request);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move(public_path('admin/assets/images/companies/'), $filename);
            $image = 'public/admin/assets/images/companies/' . $filename;
        } else {
            $image = 'public/admin/assets/images/avator.png';
        }

        InsuranceCompany::create([
            'name' => $request->name,
            'email' => $request->email,
            'status' => $request->status,
            'image' => $image
        ]);

        return redirect()->route('insurance.company.index')->with(['message' => 'Insurance Company Created Successfully']);
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('authorized_dealers', 'email')->ignore($id),
                'regex:/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/'
            ],
            'status' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'email.regex' => 'Kindly use the correct email format(e.g., abc123@gmail.com or ABC123@Gmail.com).',
        ]);

        $company = InsuranceCompany::findOrFail($id);

        $image = $company->image;

        if ($request->hasFile('image')) {
            $destination = 'public/admin/assets/images/companies/' . $company->image;
            if (File::exists($destination)) {
                File::delete($destination);
            }

            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('public/admin/assets/images/companies', $filename);
            $image = 'public/admin/assets/images/companies/' . $filename;
            $company->image = $image;
        }

        $company->update([
            'name' => $request->name,
            'email' => $request->email,
            'status' => $request->status,
            'image' => $image
        ]);

        return redirect()->route('insurance.company.index')->with(['message' => 'Insurance Company Updated Successfully']);
    }
    public function destroy($id)
    {
        try {
            InsuranceCompany::destroy($id);
            return redirect()->route('insurance.company.index')->with(['message' => 'Insurance Company Deleted Successfully']);
        } catch (QueryException $e) {
            return redirect()->route('insurance.company.index')->with(['error' => 'This insurance cannot be deleted because it has insurance policies.']);
        }
    }
}
