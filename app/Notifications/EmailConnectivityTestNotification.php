<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailConnectivityTestNotification extends Notification
{
    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('ZREMS email delivery test')
            ->greeting('Hello ' . ($notifiable->name ?? 'ZREMS user') . ',')
            ->line('This is a test message requested from your ZREMS Notification Preferences page.')
            ->line('If this arrived in your inbox, the configured mail transport delivered the message.');
    }
}