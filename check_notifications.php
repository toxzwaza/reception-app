<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Notification Settings ===\n";
$settings = App\Models\NotificationSetting::where('trigger_event', 'delivery_received')->with('activeRecipients')->get();
foreach($settings as $setting) {
    echo "Setting: {$setting->name} (ID: {$setting->id})\n";
    echo "Recipients: " . $setting->activeRecipients->count() . "\n";
    foreach($setting->activeRecipients as $recipient) {
        echo "  - {$recipient->notification_type}: {$recipient->notification_data} (user_id: {$recipient->user_id})\n";
    }
    echo "\n";
}

echo "=== All Notification Settings ===\n";
$allSettings = App\Models\NotificationSetting::with('activeRecipients')->get();
foreach($allSettings as $setting) {
    echo "Setting: {$setting->name} (ID: {$setting->id}) - Trigger: {$setting->trigger_event}\n";
    echo "Recipients: " . $setting->activeRecipients->count() . "\n";
    foreach($setting->activeRecipients as $recipient) {
        echo "  - {$recipient->notification_type}: {$recipient->notification_data} (user_id: {$recipient->user_id})\n";
    }
    echo "\n";
}
?>
