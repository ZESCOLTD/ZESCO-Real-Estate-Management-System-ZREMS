<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ScopingTaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable, \App\Notifications\Concerns\RespectsUserPreferences;

    public function __construct(
        protected int $serviceRequestId,
        protected int $serviceRequestServiceId,
        protected int $serviceChildId,
        protected string $serviceName,
        protected string $departmentCode,
        protected ?string $contextType,
        protected ?int $contextId,
        protected ?string $contextReference = null
    ) {
    }

    public function via($notifiable): array
    {
        return $this->preferredChannels($notifiable, ['mail', 'database'], 'scoping');
    }

    public function toArray($notifiable): array
    {
        $ref = $this->contextReference ? " ({$this->contextReference})" : '';
        return [
            'title'   => 'Scoping Task Assigned',
            'message' => "You have been assigned: {$this->serviceName}{$ref}.",
            'url'     => route('service_requests.show', ['id' => $this->serviceRequestId]),
            'icon'    => 'fas fa-tasks text-primary',
            'service_name' => $this->serviceName,
            'department_code' => $this->departmentCode,
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $subjectSuffix = $this->contextReference
            ? " for {$this->contextReference}"
            : '';

        $query = array_filter([
            'context_type' => $this->contextType,
            'context_id' => $this->contextId,
            'department_code' => $this->departmentCode,
        ], fn ($value) => $value !== null && $value !== '');

        $isNewSurvey = str_contains(strtolower($this->serviceName), 'new survey')
            && str_contains(strtolower($this->departmentCode), 'geomatic');

        $formUrl = $isNewSurvey
            ? route('gis.lands.create', ['service_request_service_id' => $this->serviceRequestServiceId])
            : route('service_forms.preview', array_merge([
                'serviceChild' => $this->serviceChildId,
            ], $query));

        $actionLabel = $isNewSurvey ? 'Start Survey' : 'Start Scoping';

        return (new MailMessage)
            ->subject('Scoping Task Assigned' . $subjectSuffix)
            ->greeting('Hello ' . ($notifiable->name ?? 'User') . ',')
            ->line('You have been assigned a scoping task.')
            ->line('Service: ' . $this->serviceName)
            ->line('Department: ' . $this->departmentCode)
            ->when(
                !empty($this->contextReference),
                fn (MailMessage $mail) => $mail->line('Application Reference: ' . $this->contextReference)
            )
            ->action($actionLabel, $formUrl)
            ->line('Complete and submit the form to proceed with review.');
    }
}
