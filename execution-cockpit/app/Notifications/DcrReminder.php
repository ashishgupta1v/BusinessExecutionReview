<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

/**
 * Daily "file your Day Close Report" nudge.
 * Push is the primary channel; mail is the fallback for users who haven't
 * installed the PWA / enabled push (notably iOS before Add-to-Home-Screen).
 */
class DcrReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $workspaceName,
        public int $currentStreak = 0,
    ) {}

    /** Channels are resolved per-user from their preferences. */
    public function via(object $notifiable): array
    {
        $channels = $notifiable->notificationPreference?->channels ?? ['webpush'];
        $map = ['webpush' => WebPushChannel::class, 'mail' => 'mail'];
        return array_values(array_intersect_key($map, array_flip($channels)));
    }

    public function toWebPush(object $notifiable, $notification): WebPushMessage
    {
        $tail = $this->currentStreak > 0
            ? "Keep your {$this->currentStreak}-day streak alive 🔥"
            : "Two minutes now beats a lost day.";

        return (new WebPushMessage)
            ->title('Time to close the day')
            ->icon('/icons/icon-192.png')
            ->badge('/icons/badge.png')
            ->body("File your Day Close Report for {$this->workspaceName}. {$tail}")
            ->action('Open cockpit', 'open_dcr')
            ->data(['url' => route('dcr.show', absolute: false)])
            ->options(['TTL' => 3600]); // stale after an hour — a late daily nudge is noise
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Day Close Report is waiting')
            ->greeting("Evening, {$notifiable->name}")
            ->line("Take 90 seconds to close out today for {$this->workspaceName}.")
            ->action('File Day Close Report', route('dcr.show'))
            ->line($this->currentStreak > 0
                ? "You're on a {$this->currentStreak}-day streak — don't break it."
                : 'Discipline over duration.');
    }
}
