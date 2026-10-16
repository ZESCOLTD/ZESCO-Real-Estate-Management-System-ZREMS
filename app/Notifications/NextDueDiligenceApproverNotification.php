<?php

namespace App\Notifications;

use App\Models\AcquisitionDueDiligence;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NextDueDiligenceApproverNotification extends Notification implements ShouldQueue
{
    use Queueable, \App\Notifications\Concerns\RespectsUserPreferences;

    protected AcquisitionDueDiligence $request;
    protected string $type; // 'approver' | 'progress' | 'fully_approved'

    /**
     * Create a new notification instance.
     *
     * @param AcquisitionDueDiligence $request
     * @param string $type
     */
    public function __construct(AcquisitionDueDiligence $request, string $type = 'approver')
    {
        $this->request = $request;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return $this->preferredChannels($notifiable, ['mail', 'database'], 'dd');
    }

    public function toArray($notifiable): array
    {
        $map = [
            'approver'       => ['DD Approval Needed', "Due diligence {$this->request->reference_no} awaits your approval.", 'fas fa-user-check text-primary'],
            'progress'       => ['DD Progressed', "Due diligence {$this->request->reference_no} moved to next approver.", 'fas fa-forward text-info'],
            'rejected'       => ['DD Rejected', "Due diligence {$this->request->reference_no} has been rejected.", 'fas fa-times-circle text-danger'],
            'reverted'       => ['DD Reverted', "Due diligence {$this->request->reference_no} was reverted.", 'fas fa-undo text-warning'],
            'fully_approved' => ['DD Fully Approved', "Due diligence {$this->request->reference_no} fully approved.", 'fas fa-check-double text-success'],
        ];
        [$title, $message, $icon] = $map[$this->type] ?? ['Due Diligence Update', "Update on {$this->request->reference_no}.", 'fas fa-bell text-secondary'];

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
                ->subject('Action Required: Due Diligence form Awaiting Your Approval')
                ->greeting('Hello ' . $notifiable->name . ',')
                ->line('An due diligence form requires your review and approval.')
                ->line('Request Reference: ' . $this->request->reference_no)
                ->action('Review Request', url("/acquisitions/approvals/{$this->request->id}"))
                ->line('Please take the necessary action promptly.');
        }

        if ($this->type === 'progress') {
            return $mail
                ->subject('Your Due Diligence form Has Progressed')
                ->greeting('Hello ' . $notifiable->name . ',')
                ->line('Your due diligence form has been approved by the current approver.')
                ->line('Request Reference: ' . $this->request->reference_no)
                ->line('It is now awaiting review by the next approver.')
                ->action('View Request', url("/acquisitions/requests/{$this->request->id}/edit"))
                ->line('You will be notified again once the approval process is complete.');
        }

        if ($this->type === 'rejected') {
            return $mail
                ->subject('Your Due Diligence form Has Been Rejected')
                ->greeting('Hello ' . $notifiable->name . ',')
                ->line('Your due diligence form has been rejected by the current approver.')
                ->line('Request Reference: ' . $this->request->reference_no)
                ->line('It will no longer proceed. If you still need , create a new request and ensure to address any feedback provided by the approver.')
                ->action('View Request', url("/acquisitions/requests/{$this->request->id}/edit"));
        }

        if ($this->type === 'fully_approved') {
            return $mail
                ->subject('Your Due Diligence form Has Been Fully Approved')
                ->greeting('Hello ' . $notifiable->name . ',')
                ->line('We are pleased to inform you that your due diligence form has been fully approved.')
                ->line('Request Reference: ' . $this->request->reference_no)
                ->line('The approval process has now been completed successfully.')
                ->action('View Request', url("/acquisitions/requests/{$this->request->id}/edit"))
                ->line('Thank you.');
        }
        if ($this->type === 'reverted') {
            return $mail
                ->subject('Your Due Diligence form Has Been Rever')
                ->greeting('Hello ' . $notifiable->name . ',')
                ->line('Your due diligence form has been reverted by the current approver.')
                ->line('Request Reference: ' . $this->request->reference_no)
                ->line('Please review the approver\'s feedback, make necessary adjustments, and resubmit for approval.')
                ->action('View Request', url("/acquisitions/requests/{$this->request->id}/edit"));
        }
        return $mail
            ->subject('Due Diligence form Update')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('There is an update on your due diligence form.')
            ->line('Request Reference: ' . $this->request->reference_no)
            ->action('View Request', url("/acquisitions/requests/{$this->request->id}/edit"));
    }
}
