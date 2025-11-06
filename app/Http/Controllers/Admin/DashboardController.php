<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Appointment;
use App\Models\InterviewPhone;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * 管理画面のダッシュボードを表示
     */
    public function index(): Response
    {
        // 有効なお知らせを取得
        $announcements = Announcement::active()
            ->ordered()
            ->get();

        // 今日のアポイント数
        $todayAppointments = Appointment::whereDate('visit_date', today())->count();
        
        // 未チェックインのアポイント数
        $pendingAppointments = Appointment::where('is_checked_in', false)
            ->whereDate('visit_date', '>=', today())
            ->count();

        // 面接用電話番号の登録数
        // $activePhones = InterviewPhone::active()->count();

        return Inertia::render('Admin/Dashboard', [
            'announcements' => $announcements,
            'stats' => [
                'todayAppointments' => $todayAppointments,
                'pendingAppointments' => $pendingAppointments
            ],
        ]);
    }
}
