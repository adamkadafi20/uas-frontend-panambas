<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    protected $fillable = ['store_address', 'global_reguler', 'global_kargo', 'global_instant', 'pay_va', 'pay_ewallet', 'pay_cod'];
}
