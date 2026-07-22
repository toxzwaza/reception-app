<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Group;
use App\Models\NotificationSetting;

/**
 * Teams 通知を一元化するサービス。
 *
 * 送信経路: AkiTalk Bridge（社内Notifyバッチ notify.py と同一）
 *   POST {services.akitalk_bridge.url}  ヘッダ x-api-key で認証
 *   payload = { recipients:[email...], title, msg, from, url? }
 *   宛先ユーザーの Teams 個人チャットへプロアクティブ通知される。
 *   （旧 Office365 Incoming Webhook は Microsoft 廃止により 403 で送信不可となったため移行）
 *
 *   共通エントリポイント: notify($mentionIds, $title, $message, $url = null)
 *   既存の notifyInterviewArrival 等はこの notify() を内部で呼ぶ薄いラッパー。
 *   受付システムの通知は全て「緊急」扱い（タイトル先頭に 🚨【緊急】 を付与）。
 */
class TeamsNotificationService
{
    /** 受付通知は全て緊急扱いとするためのタイトル接頭辞 */
    private const URGENT_PREFIX = '🚨【緊急】';

    /**
     * ★ 汎用通知送信メソッド（AkiTalk Bridge 経由）
     *
     * @param string|array $mentionIds 宛先 email（単一 or 配列）。空可。
     * @param string $title タイトル（先頭に 🚨【緊急】 を自動付与）
     * @param string $message 本文
     * @param string|null $url 任意。カード下部リンクとして付与
     * @return bool 送信成功時 true（宛先が空の場合も true）
     */
    public function notify(string|array $mentionIds, string $title, string $message, ?string $url = null): bool
    {
        // 宛先(email)を配列に正規化
        if (is_string($mentionIds)) {
            $mentionIds = $mentionIds === '' ? [] : [$mentionIds];
        }
        $recipients = array_values(array_filter(
            $mentionIds,
            fn($id) => is_string($id) && trim($id) !== ''
        ));

        if (empty($recipients)) {
            // 宛先なしはリトライ不要のため成功扱い（notify.py と同挙動）
            Log::info('Teams通知: 宛先が空のため送信をスキップ', ['title' => $title]);
            return true;
        }

        $bridgeUrl = config('services.akitalk_bridge.url');
        $apiKey = config('services.akitalk_bridge.api_key');
        if (!$bridgeUrl || !$apiKey) {
            Log::warning('Teams通知: AkiTalk Bridge が未設定（AKITALK_BRIDGE_URL / AKITALK_BRIDGE_API_KEY）');
            return false;
        }

        // 受付システムの通知は全て緊急扱い
        $urgentTitle = $this->withUrgentPrefix($title);

        $payload = [
            'recipients' => $recipients,
            'title' => $urgentTitle,
            'msg' => $message ?? '',
            'from' => config('services.akitalk_bridge.sender', 'AK受付システム通知'),
        ];
        if ($url) {
            $payload['url'] = $url;
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders(['x-api-key' => $apiKey])
                ->post($bridgeUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();
                if (($data['ok'] ?? false) === true) {
                    Log::info('Teams通知送信成功(AkiTalk Bridge)', [
                        'title' => $urgentTitle,
                        'recipients' => count($recipients),
                        'sent' => $data['sent'] ?? null,
                        'skipNoLicense' => $data['skipNoLicense'] ?? null,
                        'skipNoRef' => $data['skipNoRef'] ?? null,
                        'unknown' => $data['unknown'] ?? null,
                    ]);
                    return true;
                }
                Log::error('Teams通知: Bridge 応答が失敗', ['body' => $data]);
                return false;
            }

            Log::error('Teams通知: Bridge API エラー', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error('Teams通知例外(AkiTalk Bridge): ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return false;
        }
    }

    /**
     * タイトル先頭に緊急接頭辞を付与（二重付与を防止）。
     */
    private function withUrgentPrefix(string $title): string
    {
        $title = $title ?? '';
        return str_starts_with($title, self::URGENT_PREFIX)
            ? $title
            : self::URGENT_PREFIX . $title;
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
     * テスト通知送信（動作確認用）
     *
     * @param string|null $to 宛先 email（未指定なら宛先なし＝実際には送信されない）
     */
    public function sendTestNotification(?string $to = null): bool
    {
        return $this->notify(
            $to ? [$to] : [],
            '🧪 テスト通知',
            "受付システムからのテスト通知です。\n送信時刻: " . now()->format('Y年m月d日 H:i:s'),
        );
    }

    /**
     * notification_settings.trigger_event に紐づく teams type recipient の
     * メンション先 email を取得する。
     * （teams = Teams メンション先 / email = メール送信先 / phone = 電話発信先 と役割を分離）
     */
    private function getMentionIdsForTrigger(string $triggerEvent): \Illuminate\Support\Collection
    {
        $ids = collect();
        $settings = NotificationSetting::where('trigger_event', $triggerEvent)
            ->where('is_active', true)
            ->get();

        foreach ($settings as $setting) {
            $teamsRecipients = $setting->activeRecipients()
                ->where('notification_type', 'teams')
                ->get();
            foreach ($teamsRecipients as $recipient) {
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
