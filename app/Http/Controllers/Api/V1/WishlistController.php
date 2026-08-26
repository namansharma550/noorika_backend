<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->wishlist()->with('product.images')->get();
    }

    public function store(Request $request, int $productId)
    {
        $item = $request->user()->wishlist()->firstOrCreate(['product_id' => $productId]);

        return response()->json($item, 201);
    }

    public function destroy(Request $request, int $productId)
    {
        $request->user()->wishlist()->where('product_id', $productId)->delete();

        return response()->json(['message' => 'Removed from wishlist']);
    }
}
