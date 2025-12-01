<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationSetting extends Model
{
    /**
     * 使用するデータベース接続名
     *
     * @var string
     */


    protected $fillable = [
        'name',
        'description',
        'trigger_event',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    // 通知受信者とのリレーション
    public function recipients(): HasMany
    {
        return $this->hasMany(NotificationRecipient::class);
    }

    // アクティブな通知受信者のみ取得
    public function activeRecipients(): HasMany
    {
        return $this->hasMany(NotificationRecipient::class)->where('is_active', true);
    }

    // 特定の通知方法の受信者を取得
    public function recipientsByType(string $type): HasMany
    {
        return $this->activeRecipients()->where('notification_type', $type);
    }

    // トリガーイベントの定数
    public const TRIGGER_EVENTS = [
        'delivery_received' => '納品書・受領書受信',
        'pickup_received' => '集荷伝票受信',
        'interview_call' => '面接時通話',
        'visitor_checkin' => '来訪者チェックイン',
        'appointment_reminder' => 'アポイントリマインダー',
    ];

    // トリガーイベント名を取得
    public function getTriggerEventNameAttribute(): string
    {
        return self::TRIGGER_EVENTS[$this->trigger_event] ?? $this->trigger_event;
    }
}