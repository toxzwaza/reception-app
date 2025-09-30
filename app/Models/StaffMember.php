<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffMember extends Model
{
    protected $fillable = [
        'name',
        'email',
        'department',
        'teams_id',
        'electronic_seal',
    ];

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