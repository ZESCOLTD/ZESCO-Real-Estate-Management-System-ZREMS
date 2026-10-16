<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$u = App\Models\User::first();
if (!$u) { echo "No users found\n"; exit; }

$u->notify(new App\Notifications\ScopingCompletedNotification('TEST-REF-001', 1, 3, 3));

echo "Sent to: {$u->email}\n";
echo "Total notifications: " . $u->notifications()->count() . "\n";
echo "Unread: " . $u->unreadNotifications()->count() . "\n";

$last = $u->notifications()->latest()->first();
if ($last) {
    echo "Latest: " . json_encode($last->data, JSON_PRETTY_PRINT) . "\n";
}
