<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Visitor extends Model
{
    protected $fillable = [
        'company_name',
        'visitor_name',
        'phone',
        'business_card_image',
        'staff_member_id',
        'qr_code',
        'check_in_time',
        'check_out_time',
    ];

    protected $casts = [
        
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
    ];

    // 担当者とのリレーション
    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }
}