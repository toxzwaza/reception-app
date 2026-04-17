<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Group;
use App\Models\NotificationSetting;

/**
 * Teams への Incoming Webhook 通知を一元化するサービス。
 *
 * 設計方針（参考: 秘書部門/01_一時フォルダ/Notify/notify.py）:
 *   同 Python スクリプトが本番稼働している実装に合わせ、
 *   ペイロード構造を統一し「動いているもの」と同形式に固定する。
 *
 *   共通エントリポイント: notify($mentionIds, $title, $message, $url = null)
 *   既存の notifyInterviewArrival 等はこの notify() を内部で呼ぶ薄いラッパー。
 */
class TeamsNotificationService
{
    /**
     * Webhook URL の取得。config > env の順で評価。
     */
    private function getWebhookUrl(): ?string
    {
        return config('services.teams.webhook_url') ?: env('TEAMS_WEBHOOK_URL');
    }

    /**
     * ★ 汎用通知送信メソッド（参考 Python スクリプトと同一構造）
     *
     * @param string|array $mentionIds メンション対象 email（単一 or 配列）。空可。
     * @param string $title タイトル（デフォルト色）
     * @param string $message 本文（good 色、大きめ）
     * @param string|null $url 任意。末尾に「[受付システム]($url)」リンクを追加
     * @return bool 送信成功時 true
     */
    public function notify(string|array $mentionIds, string $title, string $message, ?string $url = null): bool
    {
        $webhookUrl = $this->getWebhookUrl();
        if (!$webhookUrl) {
            Log::warning('Teams webhook URL not configured');
            return false;
        }

        // mentionIds を配列に正規化
        if (is_string($mentionIds)) {
            $mentionIds = $mentionIds === '' ? [] : [$mentionIds];
        }
        $mentionIds = array_values(array_filter($mentionIds, fn($id) => !empty($id)));

        // メンションエンティティとテキストの生成
        $mentions = [];
        $mentionTextParts = [];
        foreach ($mentionIds as $id) {
            $mentions[] = [
                'type' => 'mention',
                'text' => "<at>{$id}</at>",
                'mentioned' => [
                    'id' => $id,
                    'name' => $id,
                ],
            ];
            $mentionTextParts[] = "@<at>{$id}</at>";
        }
        $mentionText = implode(' ', $mentionTextParts);

        // Adaptive Card body 構築
        $body = [];

        // メンションがある場合は最上部に大きく表示
        if ($mentionText !== '') {
            $body[] = [
                'type' => 'TextBlock',
                'text' => $mentionText,
                'color' => 'attention',
                'size' => 'large',
                'wrap' => true,
            ];
        }

        // タイトル
        $body[] = [
            'type' => 'TextBlock',
            'text' => $title,
            'color' => 'default',
            'size' => 'default',
            'wrap' => true,
        ];

        // 本文メッセージ
        $body[] = [
            'type' => 'TextBlock',
            'text' => $message,
            'color' => 'good',
            'size' => 'medium',
            'wrap' => true,
        ];

        // 任意 URL 追加
        if ($url) {
            $body[] = [
                'type' => 'TextBlock',
                'text' => "[受付システム]({$url})",
                'color' => 'accent',
                'size' => 'medium',
                'wrap' => true,
            ];
        }

        $payload = [
            'type' => 'message',
            'attachments' => [[
                'contentType' => 'application/vnd.microsoft.card.adaptive',
                'content' => [
                    'type' => 'AdaptiveCard',
                    'body' => $body,
                    '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
                    'version' => '1.0',
                    'msteams' => [
                        'entities' => $mentions,
                    ],
                ],
            ]],
        ];

        try {
            $response = Http::timeout(10)
                ->withOptions(['verify' => false])  // 社内 SSL 互換用
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($webhookUrl, $payload);

            if ($response->successful()) {
                Log::info('Teams notification sent', [
                    'title' => $title,
                    'mention_count' => count($mentionIds),
                    'status' => $response->status(),
                ]);
                return true;
            }

            Log::error('Teams notification failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error('Teams notification exception: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return false;
        }
    }

    // ========================================================================
    // 以下、トリガー別のラッパー（内部で notify() を呼ぶ）
    // ========================================================================

    /**
     * 面接受付者到着（明示的なボタン押下時のみ呼ばれる）
     */
    public function notifyInterviewArrival(): bool
    {
        $mentionIds = $this->getMentionIdsForTrigger('visitor_checkin');

        $title = '👥 面接受付者到着';
        $message = "面接の方が受付に到着しました。担当者の方は受付までお越しください。\n"
                 . "到着時刻: " . now()->format('Y年m月d日 H:i');

        return $this->notify($mentionIds->toArray(), $title, $message);
    }

    /**
     * アポイントあり来訪チェックイン
     */
    public function sendCheckinNotification(array $data): bool
    {
        $mentionIds = [];
        if (!empty($data['staff_member_email'])) {
            $mentionIds[] = $data['staff_member_email'];
        }

        $title = '✅ 来訪者チェックイン';

        $lines = [];
        $lines[] = '会社名: ' . ($data['company_name'] ?? '—');
        $lines[] = '訪問者名: ' . ($data['visitor_name'] ?? '—');
        $lines[] = '担当者: ' . ($data['staff_member_name'] ?? '未設定');
        $lines[] = '受付番号: ' . ($data['reception_number'] ?? '—');
        $lines[] = 'チェックイン時刻: ' . now()->format('Y年m月d日 H:i');
        if (!empty($data['appointment_info'])) {
            $lines[] = '';
            $lines[] = 'アポイント情報:';
            $lines[] = $data['appointment_info'];
        }

        return $this->notify($mentionIds, $title, implode("\n", $lines));
    }

    /**
     * 部署の全員へ訪問者到着を通知（アポなし来訪）
     */
    public function notifyDepartmentVisitor(array $visitorData, int $groupId): bool
    {
        // 部署ユーザーのメールを抽出（User モデルに email カラムがある想定）
        $users = User::where('group_id', $groupId)
            ->where('del_flg', 0)
            ->get();
        $mentionIds = $users->pluck('email')->filter()->values()->toArray();

        $group = Group::find($groupId);
        $groupName = $group->name ?? '部署';

        $title = '👥 来訪者到着（' . $groupName . '宛）';

        $lines = [];
        $lines[] = '会社名: ' . ($visitorData['company_name'] ?? '—');
        $lines[] = '訪問者名: ' . ($visitorData['visitor_name'] ?? '—');
        if (!empty($visitorData['number_of_people'])) {
            $lines[] = '人数: ' . $visitorData['number_of_people'] . '名';
        }
        if (!empty($visitorData['purpose'])) {
            $lines[] = '用件: ' . $visitorData['purpose'];
        }
        $lines[] = 'チェックイン時刻: ' . now()->format('Y年m月d日 H:i');

        return $this->notify($mentionIds, $title, implode("\n", $lines));
    }

    /**
     * 新規アポイント登録通知（管理画面からの登録時）
     */
    public function sendAppointmentNotification(array $appointmentData): bool
    {
        $mentionIds = [];
        if (!empty($appointmentData['staff_member_email'])) {
            $mentionIds[] = $appointmentData['staff_member_email'];
        }

        $title = '📅 新規アポイント登録';

        $lines = [];
        $lines[] = '受付番号: ' . ($appointmentData['reception_number'] ?? '—');
        $lines[] = '会社名: ' . ($appointmentData['company_name'] ?? '—');
        $lines[] = '訪問者名: ' . ($appointmentData['visitor_name'] ?? '—');
        $lines[] = '担当者: ' . ($appointmentData['staff_member_name'] ?? '未設定');
        $lines[] = '訪問予定日時: ' . ($appointmentData['visit_date'] ?? '') . ' ' . ($appointmentData['visit_time'] ?? '');
        if (!empty($appointmentData['purpose'])) {
            $lines[] = '訪問目的: ' . $appointmentData['purpose'];
        }

        // 管理画面URL
        $url = route('admin.appointments.index');

        return $this->notify($mentionIds, $title, implode("\n", $lines), $url);
    }

    /**
     * テスト通知送信（管理画面からの動作確認用）
     */
    public function sendTestNotification(): bool
    {
        return $this->notify(
            [],
            '🧪 テスト通知',
            "受付システムからのテスト通知です。\n送信時刻: " . now()->format('Y年m月d日 H:i:s'),
        );
    }

    /**
     * notification_settings.trigger_event に紐づく email type recipient の ID を取得
     */
    private function getMentionIdsForTrigger(string $triggerEvent): \Illuminate\Support\Collection
    {
        $ids = collect();
        $settings = NotificationSetting::where('trigger_event', $triggerEvent)
            ->where('is_active', true)
            ->get();

        foreach ($settings as $setting) {
            $emailRecipients = $setting->activeRecipients()
                ->where('notification_type', 'email')
                ->get();
            foreach ($emailRecipients as $recipient) {
                if (!empty($recipient->notification_data)) {
                    $ids->push($recipient->notification_data);
                }
            }
        }

        return $ids->unique()->values();
    }

    /**
     * 旧メソッドとの互換性のためのラッパー（既存コード呼び出し保護）
     */
    public function notifyAppointmentCheckIn($visitor): bool
    {
        $appointment = \App\Models\Appointment::where('reception_number', $visitor->reception_number)->first();
        $appointmentInfo = '';
        if ($appointment) {
            $appointmentInfo = "訪問予定日: " . $appointment->visit_date->format('Y年m月d日') . "\n"
                             . "訪問予定時刻: " . $appointment->visit_time->format('H:i');
        }

        $staff = $visitor->staffMember ?? null;
        return $this->sendCheckinNotification([
            'reception_number' => $visitor->reception_number,
            'company_name' => $visitor->company_name,
            'visitor_name' => $visitor->visitor_name,
            'staff_member_name' => $staff->name ?? '未設定',
            'staff_member_email' => $staff->email ?? null,
            'appointment_info' => $appointmentInfo,
        ]);
    }
}
