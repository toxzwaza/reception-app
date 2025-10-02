<?php

namespace App\Services;

use App\Models\Visitor;
use App\Models\StaffMember;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TeamsNotificationService
{
    /**
     * 担当者へのアポイント受付通知
     */
    public function notifyAppointmentCheckIn(Visitor $visitor): void
    {
        $staffMember = $visitor->staffMember;
        
        if (!$staffMember || !$staffMember->teams_webhook_url) {
            Log::warning('Teams Webhook URLが設定されていません', [
                'visitor_id' => $visitor->id,
                'staff_member_id' => $staffMember->id ?? null
            ]);
            return;
        }

        $message = [
            '@type' => 'MessageCard',
            '@context' => 'https://schema.org/extensions',
            'summary' => '来訪者受付通知',
            'themeColor' => '0078D4',
            'title' => '🔔 来訪者が受付されました',
            'sections' => [
                [
                    'activityTitle' => 'アポイントアリの来訪者',
                    'facts' => [
                        [
                            'name' => '会社名',
                            'value' => $visitor->company_name
                        ],
                        [
                            'name' => '氏名',
                            'value' => $visitor->visitor_name
                        ],
                        [
                            'name' => '受付時刻',
                            'value' => $visitor->check_in_time->format('Y年m月d日 H:i')
                        ],
                        [
                            'name' => '担当者',
                            'value' => $staffMember->name
                        ]
                    ]
                ]
            ]
        ];

        $this->sendToWebhook($staffMember->teams_webhook_url, $message);
    }

    /**
     * 面接担当者への通知
     */
    public function notifyInterviewArrival(): void
    {
        // TODO: 面接担当者のWebhook URLを取得
        $webhookUrl = config('services.teams.interview_webhook_url');
        
        if (!$webhookUrl) {
            Log::warning('面接担当者のWebhook URLが設定されていません');
            return;
        }

        $message = [
            '@type' => 'MessageCard',
            '@context' => 'https://schema.org/extensions',
            'summary' => '面接来訪者通知',
            'themeColor' => '28A745',
            'title' => '👔 面接の来訪者がいらっしゃいました',
            'sections' => [
                [
                    'activityTitle' => '面接受付',
                    'text' => '面接の来訪者が受付に来ています。ご対応をお願いいたします。',
                    'facts' => [
                        [
                            'name' => '受付時刻',
                            'value' => now()->format('Y年m月d日 H:i')
                        ]
                    ]
                ]
            ]
        ];

        $this->sendToWebhook($webhookUrl, $message);
    }

    /**
     * 部署への訪問者通知
     */
    public function notifyDepartmentVisitor(Visitor $visitor, int $groupId): void
    {
        $webhookUrl = $this->getGroupWebhookUrl($groupId);
        
        if (!$webhookUrl) {
            Log::warning('部署のWebhook URLが設定されていません', ['group_id' => $groupId]);
            return;
        }

        $message = [
            '@type' => 'MessageCard',
            '@context' => 'https://schema.org/extensions',
            'summary' => '来訪者通知',
            'themeColor' => '9C27B0',
            'title' => '🚶 来訪者がいらっしゃいました',
            'sections' => [
                [
                    'activityTitle' => 'その他の来訪者',
                    'facts' => [
                        [
                            'name' => '会社名',
                            'value' => $visitor->company_name
                        ],
                        [
                            'name' => '氏名',
                            'value' => $visitor->visitor_name
                        ],
                        [
                            'name' => '人数',
                            'value' => $visitor->number_of_people . '名'
                        ],
                        [
                            'name' => '要件',
                            'value' => $visitor->purpose
                        ],
                        [
                            'name' => '受付時刻',
                            'value' => $visitor->check_in_time->format('Y年m月d日 H:i')
                        ]
                    ]
                ]
            ],
            'potentialAction' => [
                [
                    '@type' => 'OpenUri',
                    'name' => '対応する',
                    'targets' => [
                        [
                            'os' => 'default',
                            'uri' => route('home') // TODO: 訪問者詳細ページへのリンク
                        ]
                    ]
                ]
            ]
        ];

        $this->sendToWebhook($webhookUrl, $message);
    }

    /**
     * Teams Webhookにメッセージを送信
     */
    private function sendToWebhook(string $webhookUrl, array $message): void
    {
        try {
            // 実際のHTTPリクエストを送信
            // GuzzleまたはLaravelのHttpファサードを使用
            
            // コメントアウトを解除して実際に送信
            /*
            $response = Http::post($webhookUrl, $message);
            
            if ($response->successful()) {
                Log::info('Teams通知を送信しました', [
                    'webhook_url' => $webhookUrl,
                ]);
            } else {
                Log::error('Teams通知の送信に失敗しました', [
                    'webhook_url' => $webhookUrl,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
            */

            // 開発中はログに出力
            Log::info('Teams通知を送信しました（ログのみ）', [
                'webhook_url' => $webhookUrl,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            Log::error('Teams通知送信中にエラーが発生しました', [
                'webhook_url' => $webhookUrl,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 部署のWebhook URLを取得
     */
    private function getGroupWebhookUrl(int $groupId): ?string
    {
        // TODO: データベースから取得
        // Group モデルを使用して管理
        
        // 今は設定ファイルから取得
        $groups = config('services.teams.groups', []);
        return $groups[$groupId] ?? config('services.teams.default_webhook_url');
    }
}



