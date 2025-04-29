<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Project;
use App\Traits\EmailLoggable;
use Exception;

class ProjectNotification extends Notification implements ShouldQueue
{
    use Queueable, EmailLoggable;

    protected $data;
    protected $project;

    public function __construct(array $data, Project $project)
    {
        $this->data = $data;
        $this->project = $project;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        try {
            $mailMessage = (new MailMessage)
                ->view('emails.project-notification', [
                    'subject' => $this->data['subject'],
                    'message' => $this->data['message'],
                    'description' => $this->data['description'] ?? null,
                    'action_url' => $this->data['action_url'] ?? null,
                    'action_text' => $this->data['action_text'] ?? 'Ver Proyecto',
                    'notifiable' => $notifiable,
                    'project' => $this->project
                ]);

            // Registrar el correo exitoso
            $this->logEmail(
                $notifiable->email,
                $this->data['subject'],
                $this->data['message'],
                true
            );

            return $mailMessage;
        } catch (Exception $e) {
            // Registrar el error
            $this->logEmail(
                $notifiable->email,
                $this->data['subject'],
                $this->data['message'],
                false,
                $e->getMessage()
            );

            throw $e;
        }
    }

    public function toArray($notifiable)
    {
        return $this->data;
    }
}