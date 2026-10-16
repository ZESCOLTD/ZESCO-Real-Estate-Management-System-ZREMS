<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FormSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable, \App\Notifications\Concerns\RespectsUserPreferences;

    private string $serviceName;
    private string $officerName;
    private string $departmentCode;
    private string $contextReference;
    private int $serviceRequestId;

    public function __construct(
        string $serviceName,
        string $officerName,
        string $departmentCode,
        string $contextReference,
        int $serviceRequestId
    ) {
        $this->serviceName = $serviceName;
        $this->officerName = $officerName;
        $this->departmentCode = $departmentCode;
        $this->contextReference = $contextReference;
        $this->serviceRequestId = $serviceRequestId;
    }

    public function via(object $notifiable): array
    {
        return $this->preferredChannels($notifiable, ['mail', 'database'], 'scoping');
    }

    // NOTE: toArray() already implemented below is used by database channel.

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Scoping Form Submitted - {$this->serviceName}")
            ->greeting("Hello {$notifiable->name},")
            ->line("A scoping form has been submitted and requires your review:")
            ->line("**Service:** {$this->serviceName}")
            ->line("**Submitted By:** {$this->officerName}")
            ->line("**Department:** {$this->departmentCode}")
            ->line("**Application Reference:** {$this->contextReference}")
            ->action('Review Submission', route('service_requests.show', ['id' => $this->serviceRequestId]))
            ->line("Please review the submitted form at your earliest convenience.");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Scoping Task Completed',
            'service_name' => $this->serviceName,
            'submitted_by' => $this->officerName,
            'department_code' => $this->departmentCode,
            'context_reference' => $this->contextReference,
            'service_request_id' => $this->serviceRequestId,
            'message' => "{$this->serviceName} has been submitted by {$this->officerName} and needs HOD review.",
        ];
    }
}
