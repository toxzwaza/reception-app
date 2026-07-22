<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;



    protected $fillable = [
        'name',
        'phone_number', // アポなし来訪時に部署代表電話へTwilioでつなぐための番号
        'display_order', // 受付画面・部署電話番号管理での表示順（未設定は末尾）
    ];

    /**
     * この部署に属するユーザーを取得
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
