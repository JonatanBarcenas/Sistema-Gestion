<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $message;
    public $actionUrl;

    public function __construct($order, $message, $actionUrl)
    {
        $this->order = $order;
        $this->message = $message;
        $this->actionUrl = $actionUrl;
    }

    public function build()
    {
        return $this->markdown('emails.orders.update')
                    ->subject("Actualización en su pedido #{$this->order->order_number}");
    }
}