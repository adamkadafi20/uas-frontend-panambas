<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // 1. Ini udah gue cocokin 100% sama tabel orders baru lu
    protected $fillable = [
        'user_id',
        'invoice_number',
        'subtotal',
        'shipping',
        'grand_total',
        'payment_method',
        'payment_status',
        'status',
        'resi_number',
        'snap_token',
        'receiver_name',
        'phone',
        'full_address',
        'province'
    ];

    // 2. Casts yang nggak kepake (kayak shipped_date & discount) udah dibuang
    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    // 3. Relasi ke User pembeli
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 4. Relasi ke OrderItem (Gue ganti namanya jadi orderItems biar nyambung sama halaman pesanan.blade.php tadi)
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Tambahin ini di paling bawah (sebelum tutup kurung class)
    public function refund() {
        return $this->hasOne(Refund::class);
    }
}