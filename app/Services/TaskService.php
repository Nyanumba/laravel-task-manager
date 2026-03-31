<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

class TaskService
{
    public function create(array $data): Task
    {
        return Task::create([
            'title'    => $data['title'],
            'due_date' => $data['due_date'],
            'priority' => $data['priority'],
            'status'   => Task::STATUS_PENDING,
        ]);
    }

    public function list(?string $status = null): Collection
    {
        return Task::query()
            ->ofStatus($status)
            ->sortedByPriorityAndDate()
            ->get();
    }

    public function advanceStatus(Task $task, string $requestedStatus): Task
    {
        if (!$task->canTransitionTo($requestedStatus)) {
            $allowed = $task->nextStatus();
            $message = $allowed
                ? "Cannot change status from '{$task->status}' to '{$requestedStatus}'. Only allowed next: '{$allowed}'."
                : "Task is already '{$task->status}'. No further transitions allowed.";
            throw new \InvalidArgumentException($message);
        }

        $task->advanceStatus();
        return $task->fresh();
    }

    public function delete(Task $task): void
    {
        if (!$task->isDeletable()) {
            throw new \RuntimeException(
                "Task '{$task->title}' cannot be deleted because status is '{$task->status}'. Only 'done' tasks can be deleted."
            );
        }
        $task->delete();
    }

    public function dailyReport(string $date): array
    {
        // Start with all zeros so every combination always appears
        $summary = [];
        foreach (Task::allPriorities() as $priority) {
            foreach (Task::allStatuses() as $status) {
                $summary[$priority][$status] = 0;
            }
        }

        $rows = Task::query()
            ->forDate($date)
            ->selectRaw('priority, status, COUNT(*) as total')
            ->groupBy('priority', 'status')
            ->get();

        foreach ($rows as $row) {
            $summary[$row->priority][$row->status] = (int) $row->total;
        }

        return ['date' => $date, 'summary' => $summary];
    }
}