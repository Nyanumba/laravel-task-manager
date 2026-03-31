<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    // --- Constants ---
    const PRIORITY_LOW    = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH   = 'high';

    const STATUS_PENDING     = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_DONE        = 'done';

    const STATUS_TRANSITIONS = [
        self::STATUS_PENDING     => self::STATUS_IN_PROGRESS,
        self::STATUS_IN_PROGRESS => self::STATUS_DONE,
    ];

    // --- Eloquent config ---
    protected $fillable = [
        'title',
        'due_date',
        'priority',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date:Y-m-d',
    ];

    // --- Business logic helpers ---

    public function nextStatus(): ?string
    {
        return self::STATUS_TRANSITIONS[$this->status] ?? null;
    }

    public function canTransitionTo(string $newStatus): bool
    {
        return $this->nextStatus() === $newStatus;
    }

    public function advanceStatus(): void
    {
        $next = $this->nextStatus();
        if ($next === null) {
            throw new \LogicException("Task is already '{$this->status}' and cannot be advanced.");
        }
        $this->status = $next;
        $this->save();
    }

    public function isDeletable(): bool
    {
        return $this->status === self::STATUS_DONE;
    }

    // --- Query scopes ---

    public function scopeOfStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeSortedByPriorityAndDate(Builder $query): Builder
    {
        return $query
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
            ->orderBy('due_date', 'asc');
    }

    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('due_date', $date);
    }

    // --- Static helpers ---

    public static function allPriorities(): array
    {
        return [self::PRIORITY_LOW, self::PRIORITY_MEDIUM, self::PRIORITY_HIGH];
    }

    public static function allStatuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_IN_PROGRESS, self::STATUS_DONE];
    }
}