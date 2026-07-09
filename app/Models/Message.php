<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Relasi ke tabel users (Si Pengirim)
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Relasi ke tabel users (Si Penerima)
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}