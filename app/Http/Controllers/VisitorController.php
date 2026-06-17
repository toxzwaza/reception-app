<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ScheduleEvent;
use App\Models\StaffMember;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VisitorController extends Controller
{
    // QRコード読取画面
    public function scanQr(): Response
    {
        return Inertia::render('Visitor/ScanQr');
    }

    // QRコードによるチェックイン処理
    public function checkIn(Request $request)
    {
        $validated = $request->validate([
            'visitor_id' => 'required|exists:visitors,id',
            'company_name' => 'required|string|max:255',
            'visitor_name' => 'required|string|max:255',
            'staff_member_id' => 'required|exists:staff_members,id',
        ]);

        $visitor = Visitor::create([
            'company_name' => $validated['company_name'],
            'visitor_name' => $validated['visitor_name'],
            'staff_member_id' => $validated['staff_member_id'],
            'check_in_time' => now(),
        ]);

        // Teams通知の送信
        $this->sendTeamsNotification($visitor);

        return redirect()->route('visitor.complete');
    }

    // 手動入力画面
    public function create(): Response
    {
        $staffMembers = StaffMember::all(['id', 'name', 'department']);
        return Inertia::render('Visitor/Create', [
            'staffMembers' => $staffMembers,
        ]);
    }

    // 来訪者情報の保存（手動入力）
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'visitor_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'business_card_image' => 'nullable|image|max:5120', // 5MB
            'staff_member_id' => 'required|exists:staff_members,id',
        ]);

        // 名刺画像の保存処理
        if ($request->hasFile('business_card_image')) {
            $path = $request->file('business_card_image')->store('business_cards', 'public');
            $validated['business_card_image'] = $path;
        }

        $visitor = Visitor::create($validated + [
            'check_in_time' => now(),
        ]);

        // Teams通知の送信
        $this->sendTeamsNotification($visitor);

        return redirect()->route('visitor.complete');
    }

    // 完了画面
    public function complete(Request $request): Response
    {
        // 受付完了画面に固定表示する「お客様へのデフォルトメッセージ」
        $defaultMessage = 'ただいま担当者をお呼びしております。もうしばらくお待ちください。';

        $staffName = null;   // 担当者
        $facilityName = null; // 施設名
        $visitTime = null;   // 時間
        $message = null;     // お客様宛てメッセージ

        // チェックイン直後にセッション経由で渡されたアポイントID
        $appointmentId = $request->session()->get('completed_appointment_id');

        if ($appointmentId) {
            $appointment = Appointment::with('staffMember')->find($appointmentId);

            if ($appointment) {
                $staffName = $appointment->staffMember->name ?? null;
                $message = $appointment->customer_message;

                // 施設予約（ScheduleEvent）があれば施設名と時間を取得
                $scheduleEvent = ScheduleEvent::with('facility')
                    ->where('appointment_id', $appointment->id)
                    ->latest('id')
                    ->first();

                if ($scheduleEvent) {
                    $facilityName = $scheduleEvent->facility->name ?? null;
                    $day = Carbon::parse($scheduleEvent->date)->format('n月j日');
                    $visitTime = "{$day} {$scheduleEvent->start_datetime}〜{$scheduleEvent->end_datetime}";
                } else {
                    // 施設予約が無い場合は訪問予定日時を表示
                    $day = Carbon::parse($appointment->visit_date)->format('n月j日');
                    $time = Carbon::parse($appointment->visit_time)->format('H:i');
                    $visitTime = "{$day} {$time}";
                }
            }
        }

        return Inertia::render('Visitor/Complete', [
            'staffName' => $staffName,
            'facilityName' => $facilityName,
            'visitTime' => $visitTime,
            // メッセージ未入力時はデフォルト文言
            'message' => ($message !== null && $message !== '') ? $message : $defaultMessage,
        ]);
    }

    // Teams通知の送信
    private function sendTeamsNotification(Visitor $visitor)
    {
        $teamsService = app(\App\Services\TeamsNotificationService::class);

        $staff = $visitor->staffMember;  // User モデル

        $checkinData = [
            'reception_number' => $visitor->reception_number,
            'company_name' => $visitor->company_name,
            'visitor_name' => $visitor->visitor_name,
            'staff_member_name' => $staff->name ?? '未設定',
            'staff_member_email' => $staff->email ?? null,  // メンション先
        ];

        $teamsService->sendCheckinNotification($checkinData);
    }
}