<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// アポイント通知をテスト
App\Services\NotificationService::sendAppointmentNotification(
    'visitor_checkin',
    [
        'type' => 'visitor_checkin',
        'reception_number' => '1234',
        'company_name' => 'Test Company',
        'visitor_name' => 'Test Visitor',
        'staff_member_name' => 'Test Staff',
        'check_in_time' => now()->format('Y-m-d H:i'),
        'appointment_info' => 'Test appointment'
    ],
    [
        'mention_id' => 'to-murakami@akioka-ltd.jp',
        'email' => 'to-murakami@akioka-ltd.jp'
    ]
);

echo "Appointment notification sent successfully\n";
?>
