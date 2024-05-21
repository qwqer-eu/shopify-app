<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'shop_id',
        'api',
        'api_key',
        'trading_point_id',
        'order_category',
        'is_carrier_service_available',
        'carrier_service_shipping_rates',
        'shipping_rates',
    ];

    protected $casts = [
        'carrier_service_shipping_rates' => 'json',
        'shipping_rates' => 'json',
    ];
}
