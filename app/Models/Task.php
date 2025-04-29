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
        'order_id',
        'due_date',
        'priority',
        'status',
        'type',
        'estimated_hours',
        'actual_hours',
        'materials_needed',
        'notes',
        'attachments',
        'checklist',
        'color',
        'position',
        'dependencies',
        'blocked_by'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'materials_needed' => 'array',
        'attachments' => 'array',
        'checklist' => 'array',
        'is_active' => 'boolean',
        'dependencies' => 'array',
        'blocked_by' => 'array'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_users')
            ->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function dependencies()
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'dependency_id')
            ->withTimestamps();
    }

    public function dependentTasks()
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'dependency_id', 'task_id');
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

    // Add this method to safely get dependency IDs
    public function getDependencyIds(): array
    {
        return $this->dependencies()
            ->select('tasks.id')  // Explicitly specify the table name
            ->pluck('tasks.id')   // Use the table name in pluck
            ->toArray();
    }
}
