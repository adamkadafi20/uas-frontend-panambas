<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    // Udah di-update sesuai tabel order_items yang baru!
    protected $fillable = [
        'order_id',
        'product_id',
        'variation_id',     // Baru: Buat nyimpen ID Variasi
        'product_name',     // Baru: Ganti dari 'name'
        'variation_name',   // Baru: Buat nyimpen nama variasi (misal: "Ukuran Sedang")
        'qty',
        'price',
        'total'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}