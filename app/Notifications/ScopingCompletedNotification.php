<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ScopingCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable, \App\Notifications\Concerns\RespectsUserPreferences;

    private string $contextReference;
    private int $serviceRequestId;
    private int $totalServices;
    private int $approvedServices;

    public function __construct(
        string $contextReference,
        int $serviceRequestId,
        int $totalServices,
        int $approvedServices
    ) {
        $this->contextReference = $contextReference;
        $this->serviceRequestId = $serviceRequestId;
        $this->totalServices = $totalServices;
        $this->approvedServices = $approvedServices;
    }

    public function via(object $notifiable): array
    {
        return $this->preferredChannels($notifiable, ['mail', 'database'], 'scoping');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Scoping Completed',
            'message' => "All scoping services ({$this->approvedServices}/{$this->totalServices}) approved for {$this->contextReference}.",
            'url'     => route('service_requests.show', ['id' => $this->serviceRequestId]),
            'icon'    => 'fas fa-check-circle text-success',
            'context_reference' => $this->contextReference,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Scoping Completed - {$this->contextReference}")
            ->greeting("Hello {$notifiable->name},")
            ->line("All scoping services have been completed and approved!")
            ->line("**Application Reference:** {$this->contextReference}")
            ->line("**Services Approved:** {$this->approvedServices} of {$this->totalServices}")
            ->action('Review Scoping Results', route('service_requests.show', ['id' => $this->serviceRequestId]))
            ->line("The acquisition request is now ready to proceed to the next workflow step.");
    }
}
