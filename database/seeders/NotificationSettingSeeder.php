<?php

namespace Database\Seeders;

use App\Models\NotificationSetting;
use App\Models\NotificationRecipient;
use App\Models\StaffMember;
use Illuminate\Database\Seeder;

class NotificationSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // ①面接の方の通知
        $interviewNotification = NotificationSetting::create([
            'name' => '面接来訪者通知',
            'description' => '面接来訪者受付時の通知',
            'trigger_event' => 'visitor_checkin',
            'is_active' => true,
        ]);

        NotificationRecipient::create([
            'notification_setting_id' => $interviewNotification->id,
            'user_id' => 91, // ユーザーID 91を使用
            'notification_type' => 'phone',
            'notification_data' => '09061827735',
            'is_active' => true,
        ]);

        // ②納品・集荷の方の通知
        $deliveryNotification = NotificationSetting::create([
            'name' => '納品・集荷通知',
            'description' => '配送業者受付時の通知',
            'trigger_event' => 'delivery_received',
            'is_active' => true,
        ]);

        NotificationRecipient::create([
            'notification_setting_id' => $deliveryNotification->id,
            'user_id' => 91, // ユーザーID 91を使用
            'notification_type' => 'teams',
            'notification_data' => 'to-murakami@akioka-ltd.jp',
            'is_active' => true,
        ]);

        // 集荷伝票用の通知設定も追加
        $pickupNotification = NotificationSetting::create([
            'name' => '集荷伝票通知',
            'description' => '集荷伝票受付時の通知',
            'trigger_event' => 'pickup_received',
            'is_active' => true,
        ]);

        NotificationRecipient::create([
            'notification_setting_id' => $pickupNotification->id,
            'user_id' => 91, // ユーザーID 91を使用
            'notification_type' => 'teams',
            'notification_data' => 'to-murakami@akioka-ltd.jp',
            'is_active' => true,
        ]);
    }
}