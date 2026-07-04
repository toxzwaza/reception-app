<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 在庫の格納先ごとの数量（生産管理系DBと共有するテーブル）
 * stock_id + storage_address_id ごとに quantity（在庫数）を持つ。
 */
class StockStorage extends Model
{
    protected $table = 'stock_storages';

    protected $fillable = [
        'stock_id',
        'storage_address_id',
        'quantity',
        'reorder_point',
    ];

    // 格納先（棚番地）とのリレーション
    public function storageAddress(): BelongsTo
    {
        return $this->belongsTo(StorageAddress::class, 'storage_address_id');
    }
}
