<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Generic daily rollup email for configured users.
 * Sections come in as a small array so the class can be reused for other digests.
 *
 * $sections example:
 * [
 *   ['label' => 'Pending review', 'count' => 3, 'url' => '/notifications'],
 *   ['label' => 'Upcoming action', 'count' => 1, 'url' => '/notifications'],
 * ]
 */
class DailyDigestNotification extends Notification implements ShouldQueue
{
    use Queueable, \App\Notifications\Concerns\RespectsUserPreferences;

    public function __construct(
        protected string $title,
        protected string $intro,
        protected array $sections,
        protected string $ctaUrl,
        protected string $category = 'scoping',
    ) {}

    public function via($notifiable): array
    {
        return $this->preferredChannels($notifiable, ['mail', 'database'], $this->category);
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->greeting('Hello ' . ($notifiable->name ?? 'User') . ',')
            ->line($this->intro);

        foreach ($this->sections as $s) {
            $count = (int) ($s['count'] ?? 0);
            $label = (string) ($s['label'] ?? '');
            if ($label === '') continue;
            $mail->line("• **{$label}:** {$count}");
        }

        return $mail
            ->action('Open ZREMS', url($this->ctaUrl))
            ->line('You are receiving this digest because of your configured responsibilities in ZREMS. Adjust delivery in Notification Preferences.');
    }

    public function toArray($notifiable): array
    {
        $total = array_sum(array_map(fn ($s) => (int) ($s['count'] ?? 0), $this->sections));
        return [
            'title'   => $this->title,
            'message' => "{$total} items awaiting your attention today.",
            'url'     => url($this->ctaUrl),
            'icon'    => 'fas fa-envelope-open-text text-primary',
            'sections' => $this->sections,
        ];
    }
}
