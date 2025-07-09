<?php

namespace App\Http\Controllers\Admin;

use App\Models\Uc;
use App\Models\Tehsil;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Support\Facades\Auth;

class UcController extends Controller
{
    public function index($id)
    {
        $tehsil = Tehsil::with('district')->findOrFail($id);

        $sideMenuName = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
        }

        $ucs = Uc::where('tehsil_id', $id)->orderBy('name', 'asc')->get();
        return view('admin.land.ucs.index', compact('ucs', 'tehsil','sideMenuName'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'tehsil_id' => 'required',
            'name' => 'required|string|unique:ucs,name'
        ]);
        // dd($request);

        Uc::create([
            'tehsil_id' => $request->tehsil_id,
            'name' => $request->name,
        ]);

        return redirect()->route('union.index', ['id' => $request->tehsil_id])->with(['message' => 'Union Council Created Successfully']);

    }

    public function update(Request $request, $id)
    {
        // dd($request);
        $request->validate([
            'name' => 'required|string|unique:ucs,name'
        ]);

        $data = Uc::findOrFail($id);
        $data->update([
            'name' => $request->name
        ]);

        return redirect()->route('union.index', ['id' => $request->tehsil_id])->with(['message' => 'Union Council Updated Successfully']);
    }

    public function destroy(Request $request, $id)
    {
        Uc::destroy($id);
        return redirect()->route('union.index', ['id' => $request->tehsil_id])->with(['message' => 'Union Council Deleted Successfully']);
    }
}
