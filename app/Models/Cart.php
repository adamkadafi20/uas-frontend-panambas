<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'product_id', 
        'variation_id', 
        'qty'
    ];

    // Jembatan ke tabel produk
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}