<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\NotificationSetting;
use App\Models\NotificationRecipient;

echo "\n=== 面接用通知設定の確認 ===\n\n";

// visitor_checkin トリガーの通知設定を取得
$notificationSettings = NotificationSetting::where('trigger_event', 'visitor_checkin')
    ->with('activeRecipients.user')
    ->get();

if ($notificationSettings->isEmpty()) {
    echo "❌ visitor_checkin トリガーの通知設定が見つかりません。\n\n";
    echo "【解決方法】\n";
    echo "以下のスクリプトを実行して通知設定を登録してください:\n\n";
    echo "1. register_interview_notification.php を編集して、実際の電話番号とメンションIDを設定\n";
    echo "2. php register_interview_notification.php を実行\n\n";
    exit(0);
}

echo "登録されている通知設定の総数: " . $notificationSettings->count() . "\n\n";

foreach ($notificationSettings as $index => $setting) {
    echo "【通知設定 #" . ($index + 1) . "】\n";
    echo str_repeat("-", 80) . "\n";
    echo "ID: {$setting->id}\n";
    echo "名前: {$setting->name}\n";
    echo "説明: " . ($setting->description ?? '未設定') . "\n";
    echo "トリガーイベント: {$setting->trigger_event} ({$setting->trigger_event_name})\n";
    echo "有効状態: " . ($setting->is_active ? '✅ 有効' : '❌ 無効') . "\n";
    echo "作成日時: {$setting->created_at}\n";
    echo "\n";
    
    // 受信者情報
    $activeRecipients = $setting->activeRecipients;
    
    if ($activeRecipients->isEmpty()) {
        echo "⚠️ 有効な受信者が登録されていません。\n";
    } else {
        echo "受信者一覧 ({$activeRecipients->count()}件):\n";
        
        // 電話通知
        $phoneRecipients = $activeRecipients->where('notification_type', 'phone');
        if ($phoneRecipients->count() > 0) {
            echo "\n  📞 電話通知:\n";
            foreach ($phoneRecipients as $recipient) {
                $userName = $recipient->user->name ?? '不明';
                echo "    - {$userName}: {$recipient->notification_data}\n";
            }
        } else {
            echo "\n  ⚠️ 電話通知の受信者が登録されていません\n";
        }
        
        // Teams通知（メンション）
        $emailRecipients = $activeRecipients->where('notification_type', 'email');
        if ($emailRecipients->count() > 0) {
            echo "\n  💬 Teams通知（メンション）:\n";
            foreach ($emailRecipients as $recipient) {
                $userName = $recipient->user->name ?? '不明';
                echo "    - {$userName}: {$recipient->notification_data}\n";
            }
        } else {
            echo "\n  ⚠️ Teams通知の受信者が登録されていません\n";
        }
        
        // その他の通知タイプ
        $otherRecipients = $activeRecipients->whereNotIn('notification_type', ['phone', 'email']);
        if ($otherRecipients->count() > 0) {
            echo "\n  📢 その他の通知:\n";
            foreach ($otherRecipients as $recipient) {
                $userName = $recipient->user->name ?? '不明';
                echo "    - {$userName} ({$recipient->notification_type_name}): {$recipient->notification_data}\n";
            }
        }
    }
    
    echo "\n" . str_repeat("-", 80) . "\n\n";
}

// 動作確認情報
echo "【動作確認】\n";
echo "面接画面（/interview）にアクセスすると:\n\n";

$hasPhone = false;
$hasEmail = false;

foreach ($notificationSettings as $setting) {
    if (!$setting->is_active) continue;
    
    $phoneCount = $setting->activeRecipients()->where('notification_type', 'phone')->count();
    $emailCount = $setting->activeRecipients()->where('notification_type', 'email')->count();
    
    if ($phoneCount > 0) {
        $hasPhone = true;
        echo "✅ Twilioから {$phoneCount} 件の電話番号へ自動発信されます\n";
    }
    
    if ($emailCount > 0) {
        $hasEmail = true;
        echo "✅ Teamsへメンション付き通知が {$emailCount} 名に送信されます\n";
    }
}

if (!$hasPhone) {
    echo "⚠️ 電話通知が設定されていません\n";
}

if (!$hasEmail) {
    echo "⚠️ Teams通知が設定されていません\n";
}

echo "\n=== 確認完了 ===\n\n";

