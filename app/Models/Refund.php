<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Relasi balik ke Order
    public function order() {
        return $this->belongsTo(Order::class);
    }
}
