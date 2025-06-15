<?php

namespace App\Listeners;

use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Support\Arr;

class DeleteExpiredNotificationTokens
{
    /**
     * Handle the event.
     */
    public function handle(NotificationFailed $event): void
    {
        $report = Arr::get($event->data, 'report');

        $target = $report->target();

        $event->notifiable->fcmTokens()
            ->where('token', $target->value())
            ->delete();
    }
}
