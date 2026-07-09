<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'sender_name', 'sender_phone', 'province', 'city', 'district', 'postal_code', 'detail_address'
    ];
}