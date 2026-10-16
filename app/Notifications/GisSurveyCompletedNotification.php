<?php

namespace App\Notifications;

use App\Models\GisLand;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Fires when a Geomatics officer completes a GIS survey (LandCreate::save).
 * Recipients: HOD Geomatics (for review) + PM (for progress visibility).
 */
class GisSurveyCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable, \App\Notifications\Concerns\RespectsUserPreferences;

    public function __construct(
        protected GisLand $land,
        protected ?string $contextReference = null,
    ) {}

    public function via($notifiable): array
    {
        return $this->preferredChannels($notifiable, ['mail', 'database'], 'gis');
    }

    public function toMail($notifiable): MailMessage
    {
        $ref = $this->contextReference ? " (Application: {$this->contextReference})" : '';

        return (new MailMessage)
            ->subject("GIS Survey Completed: {$this->land->plot_number}")
            ->greeting('Hello ' . ($notifiable->name ?? 'User') . ',')
            ->line("A GIS survey has been completed and submitted for review{$ref}.")
            ->line("Plot Number: **{$this->land->plot_number}**")
            ->line("Property: {$this->land->property_name}")
            ->when(!empty($this->land->area), fn ($m) => $m->line("Area: {$this->land->area} ha"))
            ->action('View Land Parcel', route('gis.lands.show', ['id' => $this->land->id]));
    }

    public function toArray($notifiable): array
    {
        return [
            'title'   => 'GIS Survey Completed',
            'message' => "Survey for plot {$this->land->plot_number} submitted for review.",
            'url'     => route('gis.lands.show', ['id' => $this->land->id]),
            'icon'    => 'fas fa-map-marked-alt text-info',
            'plot_number' => $this->land->plot_number,
            'context_reference' => $this->contextReference,
        ];
    }
}
