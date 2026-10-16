<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceRevertedNotification extends Notification implements ShouldQueue
{
    use Queueable, \App\Notifications\Concerns\RespectsUserPreferences;

    private string $serviceName;
    private string $departmentCode;
    private string $contextReference;
    private int $serviceRequestId;
    private string $revertReason;

    public function __construct(
        string $serviceName,
        string $departmentCode,
        string $contextReference,
        int $serviceRequestId,
        string $revertReason
    ) {
        $this->serviceName = $serviceName;
        $this->departmentCode = $departmentCode;
        $this->contextReference = $contextReference;
        $this->serviceRequestId = $serviceRequestId;
        $this->revertReason = $revertReason;
    }

    public function via(object $notifiable): array
    {
        return $this->preferredChannels($notifiable, ['mail', 'database'], 'scoping');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Form Reverted',
            'message' => "{$this->serviceName} ({$this->departmentCode}) reverted for revision on {$this->contextReference}.",
            'url'     => route('service_requests.show', ['id' => $this->serviceRequestId]),
            'icon'    => 'fas fa-undo text-warning',
            'reason'  => $this->revertReason,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Scoping Form Reverted - {$this->serviceName}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your submitted scoping form has been reverted by the department HOD for further revision:")
            ->line("**Service:** {$this->serviceName}")
            ->line("**Department:** {$this->departmentCode}")
            ->line("**Application Reference:** {$this->contextReference}")
            ->line("**HOD Feedback:**")
            ->line($this->revertReason)
            ->action('Revise & Resubmit', route('service_requests.show', ['id' => $this->serviceRequestId]))
            ->line("Please address the feedback and resubmit your form for review.");
    }
}
