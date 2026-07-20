<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

/** Friday nudge to complete the Weekly Review (prefilled from the week's DCRs). */
class WeeklyReviewReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $workspaceName) {}

    public function via(object $notifiable): array
    {
        $channels = $notifiable->notificationPreference?->channels ?? ['webpush'];
        $map = ['webpush' => WebPushChannel::class, 'mail' => 'mail'];
        return array_values(array_intersect_key($map, array_flip($channels)));
    }

    public function toWebPush(object $notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title('Time for your Weekly Review')
            ->icon('/icons/icon-192.png')
            ->badge('/icons/badge.png')
            ->body("Step back on {$this->workspaceName}: what moved the needle this week? Set next week's top 3.")
            ->action('Review the week', 'open_weekly')
            ->data(['url' => route('weekly.show', absolute: false)])
            ->options(['TTL' => 6 * 3600]);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Weekly Review is ready')
            ->greeting("Hi {$notifiable->name}")
            ->line("This week's DCRs are rolled up and waiting for {$this->workspaceName}.")
            ->action('Complete Weekly Review', route('weekly.show'))
            ->line('Ask the one question that matters: what moved the needle this week?');
    }
}
