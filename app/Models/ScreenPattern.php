<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 受付端末の画面パターン。設置場所ごとに表示する導線（機能）の組み合わせを保持する。
 */
class ScreenPattern extends Model
{
    protected $fillable = [
        'name',        // パターン名
        'description', // 補足説明
        'features',    // 有効な導線キーの配列
        'layout',      // 導線カードの配置（12列グリッド上の {i,x,y,w,h} 配列）
        'sort_order',  // 表示順
        'is_active',   // 選択肢に表示するか
    ];

    protected $casts = [
        'features' => 'array',
        'layout' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * 受付トップで表示/非表示を切り替えられる導線の一覧（キー => 表示名）。
     * Home/Index.vue のメニューキーと一致させること。
     */
    public const FEATURES = [
        'appointment_today' => 'アポイントありの方（当日一覧）',
        'other_visitor' => 'アポイントなしの方',
        'delivery_pickup' => '納品・集荷の方',
        'interview' => '面接の方',
        'department_call' => '担当部署を呼ぶ',
        'taxi' => 'タクシーを呼ぶ',
        'appointment' => 'アポイントあり（QR/受付番号・旧）',
    ];
}
