<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskViewController extends Controller
{
    public function __construct(private readonly TaskService $taskService)
    {
    }

    public function index(Request $request)
    {
        $status = $request->query('status');

        if ($status && !in_array($status, Task::allStatuses(), true)) {
            abort(422, 'Invalid status. Must be one of: ' . implode(', ', Task::allStatuses()));
        }

        $tasks = $this->taskService->list($status);

        return view('tasks.index', [
            'tasks' => $tasks,
        ]);
    }

    public function report(Request $request)
    {
        // Keep your existing report method here
        $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $date = $request->query('date', now()->toDateString());
        $report = $this->taskService->dailyReport($date);

        return view('tasks.report', compact('report', 'date'));   // change view name if needed
    }

    public function apiDocs()
    {
        // Keep your existing apiDocs method
        return view('tasks.api-docs');
    }
}