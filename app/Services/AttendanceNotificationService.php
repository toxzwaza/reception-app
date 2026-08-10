<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\ScheduleEvent;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * 出退勤打刻時の本人宛て Teams 通知。
 * 送信は AkiTalk Bridge（TeamsNotificationService）経由。
 * 通知の失敗が打刻を妨げないよう、呼び出し側は try/catch で握りつぶす前提。
 */
class AttendanceNotificationService
{
    public function __construct(
        private readonly TeamsNotificationService $teams,
    ) {
    }

    /**
     * 出勤通知：挨拶＋本日の施設予定（参加者として紐づくもの）
     */
    public function notifyClockIn(User $user, Attendance $attendance): bool
    {
        $lines = [
            'おはようございます。出勤登録が完了しました。',
            '',
            '【本日の予定】',
        ];

        $events = ScheduleEvent::where('date', $attendance->work_date->toDateString())
            ->whereHas('participants', fn ($query) => $query->where('users.id', $user->id))
            ->with('facility:id,name')
            ->orderBy('start_datetime')
            ->get();

        if ($events->isEmpty()) {
            $lines[] = '本日の予定はありません。';
        } else {
            foreach ($events as $event) {
                $start = Carbon::parse($event->start_datetime)->format('H:i');
                $end = Carbon::parse($event->end_datetime)->format('H:i');
                $facility = $event->facility?->name ? "（{$event->facility->name}）" : '';
                $lines[] = "{$start} ~ {$end} {$event->title}{$facility}";
            }
        }

        return $this->teams->notify(
            $user->email,
            '出勤登録完了',
            implode("\n", $lines),
            null,
            urgent: false,
        );
    }

    /**
     * 退勤通知：出退勤情報＋残業時間・累計残業時間（前月21日〜当月20日締め）
     */
    public function notifyClockOut(User $user, Attendance $attendance, int $cumulativeOvertimeMinutes): bool
    {
        $lines = [
            '退勤処理が完了しました。',
            'お疲れ様でした。',
            '',
            '【出退勤情報】',
            '出勤: ' . $attendance->clock_in_at->format('H:i'),
            '退勤: ' . $attendance->clock_out_at->format('H:i'),
            '残業時間: ' . $this->formatMinutes($attendance->overtime_minutes),
            '累計残業時間: ' . $this->formatMinutes($cumulativeOvertimeMinutes),
        ];

        return $this->teams->notify(
            $user->email,
            '退勤処理完了',
            implode("\n", $lines),
            null,
            urgent: false,
        );
    }

    /**
     * 分を「x時間x分」表記に変換
     */
    private function formatMinutes(int $minutes): string
    {
        return intdiv($minutes, 60) . '時間' . ($minutes % 60) . '分';
    }
}
