<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    /** 勤務ステータス */
    public const STATUS_NOT_CLOCKED_IN = 'not_clocked_in'; // 未出勤
    public const STATUS_WORKING = 'working';               // 出勤中
    public const STATUS_CLOCKED_OUT = 'clocked_out';       // 退勤済み

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in_at',
        'clock_out_at',
    ];

    protected $casts = [
        'work_date' => 'date',
        'clock_in_at' => 'datetime',
        'clock_out_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 当日のレコードに絞り込む
     */
    public function scopeToday($query)
    {
        return $query->where('work_date', now()->toDateString());
    }

    /**
     * 勤務ステータスを返す（出勤中 / 退勤済み）
     */
    public function getStatusAttribute(): string
    {
        if ($this->clock_out_at) {
            return self::STATUS_CLOCKED_OUT;
        }

        return self::STATUS_WORKING;
    }
}
