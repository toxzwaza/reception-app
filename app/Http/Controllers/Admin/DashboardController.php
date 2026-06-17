<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Appointment;
use App\Models\Facility;
use App\Models\ScheduleEvent;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * 管理画面のダッシュボードを表示
     */
    public function index(): Response
    {
        $today = today();
        $todayStr = $today->toDateString();

        // 有効なお知らせ
        $announcements = Announcement::active()->ordered()->get();

        // 統計
        $todayAppointments = Appointment::whereDate('visit_date', $today)->count();
        $todayCheckedIn = Appointment::whereDate('visit_date', $today)
            ->where('is_checked_in', true)
            ->count();
        $pendingAppointments = Appointment::where('is_checked_in', false)
            ->whereDate('visit_date', '>=', $today)
            ->count();
        // 本日の会議室予定数（ScheduleEvent.date は文字列扱いのため文字列比較）
        $todayRoomEvents = ScheduleEvent::where('date', $todayStr)->count();

        // 本日のアポイント一覧（時刻順）
        $todayAppointmentList = Appointment::with('staffMember')
            ->whereDate('visit_date', $today)
            ->orderBy('visit_time')
            ->get(['id', 'reception_number', 'company_name', 'visitor_name', 'visit_time', 'is_checked_in', 'staff_member_id']);

        // 本日の会議室スケジュール（施設ごと）
        $roomSchedules = Facility::with(['scheduleEvents' => function ($q) use ($todayStr) {
            $q->where('date', $todayStr)->orderBy('start_datetime');
        }])->orderBy('id')->get(['id', 'name']);

        return Inertia::render('Admin/Dashboard', [
            'announcements' => $announcements,
            'stats' => [
                'todayAppointments' => $todayAppointments,
                'todayCheckedIn' => $todayCheckedIn,
                'pendingAppointments' => $pendingAppointments,
                'todayRoomEvents' => $todayRoomEvents,
            ],
            'todayAppointmentList' => $todayAppointmentList,
            'roomSchedules' => $roomSchedules,
        ]);
    }
}
