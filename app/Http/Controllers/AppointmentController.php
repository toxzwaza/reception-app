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

    // 新フロー：当日の施設スケジュール（Outlook同期）を時間順に一覧表示
    public function today(): Response
    {
        $today = now()->toDateString();

        $events = ScheduleEvent::with('facility')
            ->where('date', $today) // date は 'Y-m-d' 文字列で保持
            ->orderBy('start_datetime')
            ->orderBy('id')
            ->get()
            ->map(fn (ScheduleEvent $e) => [
                'id' => $e->id,
                'time' => $e->start_datetime,
                'end_time' => $e->end_datetime,
                'title' => $e->title,
                'facility' => $e->facility->name ?? null,
                'recipient_count' => count($this->recipientEmails($e)),
            ])
            ->values();

        return Inertia::render('Appointment/Today', [
            'events' => $events,
            'today' => now()->format('Y年n月j日'),
        ]);
    }

    // 新フロー：来訪者が自分の予定をタップ → 予定の主催者＋参加者全員へ通知
    public function notifyArrival(ScheduleEvent $scheduleEvent)
    {
        $emails = $this->recipientEmails($scheduleEvent);

        $facilityName = $scheduleEvent->facility->name ?? '—';
        $title = '👤 来客が到着しました';
        $lines = [
            '予定: ' . ($scheduleEvent->title ?: '—'),
            '施設: ' . $facilityName,
            '時間: ' . ($scheduleEvent->start_datetime ?: '—')
                . ($scheduleEvent->end_datetime ? '〜' . $scheduleEvent->end_datetime : ''),
            '到着時刻: ' . now()->format('Y年m月d日 H:i'),
        ];

        app(TeamsNotificationService::class)->notify($emails, $title, implode("\n", $lines));

        return redirect()->route('appointment.today')->with('arrival', [
            'title' => $scheduleEvent->title,
            'notified' => count($emails),
        ]);
    }

    // 施設スケジュールの通知先メール（主催者＋参加者。会議室メール除外・重複排除）
    private function recipientEmails(ScheduleEvent $event): array
    {
        $candidates = [];
        if (!empty($event->organizer_email)) {
            $candidates[] = $event->organizer_email;
        }
        foreach (($event->attendee_emails ?? []) as $addr) {
            if (is_string($addr) && $addr !== '') {
                $candidates[] = $addr;
            }
        }

        $seen = [];
        $out = [];
        foreach ($candidates as $addr) {
            $lower = strtolower($addr);
            if (str_contains($lower, 'meetingroom') || isset($seen[$lower])) {
                continue;
            }
            $seen[$lower] = true;
            $out[] = $addr;
        }

        return $out;
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

