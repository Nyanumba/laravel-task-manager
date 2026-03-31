<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Task Manager') | TaskFlow</title>

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        body { font-family: 'Source Sans Pro', sans-serif; }
        .content-wrapper { background: #f4f6f9; }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.07); }
        .card-header { border-radius: 12px 12px 0 0 !important; }
        .main-sidebar { background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%) !important; }
        .brand-link { background: #1a1a2e !important; border-bottom: 1px solid rgba(255,255,255,0.1) !important; }
        .brand-text { color: #fff !important; font-weight: 700; letter-spacing: 1px; }
        .nav-sidebar .nav-link { color: #c2c7d0 !important; border-radius: 6px; margin: 2px 8px; }
        .nav-sidebar .nav-link.active { background: rgba(255,255,255,0.15) !important; color: #fff !important; }
        .nav-sidebar .nav-link:hover { background: rgba(255,255,255,0.08) !important; color: #fff !important; }
        .nav-sidebar .nav-link i { color: #7aa3e5 !important; }
        .main-header.navbar { background: #fff; box-shadow: 0 1px 8px rgba(0,0,0,0.07); }
        .table thead th {
            background: #f8f9fa;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
        }
        .badge { font-size: 0.78rem; padding: 0.35em 0.65em; border-radius: 6px; font-weight: 600; }
        #toast-container { position: fixed; top: 1rem; right: 1rem; z-index: 99999; }
        .toast-msg {
            min-width: 280px;
            padding: 0.85rem 1.2rem;
            border-radius: 10px;
            color: #fff;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
            animation: slideIn .3s ease;
        }
        @keyframes slideIn {
            from { transform: translateX(120%); opacity: 0; }
            to   { transform: translateX(0);    opacity: 1; }
        }
    </style>

    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    {{-- ===================== NAVBAR ===================== --}}
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('tasks.index') }}" class="nav-link font-weight-bold text-primary">
                    <i class="fas fa-tasks mr-1"></i> TaskManager
                </a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <span class="nav-link text-muted small">
                    <i class="far fa-clock mr-1"></i>
                    {{ now()->format('D, d M Y') }}
                </span>
            </li>
        </ul>
    </nav>

    {{-- ===================== SIDEBAR ===================== --}}
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('tasks.index') }}" class="brand-link">
            <span class="brand-image bg-primary d-inline-flex align-items-center justify-content-center rounded"
                  style="width:35px;height:35px;margin-left:8px;margin-right:8px;">
                <i class="fas fa-tasks text-white" style="font-size:1rem;"></i>
            </span>
            <span class="brand-text">TaskManager</span>
        </a>

        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column"
                    data-widget="treeview" role="menu" data-accordion="false">

                    <li class="nav-header" style="color:#7aa3e5;font-size:0.7rem;letter-spacing:1px;">
                        TASKS
                    </li>

                    <!-- All Tasks -->
                    <li class="nav-item">
                        <a href="{{ route('tasks.index') }}"
                           class="nav-link {{ request()->routeIs('tasks.index') && is_null(request('status')) ? 'active' : '' }}">
                            <i class="nav-icon fas fa-th-list"></i>
                            <p>All Tasks</p>
                        </a>
                    </li>

                    <!-- Pending -->
                    <li class="nav-item">
                        <a href="{{ route('tasks.index', ['status' => 'pending']) }}"
                           class="nav-link {{ request('status') === 'pending' ? 'active' : '' }}">
                            <i class="nav-icon fas fa-hourglass-start"></i>
                            <p>Pending</p>
                        </a>
                    </li>

                    <!-- In Progress -->
                    <li class="nav-item">
                        <a href="{{ route('tasks.index', ['status' => 'in_progress']) }}"
                           class="nav-link {{ request('status') === 'in_progress' ? 'active' : '' }}">
                            <i class="nav-icon fas fa-spinner"></i>
                            <p>In Progress</p>
                        </a>
                    </li>

                    <!-- Done -->
                    <li class="nav-item">
                        <a href="{{ route('tasks.index', ['status' => 'done']) }}"
                           class="nav-link {{ request('status') === 'done' ? 'active' : '' }}">
                            <i class="nav-icon fas fa-check-circle"></i>
                            <p>Done</p>
                        </a>
                    </li>

                    <li class="nav-header mt-2" style="color:#7aa3e5;font-size:0.7rem;letter-spacing:1px;">
                        REPORTS
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('tasks.report.view') }}"
                           class="nav-link {{ request()->routeIs('tasks.report.view') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-chart-bar"></i>
                            <p>Daily Report</p>
                        </a>
                    </li>

                    <li class="nav-header mt-2" style="color:#7aa3e5;font-size:0.7rem;letter-spacing:1px;">
                        DEVELOPER
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('api.docs') }}"
                           class="nav-link {{ request()->routeIs('api.docs') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-book-open"></i>
                            <p>API Docs</p>
                        </a>
                    </li>

                </ul>
            </nav>
        </div>
    </aside>

    {{-- ===================== CONTENT WRAPPER ===================== --}}
    <div class="content-wrapper">

        {{-- Page Header --}}
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0" style="font-size:1.4rem;font-weight:700;color:#2d3748;">
                            @yield('page-title', 'Dashboard')
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item">
                                <a href="{{ route('tasks.index') }}">Home</a>
                            </li>
                            @yield('breadcrumb')
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <section class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </section>

    </div>

    {{-- Footer --}}
    <footer class="main-footer">
        <strong>TaskManager</strong> — Laravel Task Management (Anthony Nyasente) &copy; {{ date('Y') }}
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0.0
        </div>
    </footer>

