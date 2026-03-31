@extends('layouts.app')
@section('page-title') Task Manager @endsection

@section('content')

{{-- Stats --}}
<div class="row mb-3">
    @foreach([
        ['Total',       $tasks->count(),                          '#3a7bd5'],
        ['Pending',     $tasks->where('status','pending')->count(), '#667eea'],
        ['In Progress', $tasks->where('status','in_progress')->count(), '#f7971e'],
        ['Done',        $tasks->where('status','done')->count(),   '#11998e'],
    ] as [$label, $count, $color])
    <div class="col-6 col-md-3 mb-2">
        <div class="card text-white mb-0" style="background:{{ $color }};border-radius:12px;">
            <div class="card-body py-3">
                <div style="font-size:1.8rem;font-weight:700;">{{ $count }}</div>
                <div style="font-size:.85rem;">{{ $label }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Toolbar --}}
<div class="card mb-3">
    <div class="card-body py-2 d-flex flex-wrap align-items-center">
        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createTaskModal">
            <i class="fas fa-plus mr-1"></i> New Task
        </button>

        <div class="ml-auto d-flex" style="gap:.4rem;">
            <!-- All -->
            <a href="{{ route('tasks.index') }}"
               class="btn btn-sm {{ is_null(request('status')) ? 'btn-primary' : 'btn-outline-secondary' }}">
                All
            </a>

            <!-- Pending -->
            <a href="{{ route('tasks.index', ['status' => 'pending']) }}"
               class="btn btn-sm {{ request('status') === 'pending' ? 'btn-primary' : 'btn-outline-secondary' }}">
                Pending
            </a>

            <!-- In Progress -->
            <a href="{{ route('tasks.index', ['status' => 'in_progress']) }}"
               class="btn btn-sm {{ request('status') === 'in_progress' ? 'btn-primary' : 'btn-outline-secondary' }}">
                In Progress
            </a>

            <!-- Done -->
            <a href="{{ route('tasks.index', ['status' => 'done']) }}"
               class="btn btn-sm {{ request('status') === 'done' ? 'btn-primary' : 'btn-outline-secondary' }}">
                Done
            </a>
        </div>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-header"><h3 class="card-title mb-0">Tasks</h3></div>
    <div class="card-body p-0">
        @if($tasks->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-clipboard-list fa-3x mb-3 d-block"></i>
                <h5>No tasks found</h5>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>#</th><th>Title</th><th>Priority</th>
                        <th>Due Date</th><th>Status</th><th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($tasks as $i => $task)
                <tr id="task-row-{{ $task->id }}">
                    <td class="text-muted">{{ $i + 1 }}</td>
                    <td><strong>{{ $task->title }}</strong></td>
                    <td>
                        @php $pColors = ['high'=>'danger','medium'=>'warning','low'=>'success']; @endphp
                        <span class="badge badge-{{ $pColors[$task->priority] }}">
                            {{ ucfirst($task->priority) }}
                        </span>
                    </td>
                    <td>{{ $task->due_date->format('d M Y') }}</td>
                    <td>
                        @php $sColors = ['pending'=>'secondary','in_progress'=>'warning','done'=>'success']; @endphp
                        <span class="badge badge-{{ $sColors[$task->status] }}">
                            {{ ucwords(str_replace('_',' ',$task->status)) }}
                        </span>
                    </td>
                    <td class="text-right">
                        @php $next = $task->nextStatus(); @endphp
                        @if($next)
                        <button class="btn btn-sm btn-outline-primary"
                                onclick="advanceStatus({{ $task->id }},'{{ $next }}',this)">
                            → {{ ucwords(str_replace('_',' ',$next)) }}
                        </button>
                        @endif
                        @if($task->isDeletable())
                        <button class="btn btn-sm btn-outline-danger ml-1"
                                onclick="deleteTask({{ $task->id }},'{{ addslashes($task->title) }}')">
                            <i class="fas fa-trash"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- Create Modal --}}
<div class="modal fade" id="createTaskModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">New Task</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" id="f-title" class="form-control" placeholder="Task title">
                    <small class="text-danger d-none" id="e-title"></small>
                </div>
                <div class="form-group">
                    <label>Due Date *</label>
                    <input type="date" id="f-date" class="form-control"
                           min="{{ today()->toDateString() }}">
                    <small class="text-danger d-none" id="e-date"></small>
                </div>
                <div class="form-group">
                    <label>Priority *</label>
                    <select id="f-priority" class="form-control">
                        <option value="">Select…</option>
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                    <small class="text-danger d-none" id="e-priority"></small>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="btn-create">
                    <i class="fas fa-save mr-1"></i> Create
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('btn-create').addEventListener('click', async function () {
    ['title','date','priority'].forEach(f => {
        document.getElementById(`e-${f}`).classList.add('d-none');
    });

    const payload = {
        title:    document.getElementById('f-title').value.trim(),
        due_date: document.getElementById('f-date').value,
        priority: document.getElementById('f-priority').value,
    };

    if (!payload.title || !payload.due_date || !payload.priority) {
        toast('Please fill in all fields.', 'error'); return;
    }

    this.disabled = true;

    const res = await fetch('/api/tasks', {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json' },
        body: JSON.stringify(payload),
    });
    const data = await res.json();

    if (res.ok) {
        toast('Task created!', 'success');
        $('#createTaskModal').modal('hide');
        setTimeout(() => location.reload(), 800);
    } else if (data.errors) {
        const map = { title:'title', due_date:'date', priority:'priority' };
        for (const [k, msgs] of Object.entries(data.errors)) {
            const el = document.getElementById(`e-${map[k]}`);
            if (el) { el.textContent = msgs[0]; el.classList.remove('d-none'); }
        }
    } else {
        toast(data.message || 'Error creating task.', 'error');
    }
    this.disabled = false;
});

$('#createTaskModal').on('hidden.bs.modal', function () {
    document.getElementById('f-title').value = '';
    document.getElementById('f-date').value  = '';
    document.getElementById('f-priority').value = 'medium';
});
</script>
@endpush