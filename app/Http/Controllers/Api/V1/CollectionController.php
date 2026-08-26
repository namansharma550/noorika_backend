<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Collection;

class CollectionController extends Controller
{
    public function show(string $slug)
    {
        return Collection::where('slug', $slug)
            ->with(['products' => fn ($q) => $q->where('status', 'active')->with('images')])
            ->firstOrFail();
    }
}
