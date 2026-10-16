<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Send a branded notification email to Benson so we can visually confirm Phase 6.
$to = 'bensonngulube@zesco.co.zm';

$user = App\Models\User::where('email', $to)->orWhere('email', strtoupper($to))->first();
if (!$user) {
    echo "User with email {$to} not found. Falling back to first user.\n";
    $user = App\Models\User::first();
}

$req = App\Models\AcquisitionRequest::first();
if (!$req) { echo "No acquisition requests found\n"; exit; }

app(App\Services\Notifications\LamsNotifier::class)
    ->virementUploaded($user, $req, 'Phase 6 Branded Template Test');

echo "Branded email dispatched to {$user->email} for request {$req->reference_no}\n";
echo "From name: " . config('mail.from.name') . " <" . config('mail.from.address') . ">\n";
