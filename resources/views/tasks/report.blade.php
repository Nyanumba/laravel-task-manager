@extends('layouts.app')
@section('page-title') Daily Report @endsection

@section('content')
<div class="card mb-4">
    <div class="card-body d-flex align-items-center" style="gap:.75rem;">
        <label class="mb-0"><i class="far fa-calendar-alt mr-2"></i>Date:</label>
        <input type="date" id="report-date" class="form-control" style="max-width:200px;"
               value="{{ $date }}">
        <button class="btn btn-primary" id="load-btn">
            <i class="fas fa-search mr-1"></i> Load
        </button>
    </div>
</div>
<div id="report-output"></div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
let chart = null;

async function loadReport() {
    const date = document.getElementById('report-date').value;
    if (!date) return;

    const res  = await fetch(`/api/tasks/report?date=${date}`, {
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': CSRF }
    });
    const data = await res.json();
    if (!res.ok) { toast(data.message, 'error'); return; }

    const { summary } = data;
    const priorities  = ['high','medium','low'];
    const statuses    = ['pending','in_progress','done'];
    const priColors   = { high:'#dc3545', medium:'#ffc107', low:'#28a745' };

    const totalByStatus = {};
    statuses.forEach(s => {
        totalByStatus[s] = priorities.reduce((a,p) => a + (summary[p]?.[s] || 0), 0);
    });
    const grand = Object.values(totalByStatus).reduce((a,b) => a+b, 0);

    document.getElementById('report-output').innerHTML = `
    <div class="row mb-3">
        ${[['Total',grand,'#3a7bd5'],['Pending',totalByStatus.pending,'#667eea'],
           ['In Progress',totalByStatus.in_progress,'#f7971e'],['Done',totalByStatus.done,'#11998e']]
          .map(([l,c,col]) => `
          <div class="col-6 col-md-3 mb-2">
            <div class="card text-white mb-0" style="background:${col};border-radius:12px;">
              <div class="card-body py-3">
                <div style="font-size:1.8rem;font-weight:700;">${c}</div>
                <div style="font-size:.85rem;">${l}</div>
              </div>
            </div>
          </div>`).join('')}
    </div>
    <div class="row">
        <div class="col-md-7 mb-3">
            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0">Breakdown</h3></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead class="thead-light">
                            <tr><th>Priority</th><th>Pending</th><th>In Progress</th><th>Done</th><th>Total</th></tr>
                        </thead>
                        <tbody>
                            ${priorities.map(p => {
                                const row = statuses.map(s => summary[p]?.[s] || 0);
                                return `<tr>
                                    <td><strong>${p.charAt(0).toUpperCase()+p.slice(1)}</strong></td>
                                    ${row.map(n => `<td>${n}</td>`).join('')}
                                    <td><strong>${row.reduce((a,b)=>a+b,0)}</strong></td>
                                </tr>`;
                            }).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-5 mb-3">
            <div class="card"><div class="card-body">
                <canvas id="priority-chart" height="220"></canvas>
            </div></div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0">Raw JSON</h3></div>
                <div class="card-body p-0">
                    <pre style="background:#1e1e1e;color:#d4d4d4;padding:1rem;margin:0;border-radius:0 0 12px 12px;font-size:.82rem;">${JSON.stringify(data,null,2)}</pre>
                </div>
            </div>
        </div>
    </div>`;

    if (chart) chart.destroy();
    chart = new Chart(document.getElementById('priority-chart'), {
        type: 'doughnut',
        data: {
            labels: ['High','Medium','Low'],
            datasets: [{
                data: priorities.map(p => statuses.reduce((a,s) => a+(summary[p]?.[s]||0),0)),
                backgroundColor: priorities.map(p => priColors[p]),
                borderWidth: 2, borderColor: '#fff',
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
}

document.getElementById('load-btn').addEventListener('click', loadReport);
loadReport(); // auto-load on page open
</script>
@endpush