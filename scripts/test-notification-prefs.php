<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$u = App\Models\User::first();
echo "User: {$u->email}\n";
echo "notify_email: " . var_export($u->notify_email, true) . "\n";
echo "notify_in_app: " . var_export($u->notify_in_app, true) . "\n";
echo "categories: " . json_encode($u->notify_categories) . "\n\n";

// Test 1: default (all on) - should return both channels
$n = new App\Notifications\ScopingCompletedNotification('PREF-TEST', 1, 1, 1);
echo "Default via(): " . json_encode($n->via($u)) . "\n";

// Test 2: mute email
$u->forceFill(['notify_email' => false])->save();
$u->refresh();
echo "After notify_email=false via(): " . json_encode($n->via($u)) . "\n";

// Test 3: mute scoping category
$u->forceFill(['notify_email' => true, 'notify_categories' => ['scoping' => false]])->save();
$u->refresh();
echo "After scoping category muted via(): " . json_encode($n->via($u)) . "\n";

// restore
$u->forceFill(['notify_email' => true, 'notify_in_app' => true, 'notify_categories' => null])->save();
echo "\nRestored defaults.\n";
