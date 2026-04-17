<?php

namespace App\Http\Controllers;

use App\Models\NotificationSetting;
use App\Models\NotificationRecipient;
use App\Services\TeamsNotificationService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class InterviewController extends Controller
{
    protected $teamsNotification;

    public function __construct(TeamsNotificationService $teamsNotification)
    {
        $this->teamsNotification = $teamsNotification;
    }

    /**
     * 面接受付画面を表示する。
     * 【重要】この GET では通知を一切発火しない（暴発対策）。
     * 実際の通知発火は notifyStaff() POST のみ。
     */
    public function index(): Response
    {
        // 電話番号は表示のみ（UI 側で明示的にボタンを押したときに初めて発信する）
        $interviewPhones = $this->getInterviewPhones();

        return Inertia::render('Interview/Index', [
            'interviewPhones' => $interviewPhones,
        ]);
    }

    /**
     * 「担当者を呼ぶ」ボタン押下時に呼ばれる POST エンドポイント。
     * Teams 通知のみ担当（Twilio 発信はフロント側で別途実行）。
     */
    public function notifyStaff(): JsonResponse
    {
        $ok = $this->teamsNotification->notifyInterviewArrival();

        return response()->json([
            'status' => $ok ? 'success' : 'failed',
            'message' => $ok ? '担当者にTeams通知を送信しました' : 'Teams通知の送信に失敗しました',
        ], $ok ? 200 : 500);
    }

    /**
     * 面接用の電話番号を取得
     *
     * @return \Illuminate\Support\Collection
     */
    private function getInterviewPhones()
    {
        // visitor_checkin トリガーの有効な通知設定を取得
        $notificationSettings = NotificationSetting::where('trigger_event', 'visitor_checkin')
            ->where('is_active', true)
            ->get();

        $phones = collect();

        foreach ($notificationSettings as $setting) {
            // 電話通知の受信者を取得
            $phoneRecipients = $setting->activeRecipients()
                ->where('notification_type', 'phone')
                ->get();

            foreach ($phoneRecipients as $recipient) {
                // TwilioAutoCall コンポーネント用のフォーマットに変換
                $phones->push([
                    'id' => $recipient->id,
                    'phone_number' => $recipient->notification_data,
                    'contact_person' => $recipient->user->name ?? '担当者',
                    'department_name' => $setting->name,
                ]);
            }
        }

        return $phones;
    }
}
