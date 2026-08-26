<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(int $productId)
    {
        $product = Product::findOrFail($productId);

        return $product->approvedReviews()->with('user:id,name')->latest()->get();
    }

    public function store(Request $request, int $productId)
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:150'],
            'body' => ['nullable', 'string', 'max:2000'],
        ]);

        $product = Product::findOrFail($productId);

        $review = $product->reviews()->create([
            'user_id' => $request->user()->id,
            'rating' => $data['rating'],
            'title' => $data['title'] ?? null,
            'body' => $data['body'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json($review, 201);
    }
}
