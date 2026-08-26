<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Page;

class PageController extends Controller
{
    public function show(string $slug)
    {
        return Page::where('slug', $slug)->where('status', 'published')->firstOrFail();
    }
}
