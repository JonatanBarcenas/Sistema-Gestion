<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'project_created',
        'project_updated',
        'project_status_changed',
        'project_comment_added',
        'project_completed',
        'email_notifications',
        'database_notifications'
    ];

    protected $casts = [
        'project_created' => 'boolean',
        'project_updated' => 'boolean',
        'project_status_changed' => 'boolean',
        'project_comment_added' => 'boolean',
        'project_completed' => 'boolean',
        'email_notifications' => 'boolean',
        'database_notifications' => 'boolean'
    ];

    /**
     * Obtiene el cliente asociado a esta preferencia de notificación.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}