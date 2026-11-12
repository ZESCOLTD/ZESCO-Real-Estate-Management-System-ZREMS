<?php

namespace App\Notifications;

use App\Models\LeaseAgreement;
use App\Notifications\Concerns\RespectsUserPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaseExpiryNotification extends Notification implements ShouldQueue
{
    use Queueable, RespectsUserPreferences;

    public function __construct(
        private readonly LeaseAgreement $lease,
        private readonly int $daysBeforeEnd
    ) {}

    public function via($notifiable): array
    {
        $channels = $this->preferredChannels($notifiable, ['database', 'mail'], 'leases');

        if (blank($notifiable->email ?? null)) {
            $channels = array_values(array_diff($channels, ['mail']));
        }

        return $channels;
    }

    public function toArray($notifiable): array
    {
        $registerRoute = $this->lease->zesco_role === 'landlord'
            ? 'lease_agreements.landlord_register'
            : 'lease_agreements.index';

        return [
            'title' => 'Lease expiring soon',
            'message' => "{$this->lease->lease_number} expires in {$this->daysBeforeEnd} days.",
            'url' => route($registerRoute),
            'icon' => 'fas fa-calendar-exclamation text-warning',
            'lease_number' => $this->lease->lease_number,
            'days_before_end' => $this->daysBeforeEnd,
            'end_date' => $this->lease->end_date?->toDateString(),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $registerRoute = $this->lease->zesco_role === 'landlord'
            ? 'lease_agreements.landlord_register'
            : 'lease_agreements.index';

        return (new MailMessage)
            ->subject("Lease {$this->lease->lease_number} expires in {$this->daysBeforeEnd} days")
            ->greeting('Hello ' . ($notifiable->name ?? 'Lease Officer') . ',')
            ->line('A lease assigned to you is approaching its end date.')
            ->line('Lease: ' . $this->lease->lease_number)
            ->line('Counterparty: ' . ($this->lease->zesco_role === 'landlord' ? $this->lease->tenant_name : $this->lease->landlord_name))
            ->line('End date: ' . ($this->lease->end_date?->format('d M Y') ?? 'Not recorded'))
            ->action('Open Lease Register', route($registerRoute));
    }
}