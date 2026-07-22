<?php

namespace App\Http\Controllers;

use App\Models\Visitor;
use App\Models\Appointment;
use App\Models\ScheduleEvent;
use App\Services\NotificationService;
use App\Services\TeamsNotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentController extends Controller
{
    // アポイントあり受付画面（旧: QR/受付番号）
    public function index(): Response
    {
        return Inertia::render('Appointment/Index');
    }

    // 新フロー：当日の予定を時間順に一覧表示
    public function today(): Response
    {
        $appointments = Appointment::with('staffMember')
            ->whereDate('visit_date', now()->toDateString())
            ->orderBy('visit_time')
            ->get()
            ->map(fn (Appointment $a) => [
                'id' => $a->id,
                'time' => optional($a->visit_time)->format('H:i'),
                'company_name' => $a->company_name,
                'visitor_name' => $a->visitor_name,
                'staff_name' => $a->staffMember->name ?? null,
                'purpose' => $a->purpose,
                'is_checked_in' => (bool) $a->is_checked_in,
            ])
            ->values();

        return Inertia::render('Appointment/Today', [
            'appointments' => $appointments,
            'today' => now()->format('Y年n月j日'),
        ]);
    }

    // 新フロー：来訪者が自分の予定をタップ → 担当者（＋施設予約の参加メンバー）へ通知＋チェックイン
    public function notifyArrival(Appointment $appointment)
    {
        // 宛先メール（担当スタッフ + 施設予約の参加メンバー）を収集
        $staff = $appointment->staffMember;
        $emails = [];
        if ($staff && !empty($staff->email)) {
            $emails[] = $staff->email;
        }

        $scheduleEvent = ScheduleEvent::with('participants')
            ->where('appointment_id', $appointment->id)
            ->latest('id')
            ->first();
        if ($scheduleEvent) {
            foreach ($scheduleEvent->participants as $participant) {
                if (!empty($participant->email)) {
                    $emails[] = $participant->email;
                }
            }
        }
        // 重複排除（大文字小文字を無視）
        $emails = array_values(array_intersect_key(
            $emails,
            array_unique(array_map('strtolower', $emails))
        ));

        // チェックイン記録
        if (!$appointment->is_checked_in) {
            $appointment->update(['is_checked_in' => true, 'checked_in_at' => now()]);
        }
        Visitor::create([
            'company_name' => $appointment->company_name,
            'visitor_name' => $appointment->visitor_name,
            'staff_member_id' => $appointment->staff_member_id,
            'visitor_type' => 'appointment',
            'reception_number' => $appointment->reception_number,
            'check_in_time' => now(),
        ]);

        // 緊急Teams通知（AkiTalk Bridge 経由・全通知緊急扱い）
        $title = '👤 来客が到着しました';
        $lines = [
            '来訪者: ' . ($appointment->visitor_name ?: '—'),
            '会社名: ' . ($appointment->company_name ?: '—'),
            '担当者: ' . ($staff->name ?? '未設定'),
            '予定時刻: ' . (optional($appointment->visit_time)->format('H:i') ?: '—'),
            '到着時刻: ' . now()->format('Y年m月d日 H:i'),
        ];
        if ($appointment->purpose) {
            $lines[] = '用件: ' . $appointment->purpose;
        }

        app(TeamsNotificationService::class)->notify($emails, $title, implode("\n", $lines));

        return redirect()->route('appointment.today')->with('arrival', [
            'staff_name' => $staff->name ?? '担当者',
            'visitor_name' => $appointment->visitor_name,
        ]);
    }

    // QRコードでチェックイン
    public function checkInQr(Request $request)
    {
        $validated = $request->validate([
            'qr_data' => 'required|string',
        ]);

        // QRコードデータをパースして訪問者情報を取得
        // フォーマット例: "visitor_id:1234"
        $qrData = json_decode($validated['qr_data'], true);
        
        if (!$qrData || !isset($qrData['visitor_id'])) {
            return back()->withErrors(['qr_data' => '無効なQRコードです']);
        }

        // 事前登録された訪問者情報を取得
        $preRegisteredVisitor = $this->findPreRegisteredVisitor($qrData['visitor_id']);
        
        if (!$preRegisteredVisitor) {
            return back()->withErrors(['qr_data' => '訪問者情報が見つかりません']);
        }

        // チェックイン記録を作成
        $visitor = Visitor::create([
            'company_name' => $preRegisteredVisitor['company_name'],
            'visitor_name' => $preRegisteredVisitor['visitor_name'],
            'staff_member_id' => $preRegisteredVisitor['staff_member_id'],
            'visitor_type' => 'appointment',
            'check_in_time' => now(),
        ]);

        // アポイント通知を送信
        $this->sendAppointmentNotification($visitor);

        // 完了画面でアポイント情報（施設・時間・担当者・メッセージ）を表示するためIDを渡す
        return redirect()->route('visitor.complete')
            ->with('completed_appointment_id', $preRegisteredVisitor['id']);
    }

    // 受付番号でチェックイン
    public function checkInNumber(Request $request)
    {
        $validated = $request->validate([
            'reception_number' => 'required|string|size:4|regex:/^[0-9]{4}$/',
        ]);

        // 受付番号で事前登録された訪問者情報を取得
        $preRegisteredVisitor = $this->findByReceptionNumber($validated['reception_number']);
        
        if (!$preRegisteredVisitor) {
            return back()->withErrors(['reception_number' => '受付番号が見つかりません']);
        }

        // チェックイン記録を作成
        $visitor = Visitor::create([
            'company_name' => $preRegisteredVisitor['company_name'],
            'visitor_name' => $preRegisteredVisitor['visitor_name'],
            'staff_member_id' => $preRegisteredVisitor['staff_member_id'],
            'visitor_type' => 'appointment',
            'reception_number' => $validated['reception_number'],
            'check_in_time' => now(),
        ]);

        // アポイント通知を送信
        $this->sendAppointmentNotification($visitor);

        // 完了画面でアポイント情報（施設・時間・担当者・メッセージ）を表示するためIDを渡す
        return redirect()->route('visitor.complete')
            ->with('completed_appointment_id', $preRegisteredVisitor['id']);
    }

    // 事前登録訪問者をIDで検索
    private function findPreRegisteredVisitor($visitorId)
    {
        $appointment = \App\Models\Appointment::find($visitorId);

        if (!$appointment) {
            return null;
        }

        return [
            'id' => $appointment->id,
            'company_name' => $appointment->company_name,
            'visitor_name' => $appointment->visitor_name,
            'staff_member_id' => $appointment->staff_member_id,
        ];
    }

    // 事前登録訪問者を受付番号で検索
    private function findByReceptionNumber($number)
    {
        $appointment = \App\Models\Appointment::where('reception_number', $number)
            ->where('is_checked_in', false)
            ->first();
        
        if (!$appointment) {
            return null;
        }
        
        // チェックイン済みフラグを更新
        $appointment->update([
            'is_checked_in' => true,
            'checked_in_at' => now(),
        ]);

        return [
            'id' => $appointment->id,
            'company_name' => $appointment->company_name,
            'visitor_name' => $appointment->visitor_name,
            'staff_member_id' => $appointment->staff_member_id,
        ];
    }

    /**
     * アポイント通知を送信
     *
     * @param Visitor $visitor
     * @return void
     */
    private function sendAppointmentNotification(Visitor $visitor): void
    {
        // アポイント情報を取得
        $appointment = Appointment::where('reception_number', $visitor->reception_number)->first();
        
        if (!$appointment) {
            return;
        }

        // 担当スタッフ情報を取得
        // staff_member_id は users テーブルのIDを指す（Appointment::staffMember は belongsTo(User::class)）
        $staffUser = $appointment->staffMember;
        $staffEmail = $staffUser->email ?? '';

        // 通知データを準備
        $data = [
            'type' => 'visitor_checkin',
            'reception_number' => $visitor->reception_number,
            'company_name' => $visitor->company_name,
            'visitor_name' => $visitor->visitor_name,
            'staff_member_name' => $staffUser->name ?? '未設定',
            'check_in_time' => $visitor->check_in_time->format('Y年m月d日 H:i'),
            'appointment_info' => $appointment->appointment_info ?? '',
        ];

        // メンション先メールを収集（担当スタッフ + 施設予約の参加メンバー）
        // 施設予約を伴うアポイントは participants に同席者が登録されている（担当スタッフ含む）。
        $mentionEmails = [];
        if (!empty($staffEmail)) {
            $mentionEmails[] = $staffEmail;
        }

        $scheduleEvent = ScheduleEvent::with('participants')
            ->where('appointment_id', $appointment->id)
            ->latest('id')
            ->first();
        if ($scheduleEvent) {
            foreach ($scheduleEvent->participants as $participant) {
                if (!empty($participant->email)) {
                    $mentionEmails[] = $participant->email;
                }
            }
        }

        // 重複を排除（大文字小文字を無視）
        $mentionEmails = array_values(array_intersect_key(
            $mentionEmails,
            array_unique(array_map('strtolower', $mentionEmails))
        ));

        // メンション情報を準備
        $mentionData = [
            'mention_ids' => $mentionEmails,                 // 担当 + 参加メンバー（配列）
            'mention_id' => $mentionEmails[0] ?? '',         // 後方互換
            'email' => $staffEmail,
            'phone_number' => '', // 必要に応じて追加
        ];

        // アポイント通知を送信
        NotificationService::sendAppointmentNotification('visitor_checkin', $data, $mentionData);
    }

}

