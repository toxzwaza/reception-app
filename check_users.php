<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$users = App\Models\User::select('id', 'name', 'email')->get();
echo "Users in database:\n";
foreach($users as $user) {
    echo $user->id . ': ' . $user->name . ' (' . $user->email . ')' . "\n";
}
?>
