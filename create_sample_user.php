<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// サンプルユーザーを作成
$user = App\Models\User::create([
    'emp_no' => '000091',
    'name' => '村上飛羽',
    'email' => 'to-murakami@akioka-ltd.jp',
    'password' => bcrypt('password'),
    'group_id' => null,
    'position_id' => null,
    'process_id' => null,
    'is_admin' => 0,
    'dispatch_flg' => 0,
    'part_flg' => 0,
    'always_order_flg' => 0,
    'duty_flg' => 0,
    'del_flg' => 0,
]);

echo "User created: {$user->name} (ID: {$user->id})\n";
?>
