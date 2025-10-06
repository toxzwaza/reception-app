<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffMember extends Model
{
    protected $fillable = [
        'user_id',
    ];

    // ユーザーとのリレーション
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 来訪者とのリレーション
    public function visitors(): HasMany
    {
        return $this->hasMany(Visitor::class);
    }

    // 納品記録とのリレーション
    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    // 集荷記録とのリレーション
    public function pickups(): HasMany
    {
        return $this->hasMany(Pickup::class);
    }
}