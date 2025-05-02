<?php

namespace App\Http\Controllers\Api;

use App\Models\DealerItem;
use Illuminate\Http\Request;
use App\Models\ProductSelection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function getDealerProducts($dealerId)
{
    $user = Auth::user();
    $products = DealerItem::with('item')
                ->where('authorized_dealer_id', $dealerId)
                ->get()
                ->map(function($dealerItem) {
                    return [
                        'id' => $dealerItem->id,
                        'name' => $dealerItem->item->name,
                        // 'description' => $dealerItem->item->description,
                        // 'image' => asset('storage/' . $dealerItem->item->image),
                        'quantity' => $dealerItem->quantity,
                        'price' => $dealerItem->price
                    ];
                });

    return response()->json([
        // 'status' => true,
        'data' => $products
    ]);
}

public function addToList(Request $request)
{
   
    $user = Auth::user();

    // Get the dealer item with the authorized dealer ID
    $dealerItem = DealerItem::findOrFail($request->item_id);
    $selection = ProductSelection::Create(
        [
            'user_id' => $user->id,
            'dealer_item_id' => $dealerItem->id,
            'quantity' => $request->quantity,
            'authorized_dealer_id' => $request->authorized_dealer_id
       
            
        ]
    );

    return response()->json([
        // 'status' => true,
        'message' => 'Product added to list successfully',
        'data' => $selection
    ]);
}

public function getAddedList()
{
    $user = Auth::user();

    $products = ProductSelection::with(['dealerItem.item'])
        ->where('user_id', $user->id)
        ->get()
        ->map(function ($product) {
            return [
                'item_name' => $product->dealerItem->item->name ?? 'N/A',
                'quantity' => $product->quantity,
                'price' => $product->dealerItem->price,
            ];
        });

    return response()->json([
        'message' => 'Fetched added list items successfully',
        'data' => $products
    ]);
}

public function deleteFromList($id)
{
    $user = Auth::user();

    $product = ProductSelection::where('id', $id)
        ->where('user_id', $user->id)
        ->first();


    $product->delete();

    return response()->json([
        'message' => 'Product removed from list successfully'
    ]);
}


}
