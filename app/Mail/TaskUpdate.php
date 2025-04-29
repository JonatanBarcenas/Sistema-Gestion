<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Task;

class TaskUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public $task;
    public $message;
    public $actionUrl;
    public $changes;

    public function __construct($task, $message, $actionUrl, $changes = [])
    {
        $this->task = $task;
        $this->message = $message;
        $this->actionUrl = $actionUrl;
        $this->changes = $changes;
    }

    public function build()
    {
        return $this->markdown('emails.tasks.update')
                    ->subject("Actualización en tarea #{$this->task->id}")
                    ->with([
                        'taskTitle' => $this->task->title,
                        'taskStatus' => $this->task->status,
                        'changes' => $this->changes,
                        'orderNumber' => $this->task->order->order_number ?? 'N/A'
                    ]);
    }
}