<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    /**
     * 使用するデータベース接続名
     *
     * @var string
     */


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
}