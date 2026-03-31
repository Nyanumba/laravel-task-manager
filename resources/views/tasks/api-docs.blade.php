@extends('layouts.app')
@section('page-title') API Reference @endsection

@section('content')

@php $base = request()->getSchemeAndHttpHost(); @endphp

<div class="row">
    {{-- Sidebar nav --}}
    <div class="col-md-3 mb-3">
        <div class="card" style="position:sticky;top:1rem;">
            <div class="card-header"><h3 class="card-title mb-0">Endpoints</h3></div>
            <div class="list-group list-group-flush">
                <a href="#create" class="list-group-item list-group-item-action py-2">
                    <span class="badge badge-success mr-2">POST</span> Create Task
                </a>
                <a href="#list" class="list-group-item list-group-item-action py-2">
                    <span class="badge badge-primary mr-2">GET</span> List Tasks
                </a>
                <a href="#status" class="list-group-item list-group-item-action py-2">
                    <span class="badge badge-warning mr-2">PATCH</span> Update Status
                </a>
                <a href="#delete" class="list-group-item list-group-item-action py-2">
                    <span class="badge badge-danger mr-2">DELETE</span> Delete Task
                </a>
                <a href="#report" class="list-group-item list-group-item-action py-2">
                    <span class="badge badge-info mr-2">GET</span> Daily Report
                </a>
            </div>
        </div>
    </div>

    {{-- Endpoint cards --}}
    <div class="col-md-9">

        {{-- 1. Create --}}
        <div class="card mb-4" id="create">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <span class="badge badge-success mr-2">POST</span>
                    <code>/api/tasks</code>
                    <small class="text-muted ml-2">Create Task</small>
                </h3>
            </div>
            <div class="card-body">
                <p>Create a new task. Status is always set to <code>pending</code> automatically.</p>

                <h6 class="text-uppercase text-muted" style="font-size:.75rem;letter-spacing:.5px;">Business Rules</h6>
                <ul>
                    <li>Title must be unique per <code>due_date</code></li>
                    <li>Due date must be today or in the future</li>
                    <li>Priority must be <code>low</code>, <code>medium</code>, or <code>high</code></li>
                    <li>Status is always <code>pending</code> at creation — not settable by caller</li>
                </ul>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted" style="font-size:.75rem;">Request</h6>
                        <pre style="background:#1e1e1e;color:#d4d4d4;padding:1rem;border-radius:8px;font-size:.8rem;overflow-x:auto;">curl -X POST {{ $base }}/api/tasks \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Deploy to production",
    "due_date": "{{ today()->addDays(3)->toDateString() }}",
    "priority": "high"
  }'</pre>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted" style="font-size:.75rem;">Response <span class="badge badge-success">201</span></h6>
                        <pre style="background:#1e1e1e;color:#d4d4d4;padding:1rem;border-radius:8px;font-size:.8rem;overflow-x:auto;">{
  "message": "Task created successfully.",
  "data": {
    "id": 1,
    "title": "Deploy to production",
    "due_date": "{{ today()->addDays(3)->toDateString() }}",
    "priority": "high",
    "status": "pending",
    "created_at": "...",
    "updated_at": "..."
  }
}</pre>
                    </div>
                </div>

                <h6 class="text-uppercase text-muted mt-3" style="font-size:.75rem;">Validation Error <span class="badge badge-danger">422</span></h6>
                <pre style="background:#1e1e1e;color:#d4d4d4;padding:1rem;border-radius:8px;font-size:.8rem;">{
  "message": "The title has already been taken.",
  "errors": {
    "title": ["A task with this title already exists for that due date."],
    "due_date": ["The due date must be today or a future date."]
  }
}</pre>
            </div>
        </div>

        {{-- 2. List --}}
        <div class="card mb-4" id="list">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <span class="badge badge-primary mr-2">GET</span>
                    <code>/api/tasks</code>
                    <small class="text-muted ml-2">List Tasks</small>
                </h3>
            </div>
            <div class="card-body">
                <p>Retrieve all tasks. Use the optional <code>?status=</code> query parameter to filter.</p>

                <h6 class="text-uppercase text-muted" style="font-size:.75rem;letter-spacing:.5px;">Business Rules</h6>
                <ul>
                    <li>Always sorted: priority <strong>high → medium → low</strong>, then <code>due_date</code> ascending</li>
                    <li>Optional filter: <code>?status=pending</code>, <code>?status=in_progress</code>, <code>?status=done</code></li>
                    <li>Returns a meaningful message and empty array when no tasks exist</li>
                </ul>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted" style="font-size:.75rem;">Request</h6>
                        <pre style="background:#1e1e1e;color:#d4d4d4;padding:1rem;border-radius:8px;font-size:.8rem;overflow-x:auto;"># All tasks
curl {{ $base }}/api/tasks

# Filter by status
curl "{{ $base }}/api/tasks?status=pending"
curl "{{ $base }}/api/tasks?status=in_progress"
curl "{{ $base }}/api/tasks?status=done"</pre>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted" style="font-size:.75rem;">Response <span class="badge badge-success">200</span></h6>
                        <pre style="background:#1e1e1e;color:#d4d4d4;padding:1rem;border-radius:8px;font-size:.8rem;overflow-x:auto;">{
  "message": "Found 3 task(s).",
  "data": [
    {
      "id": 1,
      "title": "Deploy to prod",
      "priority": "high",
      "status": "pending",
      "due_date": "..."
    }
  ]
}

