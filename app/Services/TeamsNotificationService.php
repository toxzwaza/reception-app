<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Group;

class TeamsNotificationService
{
    private $webhookUrl;

    public function __construct()
    {
        $this->webhookUrl = config('services.teams.webhook_url');
    }

    /**
     * アポイント登録通知をTeamsに送信
     *
     * @param array $appointmentData
     * @return bool
     */
    public function sendAppointmentNotification(array $appointmentData): bool
    {
        if (!$this->webhookUrl) {
            Log::warning('Teams webhook URL not configured');
            return false;
        }

        $message = [
            "@type" => "MessageCard",
            "@context" => "https://schema.org/extensions",
            "themeColor" => "0078D4",
            "summary" => "新しいアポイントが登録されました",
            "sections" => [
                [
                    "activityTitle" => "📅 新しいアポイント登録",
                    "activitySubtitle" => "受付番号: {$appointmentData['reception_number']}",
                    "activityImage" => "https://img.icons8.com/color/48/000000/calendar.png",
                    "facts" => [
                        [
                            "name" => "会社名",
                            "value" => $appointmentData['company_name']
                        ],
                        [
                            "name" => "訪問者名",
                            "value" => $appointmentData['visitor_name']
                        ],
                        [
                            "name" => "担当者",
                            "value" => $appointmentData['staff_member_name'] ?? '未設定'
                        ],
                        [
                            "name" => "訪問予定日時",
                            "value" => $appointmentData['visit_date'] . ' ' . $appointmentData['visit_time']
                        ],
                        [
                            "name" => "訪問目的",
                            "value" => $appointmentData['purpose'] ?? 'なし'
                        ]
                    ],
                    "markdown" => true
                ]
            ],
            "potentialAction" => [
                [
                    "@type" => "OpenUri",
                    "name" => "管理画面で確認",
                    "targets" => [
                        [
                            "os" => "default",
                            "uri" => route('admin.appointments.index')
                        ]
                    ]
                ]
            ]
        ];

        return $this->sendMessage($message);
    }

    /**
     * チェックイン通知をTeamsに送信
     *
     * @param array $checkinData
     * @return bool
     */
    public function sendCheckinNotification(array $checkinData): bool
    {
        if (!$this->webhookUrl) {
            Log::warning('Teams webhook URL not configured');
            return false;
        }

        // 担当者のメンション情報を取得
        $mentionIds = [];
        if (!empty($checkinData['staff_member_email'])) {
            $mentionIds[] = $checkinData['staff_member_email'];
        }

        $staffMemberName = $checkinData['staff_member_name'] ?? '未設定';
        $appointmentInfoText = isset($checkinData['appointment_info']) ? "アポイント情報:\n" . $checkinData['appointment_info'] : "";
        
        $message = [
            "summary" => "来訪者がチェックインしました",
            "text" => "✅ 来訪者がチェックインしました\n\n受付番号: {$checkinData['reception_number']}\n会社名: {$checkinData['company_name']}\n訪問者名: {$checkinData['visitor_name']}\n担当者: {$staffMemberName}\nチェックイン時刻: " . now()->format('Y年m月d日 H:i') . "\n\n" . $appointmentInfoText,
            "mention_ids" => $mentionIds
        ];

        return $this->sendMessage($message);
    }

