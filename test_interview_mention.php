<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\NotificationSetting;
use App\Models\NotificationRecipient;
use App\Services\TeamsNotificationService;

echo "\n=== 面接メンション機能のテスト ===\n\n";

// 1. データベースからメンションIDを取得
echo "【ステップ1】データベースからメンションIDを取得\n";
echo str_repeat("-", 80) . "\n";

$notificationSettings = NotificationSetting::where('trigger_event', 'visitor_checkin')
    ->where('is_active', true)
    ->with('activeRecipients.user')
    ->get();

if ($notificationSettings->isEmpty()) {
    echo "❌ visitor_checkin トリガーの通知設定が見つかりません。\n";
    echo "先に register_interview_notification.php を実行してください。\n\n";
    exit(1);
}

$mentionIds = collect();

foreach ($notificationSettings as $setting) {
    echo "通知設定: {$setting->name} (ID: {$setting->id})\n";
    
    $emailRecipients = $setting->activeRecipients()
        ->where('notification_type', 'email')
        ->get();
    
    if ($emailRecipients->isEmpty()) {
        echo "  ⚠️ メール（メンション）タイプの受信者が登録されていません\n";
    } else {
        echo "  メンション対象者:\n";
        foreach ($emailRecipients as $recipient) {
            $userName = $recipient->user->name ?? '不明';
            echo "    - {$userName}: {$recipient->notification_data}\n";
            $mentionIds->push($recipient->notification_data);
        }
    }
}

echo "\n取得されたメンションID: " . $mentionIds->count() . "件\n";
if ($mentionIds->count() > 0) {
    echo "  " . $mentionIds->implode(', ') . "\n";
} else {
    echo "  ❌ メンションIDが見つかりません\n";
    exit(1);
}

echo "\n";

// 2. Adaptive Card形式のペイロードを生成
echo "【ステップ2】Adaptive Card形式のペイロードを生成\n";
echo str_repeat("-", 80) . "\n";

$mentionText = '';
$mentions = [];

foreach ($mentionIds as $index => $mentionId) {
    $mentionText .= "<at>{$mentionId}</at> ";
    $mentions[] = [
        "type" => "mention",
        "text" => "<at>{$mentionId}</at>",
        "mentioned" => [
            "id" => $mentionId,
            "name" => $mentionId
        ]
    ];
}

echo "メンションテキスト: {$mentionText}\n";
echo "メンションエンティティ数: " . count($mentions) . "件\n\n";

$body = [];

if ($mentionText) {
    $body[] = [
        "type" => "TextBlock",
        "text" => $mentionText,
        "color" => "attention",
        "size" => "large",
        "weight" => "bolder",
        "wrap" => true
    ];
}

$body[] = [
    "type" => "TextBlock",
    "text" => "👥 面接受付者到着（テスト）",
    "color" => "warning",
    "size" => "large",
    "weight" => "bolder",
    "wrap" => true
];

$body[] = [
    "type" => "TextBlock",
    "text" => "これはテスト通知です。面接受付者が受付に到着しました。\n\nチェックイン時刻: " . now()->format('Y年m月d日 H:i'),
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
                "version" => "1.4",
                "msteams" => [
                    "width" => "Full",
                    "entities" => $mentions
                ]
            ]
        ]
    ]
];

echo "生成されたペイロード:\n";
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// 3. Teamsへ送信
echo "【ステップ3】Teamsへテスト通知を送信\n";
echo str_repeat("-", 80) . "\n";

$webhookUrl = config('services.teams.webhook_url');

if (!$webhookUrl) {
    echo "❌ Teams Webhook URLが設定されていません。\n";
    echo ".env ファイルで TEAMS_WEBHOOK_URL を設定してください。\n\n";
    exit(1);
}

echo "Webhook URL: {$webhookUrl}\n";
echo "送信中...\n\n";

try {
    $response = \Illuminate\Support\Facades\Http::timeout(10)
        ->withOptions(['verify' => false])
        ->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ])
        ->post($webhookUrl, $payload);

    echo "HTTPステータスコード: {$response->status()}\n";
    echo "レスポンスボディ: {$response->body()}\n\n";

    if ($response->successful()) {
        echo "✅ テスト通知の送信に成功しました！\n";
        echo "\nTeamsチャンネルを確認してください。\n";
        echo "メンション付きでメッセージが表示されているはずです。\n\n";
    } else {
        echo "❌ テスト通知の送信に失敗しました。\n";
        echo "\n【トラブルシューティング】\n";
        echo "1. Webhook URLが正しいか確認してください\n";
        echo "2. Teamsチャンネルのコネクタ設定を確認してください\n";
        echo "3. メンションIDがTeamsのユーザーメールアドレスと一致しているか確認してください\n\n";
    }
} catch (\Exception $e) {
    echo "❌ エラーが発生しました: {$e->getMessage()}\n";
    echo "\nスタックトレース:\n";
    echo $e->getTraceAsString() . "\n\n";
}

echo "=== テスト完了 ===\n\n";

