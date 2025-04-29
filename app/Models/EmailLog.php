<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipient',
        'subject',
        'content',
        'success',
        'error_message',
        'notification_type',
        'sent_at'
    ];

    protected $casts = [
        'success' => 'boolean',
        'sent_at' => 'datetime'
    ];

    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }
}