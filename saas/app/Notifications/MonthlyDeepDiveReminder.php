<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

/** Start-of-month nudge for the monthly deep-dive review. */
class MonthlyDeepDiveReminder extends Notification implements ShouldQueue
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
            ->title('Monthly deep-dive time')
            ->icon('/icons/icon-192.png')
            ->badge('/icons/badge.png')
            ->body("Review last month for {$this->workspaceName}: wins, challenges, and the KPI trend.")
            ->action('Start deep-dive', 'open_monthly')
            ->data(['url' => route('dcr.show', absolute: false)])
            ->options(['TTL' => 12 * 3600]);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your monthly deep-dive is due')
            ->greeting("Hi {$notifiable->name}")
            ->line("Take an hour on {$this->workspaceName}: aggregate the month's wins, challenges, and KPI snapshot.")
            ->line('This is the artifact worth sending to your coach or accountability partner.');
    }
}
