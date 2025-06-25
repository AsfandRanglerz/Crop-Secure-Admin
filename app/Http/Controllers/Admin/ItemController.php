<?php

namespace App\Http\Controllers\Admin;

use App\Models\Item;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;

class ItemController extends Controller
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

        $Items = Item::orderBy('name', 'asc')->get();

        return view('admin.item.index', compact('sideMenuPermissions', 'sideMenuName', 'Items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:items,name',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'description' => 'nullable|string|max:1000',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/items/'), $filename);
            $imagePath = 'uploads/items/' . $filename;
        }

        Item::create([
            'name' => $request->name,
            'image' => $imagePath,
            'description' => $request->description,
        ]);

        return redirect()->route('items.index')->with(['message' => 'Item Created Successfully']);
    }



    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:items,name,' . $id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'description' => 'nullable|string|max:1000',
        ]);

        $item = Item::findOrFail($id);

        $imagePath = $item->image; // keep existing image by default

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/items/'), $filename);
            $imagePath = 'uploads/items/' . $filename;
        }

        $item->update([
            'name' => $request->name,
            'image' => $imagePath,
            'description' => $request->description,
        ]);

        return redirect()->route('items.index')->with(['message' => 'Item Updated Successfully']);
    }



    public function destroy(Request $request, $id)
    {
        try {
            $item = Item::findOrFail($id);

            // ✅ Delete image file if it exists and is not the default one
            if ($item->image && File::exists(public_path($item->image))) {
                File::delete(public_path($item->image));
            }

            // ✅ Delete the item from the database
            $item->delete();

            return redirect()->route('items.index')->with(['message' => 'Item Deleted Successfully']);
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('items.index')->with([
                'error' => 'This item cannot be deleted because it is assigned to an Authorized Dealer.'
            ]);
        }
    }
}
