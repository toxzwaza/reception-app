<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    /**
     * 出退勤画面用ログイン。
     * 管理画面と異なり StaffMember 登録は不要（全社員が対象）。
     *
     * POST /api/timeclock/login
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
        ]);

        $user = User::active()->find($validated['user_id']);

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

        $user = User::active()->find($validated['user_id']);

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

        $user = User::active()->find($validated['user_id']);

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

        $user = User::active()->find($validated['user_id']);

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

        $attendance->update(['clock_out_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => '退勤を記録しました。',
            'status' => $attendance->status,
            'clock_in_at' => $attendance->clock_in_at->format('H:i'),
            'clock_out_at' => $attendance->clock_out_at->format('H:i'),
        ]);
    }

    /**
     * 当日の全社員の出退勤情報を返す専用API。
     *
     * GET /api/attendances/today
     */
    public function today(): JsonResponse
    {
        $attendances = Attendance::today()
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
