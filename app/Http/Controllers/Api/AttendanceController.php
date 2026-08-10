<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Models\WorkTimeSetting;
use App\Services\AttendanceNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceNotificationService $notification,
    ) {
    }
    /**
     * 出退勤画面用ログイン。
     * 管理画面と異なり StaffMember 登録は不要。
     * 打刻対象はメール登録済みかつ役員を除く社員（User::timeclockTarget）。
     *
     * POST /api/timeclock/login
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
        ]);

        $user = User::timeclockTarget()->find($validated['user_id']);

        if (!$user) {
            throw ValidationException::withMessages([
                'user_id' => ['ユーザーが見つかりません。'],
            ]);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'emp_no' => $user->emp_no,
                'name' => $user->name,
                'group_id' => $user->group_id,
            ],
        ]);
    }

    /**
     * ログイン中ユーザーの当日の出退勤状況を取得。
     *
     * GET /api/timeclock/me?user_id=1
     */
    public function me(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
        ]);

        $user = User::timeclockTarget()->find($validated['user_id']);

        if (!$user) {
            return response()->json(['message' => 'ユーザーが見つかりません。'], 404);
        }

        $attendance = Attendance::today()->where('user_id', $user->id)->first();

        return response()->json([
            'date' => now()->toDateString(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
            'status' => $attendance?->status ?? Attendance::STATUS_NOT_CLOCKED_IN,
            'clock_in_at' => $attendance?->clock_in_at?->format('H:i'),
            'clock_out_at' => $attendance?->clock_out_at?->format('H:i'),
        ]);
    }

    /**
     * 出勤打刻。
     * 当日すでに出勤済み（出勤中・退勤済み）の場合はエラー。
     *
     * POST /api/timeclock/clock-in
     */
    public function clockIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
        ]);

        $user = User::timeclockTarget()->find($validated['user_id']);

        if (!$user) {
            return response()->json(['message' => 'ユーザーが見つかりません。'], 404);
        }

        $existing = Attendance::today()->where('user_id', $user->id)->first();

        if ($existing) {
            return response()->json([
                'message' => $existing->status === Attendance::STATUS_WORKING
                    ? 'すでに出勤済みです。'
                    : '本日はすでに退勤済みです。',
            ], 422);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now(),
        ]);

        // Teams通知（失敗しても打刻は成立させる）
        try {
            $this->notification->notifyClockIn($user, $attendance);
        } catch (\Throwable $e) {
            Log::warning('出勤Teams通知に失敗', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'message' => '出勤を記録しました。',
            'status' => $attendance->status,
            'clock_in_at' => $attendance->clock_in_at->format('H:i'),
            'clock_out_at' => null,
        ]);
    }

    /**
     * 退勤打刻。
     * ステータスが「出勤中」の場合に限り受け付ける。
     *
     * POST /api/timeclock/clock-out
     */
    public function clockOut(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
        ]);

        $user = User::timeclockTarget()->find($validated['user_id']);

        if (!$user) {
            return response()->json(['message' => 'ユーザーが見つかりません。'], 404);
        }

        $attendance = Attendance::today()->where('user_id', $user->id)->first();

        if (!$attendance) {
            return response()->json(['message' => '本日はまだ出勤していません。'], 422);
        }

        if ($attendance->status !== Attendance::STATUS_WORKING) {
            return response()->json(['message' => 'すでに退勤済みです。'], 422);
        }

        // 残業計算：勤務時間マスタの終業時刻（17:10）を過ぎた分を残業とする
        $clockOutAt = now();
        $overtimeMinutes = 0;
        $workEnd = WorkTimeSetting::current()?->work_end_time;
        if ($workEnd) {
            $workEndAt = $clockOutAt->copy()->setTimeFromTimeString($workEnd);
            if ($clockOutAt->greaterThan($workEndAt)) {
                $overtimeMinutes = $workEndAt->diffInMinutes($clockOutAt);
            }
        }

        $attendance->update([
            'clock_out_at' => $clockOutAt,
            'overtime_minutes' => $overtimeMinutes,
        ]);

        // Teams通知（失敗しても打刻は成立させる）
        try {
            $cumulative = Attendance::cumulativeOvertimeMinutes($user->id, $clockOutAt);
            $this->notification->notifyClockOut($user, $attendance->fresh(), $cumulative);
        } catch (\Throwable $e) {
            Log::warning('退勤Teams通知に失敗', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'message' => '退勤を記録しました。',
            'status' => $attendance->status,
            'clock_in_at' => $attendance->clock_in_at->format('H:i'),
            'clock_out_at' => $attendance->clock_out_at->format('H:i'),
        ]);
    }

    /**
     * 直前の打刻の取り消し。
     * 出勤中：出勤を取り消し（当日レコードを削除して未出勤に戻す）
     * 退勤済み：退勤を取り消し（退勤時刻をクリアして出勤中に戻す）
     *
     * POST /api/timeclock/cancel
     */
    public function cancel(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
        ]);

        $user = User::timeclockTarget()->find($validated['user_id']);

        if (!$user) {
            return response()->json(['message' => 'ユーザーが見つかりません。'], 404);
        }

        $attendance = Attendance::today()->where('user_id', $user->id)->first();

        if (!$attendance) {
            return response()->json(['message' => '取り消せる打刻がありません。'], 422);
        }

        if ($attendance->status === Attendance::STATUS_WORKING) {
            $attendance->delete();

            return response()->json([
                'success' => true,
                'message' => '出勤を取り消しました。',
                'status' => Attendance::STATUS_NOT_CLOCKED_IN,
                'clock_in_at' => null,
                'clock_out_at' => null,
            ]);
        }

        // 退勤済み → 退勤のみ取り消して出勤中に戻す（残業もクリアし再退勤時に再計算）
        $attendance->update(['clock_out_at' => null, 'overtime_minutes' => 0]);

        return response()->json([
            'success' => true,
            'message' => '退勤を取り消しました。',
            'status' => $attendance->status,
            'clock_in_at' => $attendance->clock_in_at->format('H:i'),
            'clock_out_at' => null,
        ]);
    }

    /**
     * 当日の出退勤情報を返す専用API（メールアドレス登録済みの社員のみ）。
     *
     * GET /api/attendances/today
     */
    public function today(): JsonResponse
    {
        $attendances = Attendance::today()
            ->whereHas('user', fn ($query) => $query->active()->withEmail())
            ->with('user:id,emp_no,name,group_id')
            ->orderBy('clock_in_at')
            ->get()
            ->map(fn (Attendance $attendance) => [
                'user_id' => $attendance->user_id,
                'emp_no' => $attendance->user?->emp_no,
                'name' => $attendance->user?->name,
                'group_id' => $attendance->user?->group_id,
                'status' => $attendance->status,
                'clock_in_at' => $attendance->clock_in_at?->format('H:i'),
                'clock_out_at' => $attendance->clock_out_at?->format('H:i'),
            ]);

        return response()->json([
            'date' => now()->toDateString(),
            'count' => [
                'total' => $attendances->count(),
                'working' => $attendances->where('status', Attendance::STATUS_WORKING)->count(),
                'clocked_out' => $attendances->where('status', Attendance::STATUS_CLOCKED_OUT)->count(),
            ],
            'attendances' => $attendances,
        ]);
    }
}
