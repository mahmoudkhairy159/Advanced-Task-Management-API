<?php

namespace Modules\UserNotification\App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\UserNotification\App\Models\UserDatabaseNotification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class CustomNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->data['created_at']= Carbon::now();

    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database' ,FcmChannel::class];
    }

    /**
     * Get the FCM representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \NotificationChannels\Fcm\FcmMessage
     */
    public function toFcm($notifiable)
    {

        $notification = FcmNotification::create();

        $notification->title($this->data['title'])
            ->body($this->data['body'])
            ->image($this->data['image_url'] ?? null);

        return FcmMessage::create()
            ->data($this->data)
            ->notification($notification);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return $this->data;
    }
   
}
