<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable, \App\Notifications\Concerns\RespectsUserPreferences;

    private string $serviceName;
    private string $departmentCode;
    private string $contextReference;
    private int $serviceRequestId;
    private int $approvedCount;
    private int $totalCount;

    public function __construct(
        string $serviceName,
        string $departmentCode,
        string $contextReference,
        int $serviceRequestId,
        int $approvedCount = 0,
        int $totalCount = 0
    ) {
        $this->serviceName = $serviceName;
        $this->departmentCode = $departmentCode;
        $this->contextReference = $contextReference;
        $this->serviceRequestId = $serviceRequestId;
        $this->approvedCount = $approvedCount;
        $this->totalCount = $totalCount;
    }

    public function via(object $notifiable): array
    {
        return $this->preferredChannels($notifiable, ['mail', 'database'], 'scoping');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Service Approved',
            'message' => "{$this->serviceName} ({$this->departmentCode}) approved for {$this->contextReference}.",
            'url'     => route('service_requests.show', ['id' => $this->serviceRequestId]),
            'icon'    => 'fas fa-thumbs-up text-success',
            'progress' => $this->totalCount > 0 ? "{$this->approvedCount}/{$this->totalCount}" : null,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Scoping Service Approved - {$this->serviceName}")
            ->greeting("Hello {$notifiable->name},")
            ->line("A scoping service has been approved by the department HOD:")
            ->line("**Service:** {$this->serviceName}")
            ->line("**Department:** {$this->departmentCode}")
            ->line("**Application Reference:** {$this->contextReference}")
            ->action('Review Service Request', route('service_requests.show', ['id' => $this->serviceRequestId]));

        if ($this->approvedCount > 0 && $this->totalCount > 0) {
            $mail->line("**Progress:** {$this->approvedCount} of {$this->totalCount} services approved");
        }

        $mail->line("The scoping workflow continues as additional services are reviewed.");

        return $mail;
    }
}
