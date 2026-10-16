<?php

namespace App\Notifications\Concerns;

/**
 * Shared helper for ZREMS notifications to honour per-user delivery preferences.
 *
 * Usage inside a Notification class:
 *
 *   use \App\Notifications\Concerns\RespectsUserPreferences;
 *
 *   public function via($notifiable): array
 *   {
 *       return $this->preferredChannels($notifiable, ['mail', 'database'], 'scoping');
 *   }
 *
 * Category is optional; when supplied it lets users mute a whole category
 * (scoping / approvals / dd / support / gis / other) via User::notify_categories JSON.
 */
trait RespectsUserPreferences
{
    protected function preferredChannels(object $notifiable, array $defaults, ?string $category = null): array
    {
        if (!method_exists($notifiable, 'wantsNotification')) {
            return $defaults;
        }

        $allowed = [];
        foreach ($defaults as $channel) {
            if ($notifiable->wantsNotification($channel, $category)) {
                $allowed[] = $channel;
            }
        }
        return $allowed;
    }
}
