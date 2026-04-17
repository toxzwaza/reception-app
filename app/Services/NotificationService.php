<?php

namespace App\Services;

use App\Models\NotificationSetting;
use App\Models\NotificationRecipient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * 通知を送信
     *
     * @param string $triggerEvent トリガーイベント
     * @param array $data 通知データ
     * @return void
     */
    public static function sendNotification(string $triggerEvent, array $data = []): void
    {
        Log::info('通知送信開始', [
            'trigger_event' => $triggerEvent,
            'data' => $data
        ]);

        // 該当する通知設定を取得
        $notificationSettings = NotificationSetting::where('trigger_event', $triggerEvent)
            ->where('is_active', true)
            ->with(['activeRecipients.user'])
            ->get();

        Log::info('通知設定取得結果', [
            'trigger_event' => $triggerEvent,
            'settings_count' => $notificationSettings->count()
        ]);

        foreach ($notificationSettings as $setting) {
            Log::info('通知設定処理中', [
                'setting_id' => $setting->id,
                'setting_name' => $setting->name,
                'recipients_count' => $setting->activeRecipients->count()
            ]);

            foreach ($setting->activeRecipients as $recipient) {
                try {
                    Log::info('受信者に通知送信中', [
                        'recipient_id' => $recipient->id,
                        'notification_type' => $recipient->notification_type,
                        'user_id' => $recipient->user_id
                    ]);
                    self::sendToRecipient($recipient, $data);
                } catch (\Exception $e) {
                    Log::error('通知送信エラー', [
                        'notification_setting_id' => $setting->id,
                        'recipient_id' => $recipient->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    /**
     * アポイント通知を送信（アポイントテーブルからメンション先を取得）
     *
     * @param string $triggerEvent トリガーイベント
     * @param array $data 通知データ
     * @param array $mentionData メンション情報（appointment_id, staff_member_email等）
     * @return void
     */
    public static function sendAppointmentNotification(string $triggerEvent, array $data = [], array $mentionData = []): void
    {
        Log::info('アポイント通知送信開始', [
            'trigger_event' => $triggerEvent,
            'data' => $data,
            'mention_data' => $mentionData
        ]);

        // 該当する通知設定を取得
        $notificationSettings = NotificationSetting::where('trigger_event', $triggerEvent)
            ->where('is_active', true)
            ->get();

        Log::info('アポイント通知設定取得結果', [
            'trigger_event' => $triggerEvent,
            'settings_count' => $notificationSettings->count()
        ]);

        foreach ($notificationSettings as $setting) {
            Log::info('アポイント通知設定処理中', [
                'setting_id' => $setting->id,
                'setting_name' => $setting->name
            ]);

            try {
                // アポイント通知の場合は、mentionDataから直接通知を送信
                self::sendAppointmentToRecipient($setting, $data, $mentionData);
            } catch (\Exception $e) {
                Log::error('アポイント通知送信エラー', [
                    'notification_setting_id' => $setting->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * アポイント通知を個別の受信者に送信
     *
     * @param NotificationSetting $setting
     * @param array $data
     * @param array $mentionData
     * @return void
     */
    private static function sendAppointmentToRecipient(NotificationSetting $setting, array $data, array $mentionData): void
    {
        // 通知設定から通知方法を取得（最初の有効な受信者の通知方法を使用）
        $recipient = $setting->activeRecipients()->first();
        if (!$recipient) {
            Log::warning('アポイント通知: 有効な受信者が見つかりません', [
                'setting_id' => $setting->id
            ]);
            return;
        }

        $notificationType = $recipient->notification_type;
        
        switch ($notificationType) {
            case 'phone':
                self::sendAppointmentPhoneNotification($setting, $data, $mentionData);
                break;
            case 'email':
                self::sendAppointmentEmailNotification($setting, $data, $mentionData);
                break;
            case 'teams':
                self::sendAppointmentTeamsNotification($setting, $data, $mentionData);
                break;
        }
    }

    /**
     * 個別の受信者に通知を送信
     *
     * @param NotificationRecipient $recipient
     * @param array $data
     * @return void
     */
    private static function sendToRecipient(NotificationRecipient $recipient, array $data): void
    {
        switch ($recipient->notification_type) {
            case 'phone':
                self::sendPhoneNotification($recipient, $data);
                break;
            case 'email':
                self::sendEmailNotification($recipient, $data);
                break;
            case 'teams':
                self::sendTeamsNotification($recipient, $data);
                break;
        }
    }

    /**
     * 電話通知を送信
     *
     * @param NotificationRecipient $recipient
     * @param array $data
     * @return void
     */
    private static function sendPhoneNotification(NotificationRecipient $recipient, array $data): void
    {
        // Twilioを使用した電話通知の実装
        // ここではログに記録するのみ
        Log::info('電話通知送信', [
            'recipient' => $recipient->user->name,
            'phone' => $recipient->notification_data,
            'data' => $data
        ]);
    }

    /**
     * メール通知を送信
     *
     * @param NotificationRecipient $recipient
     * @param array $data
     * @return void
     */
    private static function sendEmailNotification(NotificationRecipient $recipient, array $data): void
    {
        // メール通知の実装
        // ここではログに記録するのみ
        Log::info('メール通知送信', [
            'recipient' => $recipient->user->name,
            'email' => $recipient->notification_data,
            'data' => $data
        ]);
    }

    /**
     * Teams通知を送信
     *
     * @param NotificationRecipient $recipient
     * @param array $data
     * @return void
     */
    private static function sendTeamsNotification(NotificationRecipient $recipient, array $data): void
    {
        try {
            // Teams通知は常にメンション形式で送信（.envのTEAMS_WEBHOOK_URLを使用）
            self::sendTeamsMention($recipient, $data);
        } catch (\Exception $e) {
            Log::error('Teams通知送信エラー', [
                'recipient_id' => $recipient->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Teams Webhook通知を送信
     *
     * @param NotificationRecipient $recipient
     * @param array $data
     * @return void
     */
    private static function sendTeamsWebhook(NotificationRecipient $recipient, array $data): void
    {
        $message = self::formatTeamsMessage($data);
        
        $response = Http::timeout(10)->post($recipient->notification_data, [
            'text' => $message,
            '@type' => 'MessageCard',
            '@context' => 'http://schema.org/extensions',
            'themeColor' => '0076D7',
            'summary' => '通知',
            'sections' => [
                [
                    'activityTitle' => '通知',
                    'activitySubtitle' => $message,
                    'markdown' => true
                ]
            ]
        ]);

        if ($response->successful()) {
            Log::info('Teams Webhook通知送信成功', [
                'recipient' => $recipient->user->name,
                'webhook_url' => $recipient->notification_data
            ]);
        } else {
            Log::error('Teams Webhook通知送信失敗', [
                'recipient' => $recipient->user->name,
                'status' => $response->status(),
                'response' => $response->body()
            ]);
        }
    }

    /**
     * Teamsメンション通知を送信（TeamsNotificationService::notify() へ委譲）
     *
     * @param NotificationRecipient $recipient
     * @param array $data
     * @return void
     */
    private static function sendTeamsMention(NotificationRecipient $recipient, array $data): void
    {
        $mention = $recipient->notification_data;
        $messageText = self::formatTeamsMessage($data);

        // トリガーに応じたタイトル
        $title = match ($data['type'] ?? '') {
            'delivery_received' => '📦 納品書・受領書受信',
            'pickup_received' => '🚚 集荷伝票受信',
            'interview_call' => '📞 面接時通話',
            default => '🔔 通知',
        };

        $teams = app(TeamsNotificationService::class);
        $teams->notify(
            $mention ? [$mention] : [],
            $title,
            $messageText
        );
    }

    /**
     * アポイント電話通知を送信
     *
     * @param NotificationSetting $setting
     * @param array $data
     * @param array $mentionData
     * @return void
     */
    private static function sendAppointmentPhoneNotification(NotificationSetting $setting, array $data, array $mentionData): void
    {
        $phoneNumber = $mentionData['phone_number'] ?? '';
        
        Log::info('アポイント電話通知送信', [
            'setting_name' => $setting->name,
            'phone' => $phoneNumber,
            'data' => $data
        ]);
    }

    /**
     * アポイントメール通知を送信
     *
     * @param NotificationSetting $setting
     * @param array $data
     * @param array $mentionData
     * @return void
     */
    private static function sendAppointmentEmailNotification(NotificationSetting $setting, array $data, array $mentionData): void
    {
        $email = $mentionData['email'] ?? '';
        
        Log::info('アポイントメール通知送信', [
            'setting_name' => $setting->name,
            'email' => $email,
            'data' => $data
        ]);
    }

    /**
     * アポイントTeams通知を送信（TeamsNotificationService::notify() へ委譲）
     *
     * @param NotificationSetting $setting
     * @param array $data
     * @param array $mentionData
     * @return void
     */
    private static function sendAppointmentTeamsNotification(NotificationSetting $setting, array $data, array $mentionData): void
    {
        $mention = $mentionData['mention_id'] ?? $mentionData['email'] ?? '';
        $messageText = self::formatAppointmentTeamsMessage($data);

        $teams = app(TeamsNotificationService::class);
        $teams->notify(
            $mention ? [$mention] : [],
            '📅 来訪者チェックイン',
            $messageText
        );
    }

    /**
     * Teamsメッセージをフォーマット
     *
     * @param array $data
     * @return string
     */
    private static function formatTeamsMessage(array $data): string
    {
        $message = '';
        
        if (isset($data['type'])) {
            switch ($data['type']) {
                case 'delivery_received':
                    $message = "📦 納品書・受領書が受信されました\n";
                    if (isset($data['delivery_type'])) {
                        $message .= "種類: {$data['delivery_type']}\n";
                    }
                    if (isset($data['received_at'])) {
                        $message .= "受信日時: {$data['received_at']}\n";
                    }
                    break;
                    
                case 'pickup_received':
                    $message = "📋 集荷伝票が受信されました\n";
                    if (isset($data['picked_up_at'])) {
                        $message .= "集荷日時: {$data['picked_up_at']}\n";
                    }
                    break;
                    
                case 'interview_call':
                    $message = "📞 面接時の通話が必要です\n";
                    if (isset($data['visitor_name'])) {
                        $message .= "来訪者: {$data['visitor_name']}\n";
                    }
                    if (isset($data['phone'])) {
                        $message .= "電話番号: {$data['phone']}\n";
                    }
                    break;
                    
                default:
                    $message = "🔔 通知が発生しました\n";
                    break;
            }
        }
        
        return $message;
    }

    /**
     * アポイント用Teamsメッセージをフォーマット
     *
     * @param array $data
     * @return string
     */
    private static function formatAppointmentTeamsMessage(array $data): string
    {
        $message = '';
        
        if (isset($data['type'])) {
            switch ($data['type']) {
                case 'visitor_checkin':
                    $message = "✅ 来訪者がチェックインしました\n\n";
                    if (isset($data['reception_number'])) {
                        $message .= "受付番号: {$data['reception_number']}\n";
                    }
                    if (isset($data['company_name'])) {
                        $message .= "会社名: {$data['company_name']}\n";
                    }
                    if (isset($data['visitor_name'])) {
                        $message .= "訪問者名: {$data['visitor_name']}\n";
                    }
                    if (isset($data['staff_member_name'])) {
                        $message .= "担当者: {$data['staff_member_name']}\n";
                    }
                    if (isset($data['check_in_time'])) {
                        $message .= "チェックイン時刻: {$data['check_in_time']}\n";
                    }
                    if (isset($data['appointment_info'])) {
                        $message .= "\nアポイント情報:\n{$data['appointment_info']}";
                    }
                    break;
                default:
                    $message = "アポイント通知\n";
                    foreach ($data as $key => $value) {
                        if ($key !== 'type') {
                            $message .= "{$key}: {$value}\n";
                        }
                    }
                    break;
            }
        }
        
        return $message;
    }
}
