<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class DeliveryInitialOrder extends Pivot
{
    use HasFactory;
    
    protected $table = 'delivery_initial_order';

    protected $fillable = [
        'delivery_id',
        'initial_order_id',
    ];
}
