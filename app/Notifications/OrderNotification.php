<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Order;

class OrderNotification extends Notification
{
    use Queueable;

    protected $data;
    protected $order;

    public function __construct($data, $order)
    {
        $this->data = $data;
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        \Log::info('Generando email en OrderNotification', [
            'to' => $notifiable->email,
            'subject' => $this->data['subject'],
            'message' => $this->data['message']
        ]);

        try {
            return (new MailMessage)
                ->subject($this->data['subject'])
                ->line($this->data['message'])
                ->action($this->data['action_text'], $this->data['action_url'])
                ->line('Gracias por su preferencia.');
        } catch (\Exception $e) {
            \Log::error('Error generando email', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}