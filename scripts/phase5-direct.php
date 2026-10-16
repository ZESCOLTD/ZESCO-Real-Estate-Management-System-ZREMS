<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$u = App\Models\User::first();
$u->refresh();
echo "notify_email={$u->notify_email} notify_in_app={$u->notify_in_app} cats=" . json_encode($u->notify_categories) . "\n";
$r = new App\Models\AcquisitionRequest();
$r->id = 9999;
$r->reference_no = "ACQ-VIR-TEST";
try {
    $u->notify(new App\Notifications\VirementUploadedNotification($r, "Tester"));
    echo "Notified OK. Unread=" . $u->unreadNotifications()->count() . "\n";
    $last = $u->notifications()->latest()->first();
    echo "Latest title: " . ($last->data["title"] ?? "?") . "\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
