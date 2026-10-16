<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable, \App\Notifications\Concerns\RespectsUserPreferences;

    private string $serviceName;
    private string $departmentCode;
    private string $contextReference;
    private int $serviceRequestId;
    private string $rejectionReason;
    private bool $notifyOfficer;

    public function __construct(
        string $serviceName,
        string $departmentCode,
        string $contextReference,
        int $serviceRequestId,
        string $rejectionReason,
        bool $notifyOfficer = false
    ) {
        $this->serviceName = $serviceName;
        $this->departmentCode = $departmentCode;
        $this->contextReference = $contextReference;
        $this->serviceRequestId = $serviceRequestId;
        $this->rejectionReason = $rejectionReason;
        $this->notifyOfficer = $notifyOfficer;
    }

    public function via(object $notifiable): array
    {
        return $this->preferredChannels($notifiable, ['mail', 'database'], 'scoping');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => $this->notifyOfficer ? 'Form Requires Revision' : 'Service Rejected',
            'message' => "{$this->serviceName} ({$this->departmentCode}) rejected on {$this->contextReference}.",
            'url'     => route('service_requests.show', ['id' => $this->serviceRequestId]),
            'icon'    => 'fas fa-times-circle text-danger',
            'reason'  => $this->rejectionReason,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->notifyOfficer 
            ? "Scoping Form Requires Revision - {$this->serviceName}"
            : "Scoping Service Rejected - {$this->serviceName}";

        $greeting = $this->notifyOfficer
            ? "Hello {$notifiable->name},\n\nYour submitted scoping form requires revision:"
            : "Hello {$notifiable->name},\n\nA scoping service has been rejected:";

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line("**Service:** {$this->serviceName}")
            ->line("**Department:** {$this->departmentCode}")
            ->line("**Application Reference:** {$this->contextReference}")
            ->line("**Feedback from HOD:**")
            ->line($this->rejectionReason)
            ->action('Review Service Request', route('service_requests.show', ['id' => $this->serviceRequestId]));

        if ($this->notifyOfficer) {
            $mail->line("Please revise and resubmit your form with the requested changes.");
        }

        return $mail;
    }
}
