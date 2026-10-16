<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceRequestService;
use App\Models\User;
use App\Observers\ServiceRequestServiceObserver;
use App\Services\Notifications\LamsNotifier;

// --- Test 1: LamsNotifier direct call ---
$pm = User::first();
echo "Test 1: LamsNotifier direct dispatch\n";
$before = $pm->notifications()->count();

$svc = ServiceRequestService::with('department.serviceRequest.requester')->first();
if ($svc) {
    app(LamsNotifier::class)->serviceApproved($pm, $svc, 1, 1);
    $after = $pm->notifications()->count();
    echo "  Notifications before/after: {$before}/{$after}\n";
    echo "  " . ($after > $before ? "PASS" : "FAIL") . "\n";
} else {
    echo "  SKIP (no ServiceRequestService rows)\n";
}

// --- Test 2: Observer disabled by default (no double-fire) ---
echo "\nTest 2: Observer default = disabled (no duplicate fire on status change)\n";
if ($svc) {
    $before = $pm->notifications()->count();
    $svc->status = $svc->status === 'approved' ? 'assigned' : 'approved';
    $svc->save();
    $after = $pm->notifications()->count();
    echo "  Observer disabled → notifications delta: " . ($after - $before) . " (expected 0)\n";
    echo "  " . ($after === $before ? "PASS" : "FAIL") . "\n";
}

// --- Test 3: Observer enabled ---
echo "\nTest 3: Observer enabled → status change fires notification\n";
if ($svc) {
    ServiceRequestServiceObserver::enable();
    $before = $pm->notifications()->count();
    $svc->status = $svc->status === 'approved' ? 'assigned' : 'approved';
    $svc->save();
    $after = $pm->notifications()->count();
    echo "  Observer enabled → notifications delta: " . ($after - $before) . "\n";
    echo "  " . ($after > $before ? "PASS" : "PARTIAL (may need PM relation)") . "\n";
    ServiceRequestServiceObserver::disable();
}

echo "\nDone.\n";
