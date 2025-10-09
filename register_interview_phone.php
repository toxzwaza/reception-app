<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\InterviewPhone;

echo "\n=== 面接用電話番号の登録 ===\n\n";

// ⚠️ 以下の情報を実際の値に変更してください
$phoneData = [
    'department_name' => '人事部',           // 部署名を入力
    'contact_person' => '採用担当者',        // 担当者名を入力
    'phone_number' => '090-1234-5678',      // ⚠️ 実際の電話番号に変更してください
    'extension_number' => '1001',           // 内線番号（任意）
    'notes' => '面接の担当者です',          // 備考（任意）
    'is_active' => true,                    // 有効フラグ
    'display_order' => 1,                   // 表示順
];

echo "以下の情報で登録します:\n";
echo "部署名: {$phoneData['department_name']}\n";
echo "担当者: {$phoneData['contact_person']}\n";
echo "電話番号: {$phoneData['phone_number']}\n";
echo "内線番号: {$phoneData['extension_number']}\n";
echo "備考: {$phoneData['notes']}\n";
echo "\n";

try {
    $phone = InterviewPhone::create($phoneData);
    echo "✅ 登録成功！\n\n";
    echo "登録された情報:\n";
    echo "ID: {$phone->id}\n";
    echo "部署名: {$phone->department_name}\n";
    echo "担当者: {$phone->contact_person}\n";
    echo "電話番号: {$phone->phone_number}\n";
    echo "\n";
    echo "✅ これで面接画面（/interview）から自動発信が可能になります。\n";
} catch (\Exception $e) {
    echo "❌ 登録失敗: {$e->getMessage()}\n";
}

echo "\n=== 完了 ===\n\n";

