<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 来客イベント（受付操作1回につき1件。中継役が取得して表示・チャイムを行う）
 */
class LanRelayEvent extends Model
{
    public const TYPE_APPOINTMENT = 'appointment';
    public const TYPE_WALK_IN = 'walk_in';
    public const TYPE_INTERVIEW = 'interview';

    public const REASON_INTERVIEW_COOLDOWN = 'interview_cooldown';
    public const REASON_TERMINAL_DISABLED = 'terminal_disabled';
    public const REASON_RATE_LIMITED = 'rate_limited';

    protected $fillable = [
        'type',
        'terminal',
        'camera',
        'company_name',
        'staff_name',
        'message',
        'occurred_at',
        'suppressed',
        'suppress_reason',
        'ack_display',
        'ack_chime',
        'ack_detail',
        'acked_at',
    ];

    protected $casts = [
        'terminal' => 'integer',
        'suppressed' => 'boolean',
        'occurred_at' => 'datetime',
        'acked_at' => 'datetime',
    ];

    // occurred_at はミリ秒まで保持する（age_sec の計算用）
    protected $dateFormat = 'Y-m-d H:i:s.v';
}
