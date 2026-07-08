<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Appointment;
use App\Models\Delivery;
use App\Models\Facility;
use App\Models\InitialOrder;
use App\Models\Pickup;
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
        $now = now();

        // 有効なお知らせ
        $announcements = Announcement::active()->ordered()->get();

        // ── 統計（KPI） ──────────────────────────────
        $todayAppointments = Appointment::whereDate('visit_date', $today)->count();
        $todayCheckedIn = Appointment::whereDate('visit_date', $today)
            ->where('is_checked_in', true)
            ->count();
        $todayNotCheckedIn = max($todayAppointments - $todayCheckedIn, 0);
        // 本日の会議室予定数（ScheduleEvent.date は文字列扱いのため文字列比較）
        $todayRoomEvents = ScheduleEvent::where('date', $todayStr)->count();
        // 電子印が未押印の納品書・集荷伝票
        $unsealedDeliveries = Delivery::whereNull('sealed_document_image')->count();
        $unsealedPickups = Pickup::whereNull('sealed_slip_image')->count();
        // 未納品の発注データ（未納品=receive_flg が null/0、分納=2、削除除外）
        $pendingOrders = InitialOrder::where('del_flg', 0)
            ->where(function ($q) {
                $q->whereNull('receive_flg')->orWhere('receive_flg', 0)->orWhere('receive_flg', 2);
            })
            ->count();

        // ── 本日のアポイント一覧（時刻順） ──────────────
        $todayAppointmentList = Appointment::with('staffMember')
            ->whereDate('visit_date', $today)
            ->orderBy('visit_time')
            ->get(['id', 'reception_number', 'company_name', 'visitor_name', 'visit_time', 'is_checked_in', 'staff_member_id']);

        // ── 本日の会議室スケジュール（施設ごと） ─────────
        $roomSchedules = Facility::with(['scheduleEvents' => function ($q) use ($todayStr) {
            $q->where('date', $todayStr)->orderBy('start_datetime');
        }])->orderBy('id')->get(['id', 'name']);

        // ── 要対応アイテム ─────────────────────────────
        // 未押印の納品書
        $unsealedDeliveryList = Delivery::whereNull('sealed_document_image')
            ->orderByDesc('received_at')
            ->limit(6)
            ->get(['id', 'delivery_type', 'received_at']);
        // 未押印の集荷伝票
        $unsealedPickupList = Pickup::whereNull('sealed_slip_image')
            ->orderByDesc('picked_up_at')
            ->limit(6)
            ->get(['id', 'picked_up_at']);
        // 紐づけ未完了の納品書（発注データが1件も紐づいていない納品書）
        $unlinkedDeliveryList = Delivery::whereDoesntHave('initialOrders')
            ->where('delivery_type', '納品書')
            ->orderByDesc('received_at')
            ->limit(6)
            ->get(['id', 'delivery_type', 'received_at']);
        // 訪問時刻を過ぎても未チェックインのアポイント（本日）
        $overdueAppointments = Appointment::with('staffMember')
            ->whereDate('visit_date', $today)
            ->where('is_checked_in', false)
            ->whereTime('visit_time', '<', $now->format('H:i:s'))
            ->orderBy('visit_time')
            ->get(['id', 'reception_number', 'company_name', 'visitor_name', 'visit_time', 'staff_member_id']);

        // ── 最近受信した納品書・集荷伝票（タイムライン） ──
        $recentDeliveries = Delivery::orderByDesc('received_at')
            ->limit(6)
            ->get(['id', 'delivery_type', 'received_at', 'sealed_document_image'])
            ->map(fn ($d) => [
                'kind' => 'delivery',
                'id' => $d->id,
                'label' => $d->delivery_type ?: '納品書',
                'datetime' => $d->received_at,
                'sealed' => ! empty($d->sealed_document_image),
            ]);
        $recentPickups = Pickup::orderByDesc('picked_up_at')
            ->limit(6)
            ->get(['id', 'picked_up_at', 'sealed_slip_image'])
            ->map(fn ($p) => [
                'kind' => 'pickup',
                'id' => $p->id,
                'label' => '集荷伝票',
                'datetime' => $p->picked_up_at,
                'sealed' => ! empty($p->sealed_slip_image),
            ]);
        // 2種をマージして日時降順で上位8件
        $recentDocuments = $recentDeliveries->concat($recentPickups)
            ->sortByDesc('datetime')
            ->values()
            ->take(8);

        // ── 週次サマリー（過去7日） ────────────────────
        $weekdayLabels = ['日', '月', '火', '水', '木', '金', '土'];
        $weekly = collect(range(6, 0))->map(function ($daysAgo) use ($today, $weekdayLabels) {
            $day = $today->copy()->subDays($daysAgo);
            $dayStr = $day->toDateString();

            return [
                'label' => $day->format('n/j'),
                'weekday' => $weekdayLabels[$day->dayOfWeek],
                'isToday' => $daysAgo === 0,
                'visits' => Appointment::whereDate('checked_in_at', $dayStr)->count(),
                'deliveries' => Delivery::whereDate('received_at', $dayStr)->count(),
                'pickups' => Pickup::whereDate('picked_up_at', $dayStr)->count(),
            ];
        })->values();

        return Inertia::render('Admin/Dashboard', [
            'announcements' => $announcements,
            'stats' => [
                'todayAppointments' => $todayAppointments,
                'todayCheckedIn' => $todayCheckedIn,
                'todayNotCheckedIn' => $todayNotCheckedIn,
                'todayRoomEvents' => $todayRoomEvents,
                'unsealedDeliveries' => $unsealedDeliveries,
                'unsealedPickups' => $unsealedPickups,
                'pendingOrders' => $pendingOrders,
            ],
            'todayAppointmentList' => $todayAppointmentList,
            'roomSchedules' => $roomSchedules,
            'actionItems' => [
                'unsealedDeliveries' => $unsealedDeliveryList,
                'unsealedPickups' => $unsealedPickupList,
                'unlinkedDeliveries' => $unlinkedDeliveryList,
                'overdueAppointments' => $overdueAppointments,
            ],
            'recentDocuments' => $recentDocuments,
            'weekly' => $weekly,
        ]);
    }
}
