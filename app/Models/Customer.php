<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Customer extends Model
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'company_name',
        'tax_id',
        'notes',
        'status'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    
    /**
     * Obtiene las preferencias de notificación del cliente.
     */
    public function notificationPreference()
    {
        return $this->hasOne(NotificationPreference::class);
    }
    
    /**
     * Asegura que el cliente tenga preferencias de notificación.
     * Si no existen, las crea con valores predeterminados.
     */
    public function getOrCreateNotificationPreference()
    {
        if (!$this->notificationPreference) {
            return $this->notificationPreference()->create([
                'project_created' => true,
                'project_updated' => true,
                'project_status_changed' => true,
                'project_comment_added' => true,
                'project_completed' => true,
                'email_notifications' => true,
                'database_notifications' => true
            ]);
        }
        
        return $this->notificationPreference;
    }
}