    /**
     * Teamsにメッセージを送信
     *
     * @param array $message
     * @return bool
     */
    private function sendMessage(array $message): bool
    {
        try {
            // メンション情報の処理
            $mentions = [];
            if (!empty($message['mention_ids'])) {
                $mentionIds = is_array($message['mention_ids']) ? $message['mention_ids'] : [$message['mention_ids']];
                
                foreach ($mentionIds as $id) {
                    $mentions[] = [
                        "type" => "mention",
                        "text" => "<at>{$id}</at>",
                        "mentioned" => [
                            "id" => $id,
                            "name" => $id
                        ]
                    ];
                }
            }

            // メンションテキストの生成
            $mentionText = '';
            if (!empty($message['mention_ids'])) {
                $mentionIds = is_array($message['mention_ids']) ? $message['mention_ids'] : [$message['mention_ids']];
                $mentionText = implode(' ', array_map(function($id) { return "@<at>{$id}</at>"; }, $mentionIds));
            }

            // Teams Adaptive Card形式のペイロード
            $body = [];
            
            // メンションがある場合は最初に追加
            if ($mentionText) {
                $body[] = [
                    "type" => "TextBlock",
                    "text" => $mentionText,
                    "color" => "attention",
                    "size" => "large",
                    "wrap" => true
                ];
            }

            // タイトル
            $body[] = [
                "type" => "TextBlock",
                "text" => $message['summary'] ?? 'Notification',
                "color" => $mentionText ? "default" : "attention",
                "size" => $mentionText ? "default" : "large",
                "wrap" => true
            ];

            // メッセージ本文
            $body[] = [
                "type" => "TextBlock",
                "text" => $message['text'] ?? $message['summary'] ?? 'Notification',
                "color" => "good",
                "size" => "medium",
                "wrap" => true
            ];

            $payload = [
                "type" => "message",
                "attachments" => [
                    [
                        "contentType" => "application/vnd.microsoft.card.adaptive",
                        "content" => [
                            "type" => "AdaptiveCard",
                            "body" => $body,
                            "\$schema" => "http://adaptivecards.io/schemas/adaptive-card.json",
                            "version" => "1.0",
                            "msteams" => [
                                "entities" => $mentions
                            ]
                        ]
                    ]
                ]
            ];

            $response = Http::timeout(10)
                ->withOptions(['verify' => false]) // SSL証明書検証を無効化（開発環境用）
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post($this->webhookUrl, $payload);

            Log::info('Teams notification request details', [
                'url' => $this->webhookUrl,
                'payload' => $payload,
                'status' => $response->status(),
                'response_body' => $response->body()
            ]);

            if ($response->successful()) {
                Log::info('Teams notification sent successfully', [
                    'message' => $message['summary'] ?? 'Notification',
                    'status' => $response->status()
                ]);
                return true;
            } else {
                Log::error('Failed to send Teams notification', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Teams notification error: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return false;
        }
    }

    /**
     * テスト通知を送信
     *
     * @return bool
     */
    public function sendTestNotification(): bool
    {
        if (!$this->webhookUrl) {
            Log::warning('Teams webhook URL not configured');
            return false;
        }

        // シンプルなテキストメッセージでテスト
        $message = [
            "text" => "🧪 システムテスト通知\n\nこれはテスト通知です。\n受付システムからTeams通知機能のテストを行っています。\n\n時刻: " . now()->format('Y年m月d日 H:i:s'),
            "summary" => "テスト通知",
            "themeColor" => "FF6B35"
        ];

        return $this->sendMessage($message);
    }

    /**
     * 訪問者チェックイン通知を送信（既存のメソッドとの互換性のため）
     *
     * @param \App\Models\Visitor $visitor
     * @return bool
     */
    public function notifyAppointmentCheckIn($visitor): bool
    {
        // アポイント情報を取得
        $appointment = \App\Models\Appointment::where('reception_number', $visitor->reception_number)->first();
        
        $appointmentInfo = '';
        if ($appointment) {
            $appointmentInfo = "訪問予定日: " . $appointment->visit_date->format('Y年m月d日') . "\n" .
                              "訪問予定時刻: " . $appointment->visit_time->format('H:i') . "\n" .
                              "訪問目的: " . ($appointment->purpose ?? 'なし');
        }

        $checkinData = [
            'reception_number' => $visitor->reception_number,
            'company_name' => $visitor->company_name,
            'visitor_name' => $visitor->visitor_name,
            'staff_member_name' => $visitor->staffMember->name ?? '未設定',
            'staff_member_email' => $visitor->staffMember->email ?? null,
            'appointment_info' => $appointmentInfo,
        ];

        return $this->sendCheckinNotification($checkinData);
    }

    /**
     * 面接受付通知を送信
     *
     * @return bool
     */
    public function notifyInterviewArrival(): bool
    {
        if (!$this->webhookUrl) {
            Log::warning('Teams webhook URL not configured');
            return false;
        }

        $message = [
            "@type" => "MessageCard",
            "@context" => "https://schema.org/extensions",
            "themeColor" => "FFC107",
            "summary" => "面接受付者が到着しました",
            "sections" => [
                [
                    "activityTitle" => "👥 面接受付者到着",
                    "activitySubtitle" => "面接担当者への連絡が必要です",
                    "activityImage" => "https://img.icons8.com/color/48/000000/meeting.png",
                    "text" => "面接受付者が受付に到着しました。担当者に連絡をお願いします。",
                    "markdown" => true
                ]
            ]
        ];

        return $this->sendMessage($message);
    }


    /**
     * 部署選択後の訪問者通知（メンション付き）
     *
     * @param array $visitorData
     * @param int $groupId
     * @return bool
     */
    public function notifyDepartmentVisitor(array $visitorData, int $groupId): bool
    {
        if (!$this->webhookUrl) {
            Log::warning('Teams webhook URL not configured');
            return false;
        }

        // 部署情報を取得
        $group = Group::find($groupId);
        $departmentName = $group ? $group->name : '不明な部署';

        // 部署のユーザー一覧を取得してメンション用のユーザー情報を作成
        $users = User::where('group_id', $groupId)
            ->where('del_flg', 0)
            ->get();

        $mentions = [];
        foreach ($users as $user) {
            if ($user->email) {
                $mentions[] = [
                    "type" => "mention",
                    "text" => "<at>{$user->name}</at>",
                    "mentioned" => [
                        "id" => $user->email,
                        "name" => $user->name
                    ]
                ];
            }
        }

        $message = [
            "type" => "message",
            "attachments" => [
                [
                    "contentType" => "application/vnd.microsoft.card.adaptive",
                    "content" => [
                        "type" => "AdaptiveCard",
                        "body" => [
                            [
                                "type" => "TextBlock",
                                "text" => "新しい訪問者が到着しました",
                                "color" => "attention",
                                "size" => "large",
                                "wrap" => true
                            ],
                            [
                                "type" => "TextBlock",
                                "text" => "新しい訪問者が到着しました

" . (isset($visitorData['reception_number']) && $visitorData['reception_number'] ? "受付番号: {$visitorData['reception_number']}\n" : "") . "会社名: {$visitorData['company_name']}
訪問者: {$visitorData['visitor_name']}
人数: {$visitorData['number_of_people']}名
訪問目的: {$visitorData['purpose']}
チェックイン時刻: " . now()->format('Y年m月d日 H:i') . "

訪問先部署: {$departmentName}",
                                "color" => "good",
                                "size" => "medium",
                                "wrap" => true
                            ]
                        ],
                        "\$schema" => "http://adaptivecards.io/schemas/adaptive-card.json",
                        "version" => "1.0",
                        "msteams" => [
                            "entities" => $mentions
                        ]
                    ]
                ]
            ]
        ];

        return $this->sendMessage($message);
    }
}