<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ScopingServicesSelectedNotification extends Notification implements ShouldQueue
{
    use Queueable, \App\Notifications\Concerns\RespectsUserPreferences;

    public function __construct(
        protected int $serviceRequestId,
        protected string $departmentCode,
        protected ?string $contextReference = null
    ) {
    }

    public function via($notifiable): array
    {
        return $this->preferredChannels($notifiable, ['mail', 'database'], 'scoping');
    }

    public function toArray($notifiable): array
    {
        $ref = $this->contextReference ? " for {$this->contextReference}" : '';
        return [
            'title'   => 'Scoping Services Selected',
            'message' => "Scoping services selected{$ref}. Assign your team.",
            'url'     => url("/services/requests/{$this->serviceRequestId}/show"),
            'icon'    => 'fas fa-clipboard-list text-primary',
            'department_code' => $this->departmentCode,
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $subjectSuffix = $this->contextReference
            ? " for {$this->contextReference}"
            : '';

        return (new MailMessage)
            ->subject('Scoping Services Selected' . $subjectSuffix)
            ->greeting('Hello ' . ($notifiable->name ?? 'User') . ',')
            ->line('Scoping services have been selected and require departmental assignment.')
            ->line('Department: ' . $this->departmentCode)
            ->when(
                !empty($this->contextReference),
                fn (MailMessage $mail) => $mail->line('Application Reference: ' . $this->contextReference)
            )
            ->action('Open Scoping Request', url("/services/requests/{$this->serviceRequestId}/show"))
            ->line('Please assign the relevant team member(s) and proceed.');
    }
}
