<?php

namespace App\Traits;

use App\Models\EmailLog;
use Exception;
use Illuminate\Support\Facades\Log;

trait EmailLoggable
{
    protected function logEmail($recipient, $subject, $content, $success = true, $error = null)
    {
        try {
            EmailLog::create([
                'recipient' => $recipient,
                'subject' => $subject,
                'content' => $content,
                'success' => $success,
                'error_message' => $error,
                'notification_type' => get_class($this),
                'sent_at' => now()
            ]);
        } catch (Exception $e) {
            Log::error('Error al registrar el correo: ' . $e->getMessage(), [
                'recipient' => $recipient,
                'subject' => $subject
            ]);
        }
    }
}