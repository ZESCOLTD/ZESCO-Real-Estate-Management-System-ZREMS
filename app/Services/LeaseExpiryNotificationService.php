<?php

namespace App\Services;

use App\Models\LeaseAgreement;
use App\Models\LeaseExpiryNotificationLog;
use App\Notifications\LeaseExpiryNotification;
use Illuminate\Database\UniqueConstraintViolationException;

class LeaseExpiryNotificationService
{
    public function sendDueReminders(): int
    {
        $daysBeforeEnd = collect(config('lease_notifications.expiry_days', [90, 60, 30]))
            ->map(fn ($days) => (int) $days)
            ->filter(fn (int $days) => $days > 0)
            ->unique()
            ->values();
        $queued = 0;

        foreach ($daysBeforeEnd as $days) {
            $endDate = today()->addDays($days)->toDateString();

            LeaseAgreement::query()
                ->whereDate('end_date', $endDate)
                ->whereNotNull('responsible_officer_id')
                ->whereHas('leaseStatus', fn ($query) => $query->where('code', 'ongoing'))
                ->with('responsibleOfficer')
                ->orderBy('id')
                ->chunkById(100, function ($leases) use ($days, $endDate, &$queued): void {
                    foreach ($leases as $lease) {
                        $recipient = $lease->responsibleOfficer;
                        if (! $recipient) {
                            continue;
                        }

                        $notification = new LeaseExpiryNotification($lease, $days);
                        if ($notification->via($recipient) === []) {
                            continue;
                        }

                        try {
                            $log = LeaseExpiryNotificationLog::firstOrCreate(
                                [
                                    'lease_agreement_id' => $lease->id,
                                    'days_before_end' => $days,
                                    'lease_end_date' => $endDate,
                                ],
                                [
                                    'recipient_id' => $recipient->id,
                                    'queued_at' => now(),
                                ]
                            );
                        } catch (UniqueConstraintViolationException) {
                            continue;
                        }

                        if (! $log->wasRecentlyCreated) {
                            continue;
                        }

                        $recipient->notify($notification);
                        $queued++;
                    }
                });
        }

        return $queued;
    }
}
