<?php

namespace App\Notifications;

use App\Notifications\Concerns\RespectsUserPreferences;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaseWorkflowUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable, RespectsUserPreferences;

    public function __construct(
        private readonly string $reference,
        private readonly string $title,
        private readonly string $message,
        private readonly string $url
    ) {}

    public function via($notifiable): array
    {
        $channels = $this->preferredChannels($notifiable, ['database', 'mail'], 'lease_approvals');

        if (blank($notifiable->email ?? null)) {
            $channels = array_values(array_diff($channels, ['mail']));
        }

        return $channels;
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'icon' => 'fas fa-file-signature text-info',
            'reference' => $this->reference,
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title . ': ' . $this->reference)
            ->greeting('Hello ' . ($notifiable->name ?? 'Real Estate Officer') . ',')
            ->line($this->message)
            ->line('Reference: ' . $this->reference)
            ->action('Open Lease Application', $this->url);
    }
}