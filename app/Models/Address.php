<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'receiver_name', 'phone', 'province', 
        'city', 'district', 'postal_code', 'detail_address', 'is_primary'
    ];

    // Relasi balik ke User
    public function user() {
        return $this->belongsTo(User::class);
    }
}