<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductView extends Model
{
    use HasFactory;

    // Ngasih tau Laravel kalau model ini nyambung ke tabel product_views
    protected $table = 'product_views';

    // Kolom-kolom yang diizinin buat diisi datanya (Mass Assignment)
    protected $fillable = [
        'product_id',
        'session_id',
        'ip_address',
    ];

    // Relasi: 1 View ini milik 1 Produk
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}