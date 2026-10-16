<?php

namespace App\Services\Notifications;

use App\Models\AcquisitionDueDiligence;
use App\Models\AcquisitionRequest;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestService;
use App\Models\User;
use App\Notifications\FormSubmittedNotification;
use App\Notifications\NextApproverNotification;
use App\Notifications\NextDueDiligenceApproverNotification;
use App\Notifications\ScopingCompletedNotification;
use App\Notifications\ScopingServicesSelectedNotification;
use App\Notifications\ScopingTaskAssignedNotification;
use App\Notifications\ServiceApprovedNotification;
use App\Notifications\ServiceRejectedNotification;
use App\Notifications\ServiceRevertedNotification;
use App\Notifications\VirementUploadedNotification;
use App\Notifications\GisSurveyCompletedNotification;
use App\Notifications\DisputeRaisedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Single entry point for LAMS notification dispatch.
 *
 * Wraps every Notification class with a small, intent-revealing method so
 * business code stays free of notification-class construction details.
 * All methods are safe to call with partial data — they log and return
 * on error rather than throwing.
 *
 * Usage:
 *   app(LamsNotifier::class)->scopingTaskAssigned($officer, $service);
 */
class LamsNotifier
{
    // ─────────────────────────────────────────────────────────────────────────
    // Scoping
    // ─────────────────────────────────────────────────────────────────────────

    public function scopingTaskAssigned(User $officer, ServiceRequestService $service): void
    {
        $this->safeSend(function () use ($officer, $service) {
            $dept = $service->department;
            $req  = $dept?->serviceRequest;

            $officer->notify(new ScopingTaskAssignedNotification(
                (int) ($dept?->service_request_id ?? 0),
                (int) $service->id,
                (int) $service->service_child_id,
                (string) $service->service_name,
                (string) ($dept?->department_code ?? ''),
                $req?->context_type,
                $req?->context_id,
                $this->contextReference($req),
            ));
        }, 'scopingTaskAssigned', $officer);
    }

    public function scopingServicesSelected(User $recipient, int $serviceRequestId, string $departmentCode, ?string $contextReference = null): void
    {
        $this->safeSend(function () use ($recipient, $serviceRequestId, $departmentCode, $contextReference) {
            $recipient->notify(new ScopingServicesSelectedNotification(
                $serviceRequestId,
                $departmentCode,
                $contextReference
            ));
        }, 'scopingServicesSelected', $recipient);
    }

    public function formSubmitted(User $recipient, ServiceRequestService $service, string $officerName): void
    {
        $this->safeSend(function () use ($recipient, $service, $officerName) {
            $dept = $service->department;
            $req  = $dept?->serviceRequest;

            $recipient->notify(new FormSubmittedNotification(
                (string) $service->service_name,
                $officerName,
                (string) ($dept?->department_code ?? ''),
                $this->contextReference($req) ?? 'N/A',
                (int) ($req?->id ?? 0),
            ));
        }, 'formSubmitted', $recipient);
    }

    public function serviceApproved(User $pm, ServiceRequestService $service, int $approvedCount = 0, int $totalCount = 0): void
    {
        $this->safeSend(function () use ($pm, $service, $approvedCount, $totalCount) {
            $dept = $service->department;
            $req  = $dept?->serviceRequest;

            $pm->notify(new ServiceApprovedNotification(
                (string) $service->service_name,
                (string) ($dept?->department_code ?? ''),
                $this->contextReference($req) ?? 'N/A',
                (int) ($req?->id ?? 0),
                $approvedCount,
                $totalCount,
            ));
        }, 'serviceApproved', $pm);
    }

    public function serviceRejected(User $recipient, ServiceRequestService $service, string $reason, bool $notifyOfficer = false): void
    {
        $this->safeSend(function () use ($recipient, $service, $reason, $notifyOfficer) {
            $dept = $service->department;
            $req  = $dept?->serviceRequest;

            $recipient->notify(new ServiceRejectedNotification(
                (string) $service->service_name,
                (string) ($dept?->department_code ?? ''),
                $this->contextReference($req) ?? 'N/A',
                (int) ($req?->id ?? 0),
                $reason,
                $notifyOfficer,
            ));
        }, 'serviceRejected', $recipient);
    }

