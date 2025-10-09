<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\InterviewPhone;

echo "\n=== 面接用電話番号の確認 ===\n\n";

// 全ての面接用電話番号を取得（削除済みも含む）
$allPhones = InterviewPhone::withTrashed()->get();

echo "登録されている面接用電話番号の総数: " . $allPhones->count() . "\n\n";

if ($allPhones->isEmpty()) {
    echo "❌ 面接用電話番号が1件も登録されていません。\n";
    echo "\n【解決方法】\n";
    echo "1. 管理画面（/interview-phones）から面接用電話番号を登録してください。\n";
    echo "   または\n";
    echo "2. 以下のコマンドで直接登録できます：\n\n";
    echo "   php artisan tinker\n";
    echo "   InterviewPhone::create([\n";
    echo "       'department_name' => '人事部',\n";
    echo "       'contact_person' => '田中太郎',\n";
    echo "       'phone_number' => '090-1234-5678',\n";
    echo "       'extension_number' => '1234',\n";
    echo "       'is_active' => true,\n";
    echo "       'display_order' => 1\n";
    echo "   ]);\n\n";
} else {
    echo "登録されている面接用電話番号:\n";
    echo str_repeat("-", 80) . "\n";
    
    foreach ($allPhones as $phone) {
        echo "ID: {$phone->id}\n";
        echo "部署名: {$phone->department_name}\n";
        echo "担当者: {$phone->contact_person}\n";
        echo "電話番号: {$phone->phone_number}\n";
        echo "内線番号: " . ($phone->extension_number ?? '未設定') . "\n";
        echo "有効状態: " . ($phone->is_active ? '✅ 有効' : '❌ 無効') . "\n";
        echo "表示順: {$phone->display_order}\n";
        echo "削除状態: " . ($phone->deleted_at ? '🗑️ 削除済み (' . $phone->deleted_at . ')' : '📌 有効') . "\n";
        echo str_repeat("-", 80) . "\n";
    }
    
    echo "\n";
    
    // 有効な電話番号のみを取得
    $activePhones = InterviewPhone::active()->ordered()->get();
    echo "現在有効な面接用電話番号の数: " . $activePhones->count() . "\n\n";
    
    if ($activePhones->isEmpty()) {
        echo "⚠️ 登録されていますが、有効な電話番号がありません。\n";
        echo "\n【原因】\n";
        echo "- is_active が false になっている\n";
        echo "- または削除済み（soft delete）\n";
        echo "\n【解決方法】\n";
        echo "管理画面から電話番号を「有効」に設定してください。\n\n";
    } else {
        echo "✅ 有効な面接用電話番号が正しく登録されています。\n\n";
    }
}

echo "\n=== 確認完了 ===\n\n";

