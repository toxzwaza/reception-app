<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // テスト管理者ユーザー
        User::create([
            'emp_no' => '000001',
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'gender_flg' => 0,
            'group_id' => 1,
            'position_id' => 1,
            'process_id' => 1,
            'is_admin' => 1,
            'dispatch_flg' => 0,
            'part_flg' => 0,
            'always_order_flg' => 0,
            'duty_flg' => 0,
            'del_flg' => 0,
        ]);

        // テスト一般ユーザー
        User::create([
            'emp_no' => '000002',
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
            'gender_flg' => 0,
            'group_id' => 2,
            'position_id' => 2,
            'process_id' => 2,
            'is_admin' => 0,
            'dispatch_flg' => 0,
            'part_flg' => 0,
            'always_order_flg' => 0,
            'duty_flg' => 0,
            'del_flg' => 0,
        ]);

        // テスト一般ユーザー（女性）
        User::create([
            'emp_no' => '000003',
            'name' => '佐藤花子',
            'email' => 'sato@example.com',
            'password' => Hash::make('password'),
            'gender_flg' => 1,
            'group_id' => 2,
            'position_id' => 3,
            'process_id' => 2,
            'is_admin' => 0,
            'dispatch_flg' => 0,
            'part_flg' => 0,
            'always_order_flg' => 0,
            'duty_flg' => 0,
            'del_flg' => 0,
        ]);
    }
}
