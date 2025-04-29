<?php

namespace App\Traits;

use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\RoutesNotifications;

trait CustomerNotifiable
{
    use Notifiable;
    
    /**
     * Get the notification routing information for the mail channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return array|string
     */
    public function routeNotificationForMail($notification)
    {
        return $this->email;
    }
    
    /**
     * Get the entity's notifications.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function notifications()
    {
        return $this->morphMany(config('notifications.notification_model', \Illuminate\Notifications\DatabaseNotification::class), 'notifiable')
                    ->orderBy('created_at', 'desc');
    }
    
    /**
     * Get the entity's read notifications.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function readNotifications()
    {
        return $this->notifications()->whereNotNull('read_at');
    }

    /**
     * Get the entity's unread notifications.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at');
    }
}