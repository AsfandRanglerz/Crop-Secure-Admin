<?php

namespace App\Http\Controllers\Admin;

use App\Models\Item;
use App\Models\DealerItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AuthorizedDealer;
use Illuminate\Support\Facades\Auth;

class DealerItemController extends Controller
{
    public function index($id)
    {
        $dealer_id = $id;
        $dealerItems = DealerItem::with('authorizedDealer')->where('authorized_dealer_id', $id)->with('item')->latest()->get();
        $dealer = AuthorizedDealer::findOrFail($id);

        $sideMenuName = [];
        $sideMenuPermissions = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
            $sideMenuPermissions = $subAdminData['sideMenuPermissions'];
        }

        return view('admin.authorized_dealer.items.index', compact('sideMenuPermissions', 'sideMenuName', 'dealerItems', 'dealer_id','dealer'));
    }
    public function create($id)
    {
        $dealer_id = $id;
        $savedItems = DealerItem::where('authorized_dealer_id', $id)->pluck('id');
        // $items = Item::whereNotIn('id', $savedItems)->get();
        $items = Item::whereDoesntHave('dealerItems', function ($query) use ($id) {
            $query->where('authorized_dealer_id', $id);
        })->orderBy('name', 'asc')->get();
        // dd($items);
        
        $sideMenuName = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
        }

        return view('admin.authorized_dealer.items.create', compact( 'sideMenuName', 'dealer_id', 'items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'dealer_id' => 'required',
            'item_id' => 'required|string',
            // 'quantity' => 'required|numeric|min:1',
            'price'    => 'required|numeric|min:0',
        ]);
        // dd($request);

        // Create a new dealer record
        DealerItem::create([
            'item_id' => $request->item_id,
            // 'quantity' => $request->quantity,
            'price' => $request->price,
            'authorized_dealer_id' => $request->dealer_id,
            'status' => $request->status,
        ]);

        // Return success message
        return redirect()->route('dealer.item.index', ['id' => $request->dealer_id])->with(['message' => 'Item Created Successfully']);
    }

    public function edit($dealer_id, $item_id)
    {
        $dealerItem = DealerItem::find($item_id);
        $item = Item::where('id', $dealerItem->item_id)->first();
        // dd($item);

        $sideMenuName = [];

        if (Auth::guard('subadmin')->check()) {
            $getSubAdminPermissions = new AdminController();
            $subAdminData = $getSubAdminPermissions->getSubAdminPermissions();
            $sideMenuName = $subAdminData['sideMenuName'];
        }

        return view('admin.authorized_dealer.items.edit', compact('sideMenuName', 'dealerItem', 'dealer_id', 'item'));
    }
    
    public function update(Request $request, $id)
    {
        $request->validate([
            // 'item_id' => 'required|string',
        //    'quantity' => 'required|numeric|min:1',
            'price'    => 'required|numeric|min:0',
        ]);
        // dd($request);

        $item = DealerItem::findOrFail($id);

        $item->update([
            // 'item_id' => $request->item_id,
            // 'quantity' => $request->quantity,
            'price' => $request->price,
            'status' => $request->status,
        ]);

        return redirect()->route('dealer.item.index', ['id' => $request->dealer_id])->with(['message' => 'Item Updated Successfully']);
    }

    public function destroy(Request $request, $id)
    {
        // dd($request);
        DealerItem::destroy($id);
        return redirect()->route('dealer.item.index', ['id' => $request->dealer_id])->with(['message' => 'Item Deleted Successfully']);
    }
}
