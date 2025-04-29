<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'client_id',
        'status',
        'start_date',
        'end_date',
        'budget',
        'actual_cost',
        'priority',
        'type', // tipo de proyecto: diseño, impresión, publicidad, etc.
        'notes',
        'attachments',
        'color',
        'position'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'budget' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'attachments' => 'array',
        'position' => 'integer'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class)->orderBy('position');
    }

    public function team()
    {
        return $this->belongsToMany(User::class, 'project_team')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(ProjectComment::class);
    }

    public function getProgressAttribute()
    {
        $totalTasks = $this->tasks()->count();
        if ($totalTasks === 0) return 0;

        $completedTasks = $this->tasks()->where('status', 'completed')->count();
        return round(($completedTasks / $totalTasks) * 100);
    }

    public function getTimeRemainingAttribute()
    {
        if (!$this->end_date) return null;
        
        $now = now();
        if ($now > $this->end_date) return 0;
        
        return $now->diffInDays($this->end_date);
    }
}