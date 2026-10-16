<?php

namespace App\Notifications;

use App\Models\AcquisitionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NextApproverNotification extends Notification implements ShouldQueue
{
    use Queueable, \App\Notifications\Concerns\RespectsUserPreferences;

    protected AcquisitionRequest $request;
    protected string $type; // 'approver' | 'progress' | 'fully_approved'

    /**
     * Create a new notification instance.
     *
     * @param AcquisitionRequest $request
     * @param string $type
     */
    public function __construct(AcquisitionRequest $request, string $type = 'approver')
    {
        $this->request = $request;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return $this->preferredChannels($notifiable, ['mail', 'database'], 'approvals');
    }

    public function toArray($notifiable): array
    {
        $map = [
            'approver'       => ['Approval Needed', "{$this->request->reference_no} awaits your approval.", 'fas fa-user-check text-primary'],
            'progress'       => ['Request Progressed', "{$this->request->reference_no} moved to next approver.", 'fas fa-forward text-info'],
            'rejected'       => ['Request Rejected', "{$this->request->reference_no} has been rejected.", 'fas fa-times-circle text-danger'],
            'reverted'       => ['Request Reverted', "{$this->request->reference_no} was reverted for changes.", 'fas fa-undo text-warning'],
            'fully_approved' => ['Request Fully Approved', "{$this->request->reference_no} has been fully approved.", 'fas fa-check-double text-success'],
        ];
        [$title, $message, $icon] = $map[$this->type] ?? ['Acquisition Update', "Update on {$this->request->reference_no}.", 'fas fa-bell text-secondary'];

        return [
            'title'   => $title,
            'message' => $message,
            'url'     => $this->type === 'approver'
                ? url("/acquisitions/approvals/{$this->request->id}")
                : url("/acquisitions/requests/{$this->request->id}/edit"),
            'icon'    => $icon,
            'reference_no' => $this->request->reference_no,
            'type'    => $this->type,
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $mail = new MailMessage;

        if ($this->type === 'approver') {
            return $mail
                ->subject('Action Required: Acquisition Request Awaiting Your Approval')
                ->greeting('Hello ' . $notifiable->name . ',')
                ->line('An acquisition request requires your review and approval.')
                ->line('Request Reference: ' . $this->request->reference_no)
                ->action('Review Request', url("/acquisitions/approvals/{$this->request->id}"))
                ->line('Please take the necessary action promptly.');
        }

        if ($this->type === 'progress') {
            return $mail
                ->subject('Your Acquisition Request Has Progressed')
                ->greeting('Hello ' . $notifiable->name . ',')
                ->line('Your acquisition request has been approved by the current approver.')
                ->line('Request Reference: ' . $this->request->reference_no)
                ->line('It is now awaiting review by the next approver.')
                ->action('View Request', url("/acquisitions/requests/{$this->request->id}/edit"))
                ->line('You will be notified again once the approval process is complete.');
        }

        if ($this->type === 'rejected') {
            return $mail
                ->subject('Your Acquisition Request Has Been Rejected')
                ->greeting('Hello ' . $notifiable->name . ',')
                ->line('Your acquisition request has been rejected by the current approver.')
                ->line('Request Reference: ' . $this->request->reference_no)
                ->line('It will no longer proceed. If you still need , create a new request and ensure to address any feedback provided by the approver.')
                ->action('View Request', url("/acquisitions/requests/{$this->request->id}/edit"));
        }

        if ($this->type === 'fully_approved') {
            return $mail
                ->subject('Your Acquisition Request Has Been Fully Approved')
                ->greeting('Hello ' . $notifiable->name . ',')
                ->line('We are pleased to inform you that your acquisition request has been fully approved.')
                ->line('Request Reference: ' . $this->request->reference_no)
                ->line('The approval process has now been completed successfully.')
                ->action('View Request', url("/acquisitions/requests/{$this->request->id}/edit"))
                ->line('Thank you.');
        }
        if ($this->type === 'reverted') {
            return $mail
                ->subject('Your Acquisition Request Has Been Rever')
                ->greeting('Hello ' . $notifiable->name . ',')
                ->line('Your acquisition request has been reverted by the current approver.')
                ->line('Request Reference: ' . $this->request->reference_no)
                ->line('Please review the approver\'s feedback, make necessary adjustments, and resubmit for approval.')
                ->action('View Request', url("/acquisitions/requests/{$this->request->id}/edit"));
        }
        return $mail
            ->subject('Acquisition Request Update')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('There is an update on your acquisition request.')
            ->line('Request Reference: ' . $this->request->reference_no)
            ->action('View Request', url("/acquisitions/requests/{$this->request->id}/edit"));
    }
}
