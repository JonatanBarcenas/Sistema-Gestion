<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'project_id',
        'order_id',
        'type',
        'estimated_hours',
        'actual_hours',
        'materials_needed',
        'notes',
        'attachments',
        'checklist',
        'color',
        'position',
        'start_date',
        'completed_at',
        'progress',
        'blocked_by',
        'dependencies'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'start_date' => 'datetime',
        'completed_at' => 'datetime',
        'attachments' => 'array',
        'checklist' => 'array',
        'blocked_by' => 'array',
        'dependencies' => 'array',
        'progress' => 'integer'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_users')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function assignUsers(array $userIds, string $role = 'assignee'): void
    {
        $assignments = [];
        foreach ($userIds as $userId) {
            $assignments[$userId] = ['role' => $role];
        }
        $this->assignees()->sync($assignments);
    }

    public function updateProgress(int $progress): void
    {
        $this->progress = $progress;
        if ($progress >= 100) {
            $this->completed_at = now();
            $this->status = 'completed';
        }
        $this->save();
    }

    public function isBlocked(): bool
    {
        if (empty($this->blocked_by)) {
            return false;
        }

        return Task::whereIn('id', $this->blocked_by)
            ->where('status', '!=', 'completed')
            ->exists();
    }

    public function dependenciesCompleted(): bool
    {
        if (empty($this->dependencies)) {
            return true;
        }

        return Task::whereIn('id', $this->dependencies)
            ->where('status', '!=', 'completed')
            ->doesntExist();
    }

    public function getTotalWorkedHours(): float
    {
        return $this->actual_hours ?? 0;
    }

    public function getEstimatedRemainingHours(): float
    {
        return max(0, ($this->estimated_hours ?? 0) - ($this->actual_hours ?? 0));
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'completed';
    }
}
