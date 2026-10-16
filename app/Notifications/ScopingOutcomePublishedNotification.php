<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ScopingOutcomePublishedNotification extends Notification implements ShouldQueue
{
    use Queueable, \App\Notifications\Concerns\RespectsUserPreferences;

    public function __construct(
        private readonly string $referenceNo,
        private readonly string $consolidationNote,
        private readonly int $approvedServices,
        private readonly int $totalServices,
        private readonly int $requestId,
    ) {
    }

    public function via(object $notifiable): array
    {
        return $this->preferredChannels($notifiable, ['mail', 'database'], 'scoping');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Scoping Outcome Published',
            'message' => "Scoping consolidated for {$this->referenceNo} ({$this->approvedServices}/{$this->totalServices} approved).",
            'url'     => route('acquisitions.requests.show', ['id' => $this->requestId]),
            'icon'    => 'fas fa-flag-checkered text-info',
            'reference_no' => $this->referenceNo,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Scoping outcome published: {$this->referenceNo}")
            ->greeting("Hello {$notifiable->name},")
            ->line('Scoping has been completed and consolidated for your application.')
            ->line("Reference: {$this->referenceNo}")
            ->line("Approved services: {$this->approvedServices} of {$this->totalServices}")
            ->line('Consolidated outcome summary:')
            ->line($this->consolidationNote)
            ->action('View application', route('acquisitions.requests.show', ['id' => $this->requestId]));
    }
}
