<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id');
    }

    public function productRatings()
    {
        return $this->hasMany(ProductRating::class);
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class, 'product_id', 'id');
    }

    // Relasi: 1 Produk punya BANYAK Views (Klik)
    public function views()
    {
        return $this->hasMany(ProductView::class);
    }
}