<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'locations';

    protected $fillable = [
        'shop_id',
        'location_id',
        'name',
        'address',
        'city',
        'country',
        'phone',
    ];
}
