<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    public function __construct(private readonly TaskService $taskService)
    {
    }

    // POST /api/tasks
    public function store(CreateTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->create($request->validated());

        return response()->json([
            'message' => 'Task created successfully.',
            'data'    => $task,
        ], Response::HTTP_CREATED);
    }

    // GET /api/tasks
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');

        if ($status && !in_array($status, Task::allStatuses(), true)) {
            return response()->json([
                'message' => 'Invalid status. Must be: ' . implode(', ', Task::allStatuses()),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $tasks = $this->taskService->list($status);

        if ($tasks->isEmpty()) {
            return response()->json(['message' => 'No tasks found.', 'data' => []], 200);
        }

        return response()->json([
            'message' => "Found {$tasks->count()} task(s).",
            'data'    => $tasks,
        ], 200);
    }

    // PATCH /api/tasks/{id}/status
    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): JsonResponse
    {
        try {
            $updated = $this->taskService->advanceStatus($task, $request->validated('status'));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'message' => 'Task status updated successfully.',
            'data'    => $updated,
        ], 200);
    }

    // DELETE /api/tasks/{id}
    public function destroy(Task $task): JsonResponse
    {
        try {
            $this->taskService->delete($task);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'message' => "Task '{$task->title}' deleted successfully.",
        ], 200);
    }

    // GET /api/tasks/report?date=YYYY-MM-DD
    public function report(Request $request): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $report = $this->taskService->dailyReport($request->query('date'));

        return response()->json($report, 200);
    }
}