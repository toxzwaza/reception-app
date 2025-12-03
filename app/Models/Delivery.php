<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Delivery extends Model
{
    protected $fillable = [
        'delivery_type',
        'document_image',
        'sealed_document_image',
        'qr_code_url',
        'qr_code_file_path',
        'staff_member_id',
        'received_at',
        'initial_order_id',
    ];

    protected $casts = [
        'received_at' => 'datetime',
    ];

    // 担当者とのリレーション
    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(StaffMember::class);
    }

    // 発注データとの多対多リレーション
    public function initialOrders(): BelongsToMany
    {
        return $this->belongsToMany(InitialOrder::class, 'delivery_initial_order', 'delivery_id', 'initial_order_id')
            ->withTimestamps();
    }
}