<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Generic dispute-raised notification for future dispute module integration.
 * Recipients: Legal HOD + any assigned dispute officers.
 */
class DisputeRaisedNotification extends Notification implements ShouldQueue
{
    use Queueable, \App\Notifications\Concerns\RespectsUserPreferences;

    public function __construct(
        protected string $referenceNo,
        protected string $summary,
        protected string $raisedByName,
        protected string $url,
    ) {}

    public function via($notifiable): array
    {
        return $this->preferredChannels($notifiable, ['mail', 'database'], 'approvals');
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Dispute Raised: {$this->referenceNo}")
            ->greeting('Hello ' . ($notifiable->name ?? 'User') . ',')
            ->line("A dispute has been raised on **{$this->referenceNo}**.")
            ->line("Raised by: {$this->raisedByName}")
            ->line('Summary:')
            ->line($this->summary)
            ->action('Review Dispute', $this->url);
    }

    public function toArray($notifiable): array
    {
        return [
            'title'   => 'Dispute Raised',
            'message' => "{$this->raisedByName} raised a dispute on {$this->referenceNo}.",
            'url'     => $this->url,
            'icon'    => 'fas fa-exclamation-triangle text-danger',
            'reference_no' => $this->referenceNo,
        ];
    }
}
