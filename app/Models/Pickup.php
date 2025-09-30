<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pickup extends Model
{
    protected $fillable = [
        'company_name',
        'slip_image',
        'sealed_slip_image',
        'qr_code_url',
        'staff_member_id',
        'picked_up_at',
    ];

    protected $casts = [
        'picked_up_at' => 'datetime',
    ];

    // 担当者とのリレーション
    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }
}