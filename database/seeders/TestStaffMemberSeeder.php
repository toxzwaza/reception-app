<?php

namespace Database\Seeders;

use App\Models\StaffMember;
use Illuminate\Database\Seeder;

class TestStaffMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        StaffMember::create([
            'name' => '田中一郎',
            'email' => 'tanaka@example.com',
            'department' => '営業部',
        ]);

        StaffMember::create([
            'name' => '鈴木次郎',
            'email' => 'suzuki@example.com',
            'department' => '開発部',
        ]);

        StaffMember::create([
            'name' => '高橋三郎',
            'email' => 'takahashi@example.com',
            'department' => '総務部',
        ]);

        StaffMember::create([
            'name' => '渡辺美咲',
            'email' => 'watanabe@example.com',
            'department' => '人事部',
        ]);

        StaffMember::create([
            'name' => '伊藤健太',
            'email' => 'ito@example.com',
            'department' => '企画部',
        ]);
    }
}
