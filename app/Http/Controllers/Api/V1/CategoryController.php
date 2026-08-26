<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::where('status', 'active')
            ->whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('display_order')
            ->get();
    }

    public function show(string $slug)
    {
        return Category::where('slug', $slug)
            ->where('status', 'active')
            ->with('children')
            ->firstOrFail();
    }
}
