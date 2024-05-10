<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    use HasFactory;
    protected $table = "settings";

    protected $fillable = [
        'shop_id',
        'api_key',
        'trading_point_id',
        'order_category',
        'shipping_rates',
    ];

    protected $casts = [
        'shipping_rates' => 'json',
    ];
}
