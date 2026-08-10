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
        'overtime_minutes',
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
     * 残業集計期間（前月21日〜当月20日締め）を返す。
     * 基準日が20日以前なら「前月21日〜当月20日」、21日以降なら「当月21日〜翌月20日」。
     *
     * @return array{0: string, 1: string} [開始日, 終了日]（Y-m-d）
     */
    public static function overtimePeriod(\Carbon\CarbonInterface $date): array
    {
        if ($date->day <= 20) {
            $start = $date->copy()->subMonthNoOverflow()->day(21);
            $end = $date->copy()->day(20);
        } else {
            $start = $date->copy()->day(21);
            $end = $date->copy()->addMonthNoOverflow()->day(20);
        }

        return [$start->toDateString(), $end->toDateString()];
    }

    /**
     * 指定ユーザーの当期（前月21日〜当月20日締め）の累計残業時間（分）を返す
     */
    public static function cumulativeOvertimeMinutes(int $userId, \Carbon\CarbonInterface $date): int
    {
        [$start, $end] = static::overtimePeriod($date);

        return (int) static::where('user_id', $userId)
            ->whereBetween('work_date', [$start, $end])
            ->sum('overtime_minutes');
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