</div>

{{-- Toast container --}}
<div id="toast-container"></div>

{{-- ===================== SCRIPTS ===================== --}}

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Global CSRF token
const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Toast helper
function toast(msg, type = 'success') {
    const icons  = { success: 'fa-check-circle', error: 'fa-times-circle', info: 'fa-info-circle' };
    const colors = { success: '#28a745', error: '#dc3545', info: '#17a2b8' };

    const el = document.createElement('div');
    el.className = 'toast-msg';
    el.style.background = colors[type] || colors.info;
    el.innerHTML = `<i class="fas ${icons[type] || icons.info}"></i><span>${msg}</span>`;

    document.getElementById('toast-container').appendChild(el);
    setTimeout(() => el.remove(), 4000);
}

// Advance status
async function advanceStatus(taskId, nextStatus, btn) {
    const label = nextStatus.replace('_', ' ');

    const result = await Swal.fire({
        title: `Move to "${label}"?`,
        text: 'This action cannot be undone.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3a7bd5',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, advance it!',
        cancelButtonText: 'Cancel'
    });

    if (!result.isConfirmed) return;

    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    try {
        const res = await fetch(`/api/tasks/${taskId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: nextStatus }),
        });

        const data = await res.json();

        if (res.ok) {
            toast(data.message || 'Status updated successfully!', 'success');
            setTimeout(() => location.reload(), 900);
        } else {
            toast(data.message || 'Could not update status.', 'error');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    } catch (err) {
        toast('Network error. Please try again.', 'error');
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
}

// Delete task
async function deleteTask(taskId, taskTitle) {
    const result = await Swal.fire({
        title: 'Delete this task?',
        html: `<strong>${taskTitle}</strong> will be permanently removed.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    });

    if (!result.isConfirmed) return;

    try {
        const res = await fetch(`/api/tasks/${taskId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json'
            },
        });

        const data = await res.json();

        if (res.ok) {
            toast(data.message || 'Task deleted.', 'success');
            const row = document.getElementById(`task-row-${taskId}`);
            if (row) row.remove();
        } else {
            toast(data.message || 'Could not delete task.', 'error');
        }
    } catch (err) {
        toast('Network error. Please try again.', 'error');
    }
}
</script>

@stack('scripts')

</body>
</html>