// When empty:
{
  "message": "No tasks found.",
  "data": []
}</pre>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Update Status --}}
        <div class="card mb-4" id="status">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <span class="badge badge-warning mr-2">PATCH</span>
                    <code>/api/tasks/{id}/status</code>
                    <small class="text-muted ml-2">Update Status</small>
                </h3>
            </div>
            <div class="card-body">
                <p>Advance a task's status by exactly one step forward.</p>

                <h6 class="text-uppercase text-muted" style="font-size:.75rem;letter-spacing:.5px;">Business Rules</h6>
                <ul>
                    <li>Only forward transitions are allowed</li>
                    <li><code>pending</code> → <code>in_progress</code> → <code>done</code></li>
                    <li>Cannot skip steps or revert to a previous status</li>
                    <li>Returns <code>422</code> with a clear message on illegal transition</li>
                </ul>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted" style="font-size:.75rem;">Request</h6>
                        <pre style="background:#1e1e1e;color:#d4d4d4;padding:1rem;border-radius:8px;font-size:.8rem;overflow-x:auto;"># pending → in_progress
curl -X PATCH {{ $base }}/api/tasks/1/status \
  -H "Content-Type: application/json" \
  -d '{"status": "in_progress"}'

# in_progress → done
curl -X PATCH {{ $base }}/api/tasks/1/status \
  -H "Content-Type: application/json" \
  -d '{"status": "done"}'</pre>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted" style="font-size:.75rem;">Response <span class="badge badge-success">200</span></h6>
                        <pre style="background:#1e1e1e;color:#d4d4d4;padding:1rem;border-radius:8px;font-size:.8rem;overflow-x:auto;">{
  "message": "Task status updated.",
  "data": {
    "id": 1,
    "status": "in_progress",
    ...
  }
}

// Illegal transition — 422:
{
  "message": "Cannot change status
  from 'pending' to 'done'. Only
  allowed next: 'in_progress'."
}</pre>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Delete --}}
        <div class="card mb-4" id="delete">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <span class="badge badge-danger mr-2">DELETE</span>
                    <code>/api/tasks/{id}</code>
                    <small class="text-muted ml-2">Delete Task</small>
                </h3>
            </div>
            <div class="card-body">
                <p>Permanently delete a task. Only tasks with status <code>done</code> can be deleted.</p>

                <h6 class="text-uppercase text-muted" style="font-size:.75rem;letter-spacing:.5px;">Business Rules</h6>
                <ul>
                    <li>Task must have status <code>done</code></li>
                    <li>Returns <code>403 Forbidden</code> for pending or in_progress tasks</li>
                    <li>Returns <code>404</code> if task ID does not exist</li>
                </ul>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted" style="font-size:.75rem;">Request</h6>
                        <pre style="background:#1e1e1e;color:#d4d4d4;padding:1rem;border-radius:8px;font-size:.8rem;overflow-x:auto;">curl -X DELETE {{ $base }}/api/tasks/1</pre>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted" style="font-size:.75rem;">Response <span class="badge badge-success">200</span></h6>
                        <pre style="background:#1e1e1e;color:#d4d4d4;padding:1rem;border-radius:8px;font-size:.8rem;overflow-x:auto;">{
  "message": "Task 'Deploy to prod'
  deleted successfully."
}

// If not done — 403:
{
  "message": "Task cannot be deleted
  because status is 'pending'. Only
  'done' tasks may be deleted."
}</pre>
                    </div>
                </div>
            </div>
        </div>

        {{-- 5. Report --}}
        <div class="card mb-4" id="report">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <span class="badge badge-info mr-2">GET</span>
                    <code>/api/tasks/report?date=YYYY-MM-DD</code>
                    <small class="text-muted ml-2">Daily Report (Bonus)</small>
                </h3>
            </div>
            <div class="card-body">
                <p>Returns task counts grouped by priority and status for a given date. All combinations are always present — zero-filled when empty.</p>

                <h6 class="text-uppercase text-muted" style="font-size:.75rem;letter-spacing:.5px;">Business Rules</h6>
                <ul>
                    <li>Date must be in <code>YYYY-MM-DD</code> format</li>
                    <li>Filters tasks by their <code>due_date</code> (not created_at)</li>
                    <li>All 9 priority × status combinations always appear in the response</li>
                </ul>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted" style="font-size:.75rem;">Request</h6>
                        <pre style="background:#1e1e1e;color:#d4d4d4;padding:1rem;border-radius:8px;font-size:.8rem;overflow-x:auto;">curl "{{ $base }}/api/tasks/report \
  ?date={{ today()->toDateString() }}"</pre>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted" style="font-size:.75rem;">Response <span class="badge badge-success">200</span></h6>
                        <pre style="background:#1e1e1e;color:#d4d4d4;padding:1rem;border-radius:8px;font-size:.8rem;overflow-x:auto;">{
  "date": "{{ today()->toDateString() }}",
  "summary": {
    "high": {
      "pending": 2,
      "in_progress": 1,
      "done": 0
    },
    "medium": {
      "pending": 1,
      "in_progress": 0,
      "done": 3
    },
    "low": {
      "pending": 0,
      "in_progress": 0,
      "done": 1
    }
  }
}</pre>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection