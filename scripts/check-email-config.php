<?php
require __DIR__ . '/../vendor/autoload.php';
$a = require __DIR__ . '/../bootstrap/app.php';
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$total = App\Models\User::count();
$off   = App\Models\User::where('notify_email', 0)->count();
$null  = App\Models\User::whereNull('notify_email')->count();
$on    = App\Models\User::where('notify_email', 1)->count();
$noEmail = App\Models\User::whereNull('email')->orWhere('email', '')->count();

echo "USERS  total=$total  on=$on  off=$off  null(defaults on)=$null  missing_email=$noEmail\n";
echo 'MAIL   default=' . config('mail.default')
   . '  host=' . config('mail.mailers.smtp.host')
   . ':' . config('mail.mailers.smtp.port')
   . '  enc=' . (config('mail.mailers.smtp.encryption') ?: 'null')
   . '  from=' . config('mail.from.address') . PHP_EOL;
echo 'QUEUE  ' . config('queue.default') . PHP_EOL;
