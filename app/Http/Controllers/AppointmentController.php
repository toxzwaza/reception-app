<?php

namespace App\Http\Controllers;

use App\Models\Visitor;
use App\Models\StaffMember;
use App\Models\Appointment;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentController extends Controller
{
    // アポイントあり受付画面
    public function index(): Response
    {
        return Inertia::render('Appointment/Index');
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

        return redirect()->route('visitor.complete');
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

        return redirect()->route('visitor.complete');
    }

    // 事前登録訪問者をIDで検索
    private function findPreRegisteredVisitor($visitorId)
    {
        $appointment = \App\Models\Appointment::find($visitorId);
        
        if (!$appointment) {
            return null;
        }
        
        return [
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

        // スタッフメンバー情報を取得
        $staffMember = StaffMember::with('user')->find($appointment->staff_member_id);
        
        // 通知データを準備
        $data = [
            'type' => 'visitor_checkin',
            'reception_number' => $visitor->reception_number,
            'company_name' => $visitor->company_name,
            'visitor_name' => $visitor->visitor_name,
            'staff_member_name' => $staffMember ? $staffMember->user->name : '未設定',
            'check_in_time' => $visitor->check_in_time->format('Y年m月d日 H:i'),
            'appointment_info' => $appointment->appointment_info ?? '',
        ];

        // メンション情報を準備
        $mentionData = [
            'mention_id' => $staffMember ? $staffMember->user->email : '',
            'email' => $staffMember ? $staffMember->user->email : '',
            'phone_number' => '', // 必要に応じて追加
        ];

        // アポイント通知を送信
        NotificationService::sendAppointmentNotification('visitor_checkin', $data, $mentionData);
    }

}

