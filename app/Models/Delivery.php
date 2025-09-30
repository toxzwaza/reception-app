<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    protected $fillable = [
        'company_name',
        'delivery_type',
        'document_image',
        'sealed_document_image',
        'qr_code_url',
        'staff_member_id',
        'received_at',
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];

    // 担当者とのリレーション
    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }
}