<?php

namespace App\Http\Controllers;

use App\Models\Visitor;
use App\Models\StaffMember;
use App\Services\TeamsNotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentController extends Controller
{
    protected $teamsNotification;

    public function __construct(TeamsNotificationService $teamsNotification)
    {
        $this->teamsNotification = $teamsNotification;
    }
    // アポイントアリ受付画面
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

        // Teams通知を送信
        $this->teamsNotification->notifyAppointmentCheckIn($visitor);

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

        // Teams通知を送信
        $this->teamsNotification->notifyAppointmentCheckIn($visitor);

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

}