    public function serviceReverted(User $officer, ServiceRequestService $service, string $reason): void
    {
        $this->safeSend(function () use ($officer, $service, $reason) {
            $dept = $service->department;
            $req  = $dept?->serviceRequest;

            $officer->notify(new ServiceRevertedNotification(
                (string) $service->service_name,
                (string) ($dept?->department_code ?? ''),
                $this->contextReference($req) ?? 'N/A',
                (int) ($req?->id ?? 0),
                $reason,
            ));
        }, 'serviceReverted', $officer);
    }

    public function scopingCompleted(User $pm, ServiceRequest $req, int $approved, int $total): void
    {
        $this->safeSend(function () use ($pm, $req, $approved, $total) {
            $pm->notify(new ScopingCompletedNotification(
                $this->contextReference($req) ?? 'N/A',
                (int) $req->id,
                $total,
                $approved,
            ));
        }, 'scopingCompleted', $pm);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Approvals
    // ─────────────────────────────────────────────────────────────────────────

    public function nextApprover(User $recipient, AcquisitionRequest $request, string $type = 'approver'): void
    {
        $this->safeSend(function () use ($recipient, $request, $type) {
            $recipient->notify(new NextApproverNotification($request, $type));
        }, "nextApprover:{$type}", $recipient);
    }

    public function nextDueDiligenceApprover(User $recipient, AcquisitionDueDiligence $dd, string $type = 'approver'): void
    {
        $this->safeSend(function () use ($recipient, $dd, $type) {
            $recipient->notify(new NextDueDiligenceApproverNotification($dd, $type));
        }, "nextDueDiligenceApprover:{$type}", $recipient);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // New coverage (Phase 5)
    // ─────────────────────────────────────────────────────────────────────────

    public function virementUploaded(User $recipient, AcquisitionRequest $request, string $uploadedByName): void
    {
        $this->safeSend(function () use ($recipient, $request, $uploadedByName) {
            $recipient->notify(new VirementUploadedNotification($request, $uploadedByName));
        }, 'virementUploaded', $recipient);
    }

    public function gisSurveyCompleted(User $recipient, \App\Models\GisLand $land, ?string $contextReference = null): void
    {
        $this->safeSend(function () use ($recipient, $land, $contextReference) {
            $recipient->notify(new GisSurveyCompletedNotification($land, $contextReference));
        }, 'gisSurveyCompleted', $recipient);
    }

    public function disputeRaised(User $recipient, string $referenceNo, string $summary, string $raisedByName, string $url): void
    {
        $this->safeSend(function () use ($recipient, $referenceNo, $summary, $raisedByName, $url) {
            $recipient->notify(new DisputeRaisedNotification($referenceNo, $summary, $raisedByName, $url));
        }, 'disputeRaised', $recipient);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Resolve a human-readable reference (e.g. REQ-2026-000123) for the request
     * across both Acquisition and Due Diligence contexts.
     */
    public function contextReference(?ServiceRequest $req): ?string
    {
        if (!$req || !$req->context_type || !$req->context_id) {
            return null;
        }

        if ($req->context_type === ServiceRequest::CONTEXT_ACQUISITION_REQUEST) {
            return (string) DB::table('acquisition_requests')
                ->where('id', $req->context_id)
                ->value('reference_no');
        }

        if ($req->context_type === ServiceRequest::CONTEXT_DUE_DILIGENCE) {
            $row = DB::table('acquisition_due_diligences')->where('id', $req->context_id)->first();
            return (string) ($row->ref_no ?? $row->acquisition_request_reference_no ?? '');
        }

        return null;
    }

    /**
     * Wrap a notification dispatch so a broken email transport never
     * kills the business action that triggered it.
     */
    protected function safeSend(callable $fn, string $label, ?User $recipient = null): void
    {
        try {
            $fn();
        } catch (\Throwable $e) {
            Log::warning("LamsNotifier[{$label}] failed", [
                'user_id' => $recipient?->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
