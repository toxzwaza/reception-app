<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\NotificationSetting;
use App\Models\NotificationRecipient;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "\n=== 面接用通知設定の登録 ===\n\n";

// ⚠️ 以下の情報を実際の値に変更してください
$phoneNumber = '09061827735';           // ⚠️ 実際の電話番号
$mentionId = 'to-murakami@akioka-ltd.jp';          // ⚠️ TeamsのメンションID（メールアドレス形式）
$userId = 91;                              // ⚠️ 実際のユーザーID

echo "【設定内容】\n";
echo "電話番号: {$phoneNumber}\n";
echo "TeamsメンションID: {$mentionId}\n";
echo "ユーザーID: {$userId}\n";
echo "\n";

try {
    // ユーザーの存在確認
    $user = User::find($userId);
    if (!$user) {
        echo "❌ エラー: ユーザーID {$userId} が見つかりません。\n";
        echo "利用可能なユーザーを確認してください。\n\n";
        exit(1);
    }

    echo "✅ ユーザー確認: {$user->name} (ID: {$user->id})\n\n";

    // トランザクション開始
    DB::beginTransaction();

    // 1. NotificationSettingを作成または取得
    $notificationSetting = NotificationSetting::firstOrCreate(
        [
            'trigger_event' => 'visitor_checkin',
            'name' => '面接受付通知'
        ],
        [
            'description' => '面接受付時に担当者へ電話とTeams通知を送信',
            'is_active' => true,
            'settings' => [
                'auto_call' => true,
                'call_delay' => 3000,
            ]
        ]
    );

    echo "✅ 通知設定を作成/取得しました (ID: {$notificationSetting->id})\n";
    echo "   名前: {$notificationSetting->name}\n";
    echo "   トリガー: {$notificationSetting->trigger_event}\n\n";

    // 2. 電話通知の受信者を登録
    $phoneRecipient = NotificationRecipient::updateOrCreate(
        [
            'notification_setting_id' => $notificationSetting->id,
            'user_id' => $userId,
            'notification_type' => 'phone',
        ],
        [
            'notification_data' => $phoneNumber,
            'is_active' => true,
        ]
    );

    echo "✅ 電話通知受信者を登録しました (ID: {$phoneRecipient->id})\n";
    echo "   通知タイプ: phone\n";
    echo "   電話番号: {$phoneRecipient->notification_data}\n";
    echo "   担当者: {$user->name}\n\n";

    // 3. Teams通知（メンション）の受信者を登録
    $teamsRecipient = NotificationRecipient::updateOrCreate(
        [
            'notification_setting_id' => $notificationSetting->id,
            'user_id' => $userId,
            'notification_type' => 'email',  // emailタイプでメンションIDを保存
        ],
        [
            'notification_data' => $mentionId,
            'is_active' => true,
        ]
    );

    echo "✅ Teams通知受信者を登録しました (ID: {$teamsRecipient->id})\n";
    echo "   通知タイプ: email (メンションID)\n";
    echo "   メンションID: {$teamsRecipient->notification_data}\n";
    echo "   担当者: {$user->name}\n\n";

    DB::commit();

    echo "=== 登録完了 ===\n\n";
    echo "✅ 面接画面（/interview）にアクセスすると、以下の動作が実行されます:\n";
    echo "   1. Twilioから {$phoneNumber} へ自動発信\n";
    echo "   2. Teamsへメンション付き通知（@{$mentionId}）\n\n";

    // 登録内容の確認
    echo "【登録されている通知設定の確認】\n";
    echo str_repeat("-", 80) . "\n";
    
    $allSettings = NotificationSetting::where('trigger_event', 'visitor_checkin')
        ->where('is_active', true)
        ->with('activeRecipients.user')
        ->get();

    foreach ($allSettings as $setting) {
        echo "通知設定: {$setting->name}\n";
        echo "トリガー: {$setting->trigger_event_name}\n";
        echo "受信者:\n";
        
        foreach ($setting->activeRecipients as $recipient) {
            $userName = $recipient->user->name ?? '不明';
            echo "  - {$userName} ({$recipient->notification_type_name}): {$recipient->notification_data}\n";
        }
        
        echo "\n";
    }

} catch (\Exception $e) {
    DB::rollBack();
    echo "❌ 登録失敗: {$e->getMessage()}\n";
    echo "スタックトレース:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n";

