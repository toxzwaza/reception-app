<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 集荷依頼。管理画面で事前に登録し、受付端末の集荷画面で一覧から選択して集荷する。
 */
class PickupRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requester_name',    // 依頼者（担当者名）
        'requester_group_id', // 依頼者の所属部署（groups.id）
        'item',              // 物品
        'item_image',        // 物品画像の保存パス
        'storage_location',  // 置き場所
        'contact_phone',     // 問い合わせ電話番号
        'memo',              // 備考
        'status',            // pending:未集荷 / completed:集荷済み
        'pickup_id',         // 集荷実施時に紐づく pickups.id
        'completed_at',      // 集荷完了日時
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    // 未集荷のみ
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
