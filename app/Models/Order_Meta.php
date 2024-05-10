<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order_Meta extends Model
{
    use HasFactory; 
    
    protected $table = "orders_meta";
    
    protected $fillable = [
        'shop_id', 'order_id', 'customer_name', 'customer_phone', 'shipping_address', 'billing_address'    
    ];
}
