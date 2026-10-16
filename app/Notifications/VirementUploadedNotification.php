<?php

namespace App\Notifications;

use App\Models\AcquisitionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Fires when an approver uploads a virement document on an Acquisition Request
 * (mandatory when budget approved = No). Sent to the PM/originator and the
 * current approver so they see funds are earmarked.
 */
class VirementUploadedNotification extends Notification implements ShouldQueue
{
    use Queueable, \App\Notifications\Concerns\RespectsUserPreferences;

    public function __construct(
        protected AcquisitionRequest $request,
        protected string $uploadedByName,
    ) {}

    public function via($notifiable): array
    {
        return $this->preferredChannels($notifiable, ['mail', 'database'], 'approvals');
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Virement uploaded for {$this->request->reference_no}")
            ->greeting('Hello ' . ($notifiable->name ?? 'User') . ',')
            ->line("A virement document has been uploaded for acquisition request **{$this->request->reference_no}**.")
            ->line("Uploaded by: {$this->uploadedByName}")
            ->line('The approval workflow can now continue.')
            ->action('View Request', url("/acquisitions/approvals/{$this->request->id}"));
    }

    public function toArray($notifiable): array
    {
        return [
            'title'   => 'Virement Uploaded',
            'message' => "Virement uploaded for {$this->request->reference_no} by {$this->uploadedByName}.",
            'url'     => url("/acquisitions/approvals/{$this->request->id}"),
            'icon'    => 'fas fa-file-invoice-dollar text-success',
            'reference_no' => $this->request->reference_no,
        ];
    }
}
