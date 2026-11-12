<?php

namespace App\Console\Commands;

use App\Services\LeaseExpiryNotificationService;
use Illuminate\Console\Command;

class SendLeaseExpiryNotifications extends Command
{
    protected $signature = 'leases:notify-expiring';

    protected $description = 'Queue configured reminders for leases approaching expiry';

    public function handle(LeaseExpiryNotificationService $service): int
    {
        $queued = $service->sendDueReminders();
        $this->info("Queued {$queued} lease expiry reminder(s).");

        return self::SUCCESS;
    }
}
