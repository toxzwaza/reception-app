<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InitialOrder extends Model
{
    use HasFactory;

    // 納品データとの多対多リレーション
    public function deliveries(): BelongsToMany
    {
        return $this->belongsToMany(Delivery::class, 'delivery_initial_order', 'initial_order_id', 'delivery_id')
            ->withTimestamps();
    }
}
