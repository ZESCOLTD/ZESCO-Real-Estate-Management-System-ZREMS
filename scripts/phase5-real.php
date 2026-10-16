<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$u = App\Models\User::first();
$r = App\Models\AcquisitionRequest::first();
if (!$r) { echo "No acquisition requests found\n"; exit; }
echo "Using request id={$r->id} ref={$r->reference_no}\n";
$before = $u->unreadNotifications()->count();
app(App\Services\Notifications\LamsNotifier::class)->virementUploaded($u, $r, "Test Approver");
$after = $u->unreadNotifications()->count();
echo "Unread {$before} -> {$after}\n";
$last = $u->notifications()->latest()->first();
echo "Latest: " . ($last->data["title"] ?? "?") . " | " . ($last->data["message"] ?? "?") . "\n";
