<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$u = App\Models\User::first();
$before = $u->unreadNotifications()->count();

// Fabricate a real-ish AcquisitionRequest for the notification payload
$r = new App\Models\AcquisitionRequest();
$r->id = 9999;
$r->reference_no = 'ACQ-TEST-VIR-001';

app(App\Services\Notifications\LamsNotifier::class)
    ->virementUploaded($u, $r, 'Test Approver');

echo "Virement notification dispatched to {$u->email}\n";
echo "Unread before/after: {$before}/" . $u->unreadNotifications()->count() . "\n";

$last = $u->notifications()->latest()->first();
echo "Latest payload: " . json_encode($last->data, JSON_PRETTY_PRINT) . "\n";
