<?php

namespace App\Http\Controllers;

use App\Models\NotificationSetting;
use App\Models\NotificationRecipient;
use App\Services\TeamsNotificationService;
use Inertia\Inertia;
use Inertia\Response;

class InterviewController extends Controller
{
    protected $teamsNotification;

    public function __construct(TeamsNotificationService $teamsNotification)
    {
        $this->teamsNotification = $teamsNotification;
    }

    // 面接受付画面
    public function index(): Response
    {
        // 面接担当者へ通知を送信（メンション付き）
        $this->teamsNotification->notifyInterviewArrival();
        
        // 有効な面接用電話番号を取得
        // trigger_event = 'visitor_checkin' かつ notification_type = 'phone' のデータを取得
        $interviewPhones = $this->getInterviewPhones();
        
        return Inertia::render('Interview/Index', [
            'interviewPhones' => $interviewPhones,
        ]);
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
                // TwilioAutoCallコンポーネントで使用するフォーマットに変換
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

