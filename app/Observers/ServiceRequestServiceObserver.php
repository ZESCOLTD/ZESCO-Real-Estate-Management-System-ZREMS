<?php

namespace App\Observers;

use App\Models\ServiceRequestService;
use App\Models\User;
use App\Services\Notifications\LamsNotifier;

/**
 * Observes status transitions on ServiceRequestService and fires the
 * corresponding notification via LamsNotifier.
 *
 * SAFETY / DUPLICATION:
 *  - Existing Livewire actions in ServiceRequests\Show and ServiceForms\Preview
 *    already dispatch these notifications inline.
 *  - To prevent duplicate emails during the migration to the observer-only
 *    model, the observer only fires when a request-scoped flag is set:
 *
 *       ServiceRequestServiceObserver::enable();  // opt-in per request
 *
 *    Call sites can opt-in as they are refactored away from inline notify().
 *    In tests you can toggle this too.
 */
class ServiceRequestServiceObserver
{
    protected static bool $enabled = false;

    public static function enable(): void  { self::$enabled = true; }
    public static function disable(): void { self::$enabled = false; }
    public static function isEnabled(): bool { return self::$enabled; }

    public function updated(ServiceRequestService $service): void
    {
        if (!self::$enabled) {
            return;
        }
        if (!$service->wasChanged('status')) {
            return;
        }

        $notifier = app(LamsNotifier::class);
        $status = (string) $service->status;

        // Reload relations for clean payloads.
        $service->loadMissing(['department.serviceRequest.requester', 'assignedTo']);
        $dept = $service->department;
        $req  = $dept?->serviceRequest;
        $pm   = $req?->requester;

        switch ($status) {
            case 'assigned':
                $officerId = (int) $service->assigned_to_user_id;
                if ($officerId > 0) {
                    $officer = User::find($officerId);
                    if ($officer) {
                        $notifier->scopingTaskAssigned($officer, $service);
                    }
                }
                break;

            case 'approved':
                if ($pm) {
                    $siblingApproved = (int) ($dept?->services()->where('status', 'approved')->count() ?? 0);
                    $siblingTotal    = (int) ($dept?->services()->count() ?? 0);
                    $notifier->serviceApproved($pm, $service, $siblingApproved, $siblingTotal);
                }
                break;

            case 'rejected':
                // We can't know the reason inside the observer; the caller
                // should still send the reason-bearing notification explicitly.
                // No-op here to avoid a reason-less "rejected" email.
                break;
        }
    }
}
