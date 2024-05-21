<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'shop_id',
        'shop_order_id',
        'order_id',
        'delivery_area_id',
        'client_id',
        'courier_id',
        'trading_point_id',
        'is_parent',
        'parent_id',
        'status',
        'type',
        'real_type',
        'category',
        'pickup_datetime',
        'is_round_trip',
        'courier_vehicle_id',
        'courier_transport_mode',
        'dropdowns',
        'client_price',
        'client_distance',
        'client_distance_price',
        'client_adjustments_price',
        'client_dropdowns_price',
        'client_pickup_price',
        'direct_distance',
        'distance',
        'accepted_at',
        'picked_up_at',
        'finished_at'
    ];
}
