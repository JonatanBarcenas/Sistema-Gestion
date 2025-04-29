<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TaskNotification extends Notification
{
    use Queueable;

    protected $data;
    protected $task;

    public function __construct($data, $task)
    {
        $this->data = $data;
        $this->task = $task;
    }

    public function via($notifiable)
    {
        return ['mail']; // Make sure this returns ['mail']
    }

    public function toMail($notifiable)
    {
        \Log::info('Generando email de notificación', [
            'to' => $notifiable->email,
            'subject' => $this->data['subject']
        ]);

        return (new MailMessage)
            ->subject($this->data['subject'])
            ->line($this->data['message'])
            ->line($this->data['description'])
            ->action($this->data['action_text'], $this->data['action_url'])
            ->line('Gracias por usar nuestra aplicación!');
    }
}