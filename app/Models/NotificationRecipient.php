<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationRecipient extends Model
{
    protected $fillable = [
        'notification_setting_id',
        'user_id',
        'notification_type',
        'notification_data',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // 通知設定とのリレーション
    public function notificationSetting(): BelongsTo
    {
        return $this->belongsTo(NotificationSetting::class);
    }

    // ユーザーとのリレーション
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 通知方法の定数
    public const NOTIFICATION_TYPES = [
        'phone' => '電話',
        'email' => 'メール',
        'teams' => 'Teams',
    ];

    // 通知方法名を取得
    public function getNotificationTypeNameAttribute(): string
    {
        return self::NOTIFICATION_TYPES[$this->notification_type] ?? $this->notification_type;
    }

    // 通知データの検証
    public function validateNotificationData(): bool
    {
        switch ($this->notification_type) {
            case 'phone':
                return preg_match('/^[\d\-\+\(\)\s]+$/', $this->notification_data);
            case 'email':
                return filter_var($this->notification_data, FILTER_VALIDATE_EMAIL) !== false;
            case 'teams':
                return !empty($this->notification_data); // TeamsメンションIDやWebhook URL
            default:
                return false;
        }
    }
}