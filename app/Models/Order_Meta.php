<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Order_Meta extends Model
{
    protected $table = 'orders_meta';

    protected $fillable = [
        'shop_id',
        'order_id',
        'customer_name',
        'customer_phone',
        'delivery_type',
        'shipping_address',
        'billing_address',
        'date',
        'status',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function getFormattedDateAttribute(): string
    {
        if ($this->date instanceof Carbon) {
            return $this->date
                ->timezone('Europe/Riga')
                ->format('M d \a\t g:i a');
        }
        return '';
    }

    public function getFormattedDeliveryTypeAttribute(): string
    {
        return match ($this->delivery_type) {
            'QWQER' => 'Evening',
            'QWQER_EVENING' => 'Evening',
            'QWQER_EXPRESS' => 'Express',
            default => $this->delivery_type,
        };
    }
    public function getFormattedStatusAttribute(): string
    {
        return match ($this->status) {
            '1' => 'Processed',
            '0' => 'Unprocessed',
            default => $this->status,
        };
    }
}
