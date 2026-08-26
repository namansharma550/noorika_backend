<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Collection extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'banner_image'];

    protected $appends = ['banner_image_url'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'collection_product');
    }

    protected function bannerImageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->banner_image ? Storage::disk('public')->url($this->banner_image) : null);
    }
}
