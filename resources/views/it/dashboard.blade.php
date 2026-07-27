@extends('layouts.dashboard')

@section('content')
<style>
    :root {
        --it-primary: #0284c7;
        --it-primary-hover: #0369a1;
        --it-dark: #0f172a;
        --it-card-bg: #ffffff;
        --it-border: #e2e8f0;
        --it-text-main: #0f172a;
        --it-text-muted: #64748b;
        --shadow-premium: 0 20px 40px -15px rgba(15, 23, 42, 0.05), 0 0 0 1px rgba(15, 23, 42, 0.03);
    }

    .it-stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .it-stat-card {
        background: #ffffff;
        border: 1px solid var(--border-color);
        border-radius: 18px;
        padding: 1.5rem;
        box-shadow: var(--shadow-premium);
        position: relative;
        overflow: hidden;
        transition: transform 0.2s;
    }

    .it-stat-card:hover {
        transform: translateY(-2px);
    }

    .it-badge-severity {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 99px;
        font-size: 0.68rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .it-badge-critical {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.25);
    }

    .it-badge-warning {
        background: rgba(245, 158, 11, 0.1);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.25);
    }

    .it-badge-optimization {
        background: rgba(2, 132, 199, 0.1);
        color: #0284c7;
        border: 1px solid rgba(2, 132, 199, 0.25);
    }

    .it-table-container {
        background: #ffffff !important;
        border: 1px solid var(--border-color);
        border-radius: 20px;
        box-shadow: var(--shadow-premium);
        overflow: hidden;
    }

    .it-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        background: #ffffff !important;
    }

    .it-table th {
        padding: 1.1rem 1.25rem;
        font-size: 0.72rem;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        background: #ffffff !important;
        border-bottom: 1px solid var(--border-color);
    }

    .it-table td {
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.85rem;
        color: var(--text-main);
        vertical-align: middle;
        background: #ffffff !important;
    }

    .it-code-block {
        font-family: 'JetBrains Mono', 'Fira Code', 'Courier New', monospace;
        font-size: 0.78rem;
        background: #0f172a;
        color: #f8fafc;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        overflow-x: auto;
        line-height: 1.5;
    }

    .diff-old {
        background: rgba(239, 68, 68, 0.15) !important;
        color: #fca5a5 !important;
        border-left: 4px solid #ef4444;
        padding: 6px 12px;
        margin-bottom: 6px;
        font-family: monospace;
        border-radius: 6px;
        white-space: pre-wrap;
    }

    .diff-new {
        background: rgba(16, 185, 129, 0.15) !important;
        color: #6ee7b7 !important;
        border-left: 4px solid #10b981;
        padding: 6px 12px;
        font-family: monospace;
        border-radius: 6px;
        white-space: pre-wrap;
    }

    /* Modal Overlay */
    .it-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(8px);
        z-index: 100000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
    }

    .it-modal-overlay.active {
        display: flex;
    }

    .it-modal-card {
        background: #ffffff;
        border-radius: 20px;
        max-width: 720px;
        width: 100%;
        border: 1px solid var(--border-color);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        overflow: hidden;
        animation: modalFadeIn 0.25s ease;
    }

    @keyframes modalFadeIn {
        from { opacity: 0; transform: scale(0.96); }
        to { opacity: 1; transform: scale(1); }
    }
</style>

<div style="padding: 2rem;">
    {{-- Header Section --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; flex-wrap:wrap; gap:1.25rem;">
        <div>
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                <span class="it-badge-severity it-badge-optimization" style="font-size:0.65rem;">
                    <i data-lucide="shield-check" style="width:13px; height:13px;"></i> Strategic IT Operations Hub
                </span>
                <span style="font-size:0.75rem; color:var(--text-muted); font-weight:700;">
                    PHP {{ $diagnostics['php_version'] }} &bull; Laravel {{ $diagnostics['laravel_version'] }}
                </span>
            </div>
            <h1 style="font-size:1.75rem; font-weight:900; color:var(--text-main); margin:0; letter-spacing:-0.03em;">
                System Health, Performance &amp; Diagnostics
            </h1>
            <p style="font-size:0.88rem; color:var(--text-muted); font-weight:600; margin:4px 0 0;">
                Live telemetry monitoring, data transfer rates, predictive code issue detection, and line-specific remediation.
            </p>
        </div>

        <div style="display:flex; align-items:center; gap:0.75rem; flex-wrap:wrap;">
            <button onclick="runSystemScan()" id="btnScanNow" style="display:inline-flex; align-items:center; gap:8px; padding:0.65rem 1.25rem; background:#0284c7; color:white; border:none; border-radius:12px; font-weight:800; font-size:0.82rem; cursor:pointer; transition:all 0.2s; box-shadow:0 4px 12px rgba(2, 132, 199, 0.25);" onmouseover="this.style.background='#0369a1';" onmouseout="this.style.background='#0284c7';">
                <i data-lucide="cpu" style="width:16px; height:16px;" id="scanIcon"></i>
                <span>Run Diagnostic Scan</span>
            </button>
        </div>
    </div>

    {{-- Telemetry Metrics Grid --}}
    <div class="it-stat-grid">
        {{-- Card 1: System Health Score --}}
        <div class="it-stat-card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.75rem;">
                <span style="font-size:0.72rem; font-weight:800; text-transform:uppercase; color:var(--text-muted); letter-spacing:0.06em;">
                    System Health Index
                </span>
                <div style="width:36px; height:36px; border-radius:10px; background:rgba(2, 132, 199, 0.1); display:flex; align-items:center; justify-content:center; color:#0284c7;">
                    <i data-lucide="activity" style="width:18px; height:18px;"></i>
                </div>
            </div>
            <div style="font-size:2rem; font-weight:900; color:var(--text-main); letter-spacing:-0.03em;" id="statHealthScore">
                {{ $diagnostics['health_score'] }}%
            </div>
            <div style="margin-top:0.75rem; background:rgba(0,0,0,0.06); height:6px; border-radius:99px; overflow:hidden;">
                <div id="healthScoreBar" style="width: {{ $diagnostics['health_score'] }}%; height:100%; background: {{ $diagnostics['health_score'] >= 80 ? '#10b981' : ($diagnostics['health_score'] >= 60 ? '#f59e0b' : '#ef4444') }}; transition: width 0.5s ease;"></div>
            </div>
            <div style="font-size:0.72rem; color:var(--text-muted); font-weight:700; margin-top:0.5rem;" id="statHealthText">
                {{ $diagnostics['health_score'] >= 80 ? 'Optimal Performance State' : 'Minor Diagnostic Warnings' }}
            </div>
        </div>

        {{-- Card 2: Database Latency --}}
        <div class="it-stat-card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.75rem;">
                <span style="font-size:0.72rem; font-weight:800; text-transform:uppercase; color:var(--text-muted); letter-spacing:0.06em;">
                    DB Connection Latency
                </span>
                <div style="width:36px; height:36px; border-radius:10px; background:rgba(16, 185, 129, 0.1); display:flex; align-items:center; justify-content:center; color:#10b981;">
                    <i data-lucide="database" style="width:18px; height:18px;"></i>
                </div>
            </div>
            <div style="font-size:2rem; font-weight:900; color:var(--text-main); letter-spacing:-0.03em;" id="statDbLatency">
                {{ $diagnostics['db_latency_ms'] }} <span style="font-size:1.1rem; font-weight:700; color:var(--text-muted);">ms</span>
            </div>
            <div style="font-size:0.72rem; color:var(--text-muted); font-weight:700; margin-top:0.6rem;">
                Query Benchmark: <b id="statQueryBench">{{ $diagnostics['query_benchmark_ms'] }} ms</b> &bull; Status: <span style="color:#10b981; font-weight:800;">Connected</span>
            </div>
        </div>

        {{-- Card 3: Transfer Speed Rate --}}
        <div class="it-stat-card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.75rem;">
                <span style="font-size:0.72rem; font-weight:800; text-transform:uppercase; color:var(--text-muted); letter-spacing:0.06em;">
                    Transfer Rate Throughput
                </span>
                <div style="width:36px; height:36px; border-radius:10px; background:rgba(139, 92, 246, 0.1); display:flex; align-items:center; justify-content:center; color:#8b5cf6;">
                    <i data-lucide="zap" style="width:18px; height:18px;"></i>
                </div>
            </div>
            <div style="font-size:2rem; font-weight:900; color:var(--text-main); letter-spacing:-0.03em;" id="statTransferSpeed">
                {{ number_format($diagnostics['transfer_rate_kbps']) }} <span style="font-size:1.1rem; font-weight:700; color:var(--text-muted);">KB/s</span>
            </div>
            <div style="font-size:0.72rem; color:var(--text-muted); font-weight:700; margin-top:0.6rem;">
                Est. Data Rate: <b style="color:#8b5cf6;">{{ round($diagnostics['transfer_rate_kbps'] / 1024, 2) }} MB/s</b> &bull; High Efficiency
            </div>
        </div>

        {{-- Card 4: Memory & Storage --}}
        <div class="it-stat-card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.75rem;">
                <span style="font-size:0.72rem; font-weight:800; text-transform:uppercase; color:var(--text-muted); letter-spacing:0.06em;">
                    Memory &amp; Storage Footprint
                </span>
                <div style="width:36px; height:36px; border-radius:10px; background:rgba(245, 158, 11, 0.1); display:flex; align-items:center; justify-content:center; color:#f59e0b;">
                    <i data-lucide="hard-drive" style="width:18px; height:18px;"></i>
                </div>
            </div>
            <div style="font-size:1.75rem; font-weight:900; color:var(--text-main); letter-spacing:-0.03em;" id="statMemoryUsed">
                {{ $diagnostics['memory_used_mb'] }} MB <span style="font-size:0.85rem; font-weight:700; color:var(--text-muted);">/ {{ $diagnostics['memory_limit'] }}</span>
            </div>
            <div style="font-size:0.72rem; color:var(--text-muted); font-weight:700; margin-top:0.6rem;">
                Disk: <b>{{ $diagnostics['disk_used_gb'] }} GB</b> / {{ $diagnostics['disk_total_gb'] }} GB ({{ $diagnostics['disk_percent'] }}% used)
            </div>
        </div>
    </div>

    {{-- Predictive Issues & Line-Level Fixes Section --}}
    <div style="margin-bottom:1.5rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
        <div>
            <h2 style="font-size:1.2rem; font-weight:900; color:var(--text-main); margin:0; letter-spacing:-0.02em;">
                Predictive Issues &amp; Line-Specific Code Remediation
            </h2>
            <p style="font-size:0.82rem; color:var(--text-muted); font-weight:600; margin:2px 0 0;">
                Automated detection of potential performance bottlenecks, file growth hazards, and line-specific code remedies.
            </p>
        </div>
        <div>
            <span class="it-badge-severity it-badge-critical" style="font-size:0.75rem;" id="badgeTotalIssues">
                <i data-lucide="alert-triangle" style="width:14px; height:14px;"></i> {{ $diagnostics['total_issues_count'] }} Issue(s) Detected
            </span>
        </div>
    </div>

    <div class="it-table-container">
        <table class="it-table">
            <thead>
                <tr>
                    <th>Risk Level</th>
                    <th>Predictive Issue &amp; Description</th>
                    <th>Target File &amp; Line Number</th>
                    <th>Code Remediation Action</th>
                </tr>
            </thead>
            <tbody id="predictiveIssuesTbody">
                @forelse($diagnostics['predictive_issues'] as $issue)
                <tr>
                    <td>
                        <span class="it-badge-severity it-badge-{{ $issue['severity'] }}">
                            <i data-lucide="{{ $issue['severity'] === 'critical' ? 'alert-octagon' : ($issue['severity'] === 'warning' ? 'alert-triangle' : 'info') }}" style="width:13px; height:13px;"></i>
                            {{ $issue['severity_label'] }}
                        </span>
                    </td>
                    <td style="max-width:340px;">
                        <div style="font-weight:800; color:var(--text-main); font-size:0.88rem; margin-bottom:2px;">{{ $issue['title'] }}</div>
                        <div style="font-size:0.78rem; color:var(--text-muted); line-height:1.4;">{{ $issue['description'] }}</div>
                    </td>
                    <td>
                        <span style="display:inline-flex; align-items:center; gap:5px; font-family:monospace; font-size:0.8rem; font-weight:800; color:#0284c7; background:rgba(2, 132, 199, 0.08); padding:4px 8px; border-radius:6px; border:1px solid rgba(2, 132, 199, 0.2);" title="Project Path: {{ $issue['file_path'] }}">
                            <i data-lucide="file-code" style="width:13px; height:13px;"></i>
                            {{ basename($issue['file_path']) }}:L{{ $issue['line_number'] }}
                        </span>
                    </td>
                    <td>
                        <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                            <button onclick='openCodeFixModal(@json($issue))' style="padding:0.45rem 0.85rem; border-radius:8px; font-size:0.75rem; font-weight:800; background:rgba(2, 132, 199, 0.1); color:#0284c7; border:1px solid rgba(2, 132, 199, 0.25); cursor:pointer; display:flex; align-items:center; gap:4px; transition:all 0.2s;" onmouseover="this.style.background='#0284c7';this.style.color='white';" onmouseout="this.style.background='rgba(2, 132, 199, 0.1)';this.style.color='#0284c7';">
                                <i data-lucide="code" style="width:13px; height:13px;"></i> View Code Fix
                            </button>
                            @if(!empty($issue['can_autofix']))
                            <button onclick="applyAutomatedPatch('{{ $issue['id'] }}')" style="padding:0.45rem 0.85rem; border-radius:8px; font-size:0.75rem; font-weight:800; background:rgba(16, 185, 129, 0.1); color:#10b981; border:1px solid rgba(16, 185, 129, 0.25); cursor:pointer; display:flex; align-items:center; gap:4px; transition:all 0.2s;" onmouseover="this.style.background='#10b981';this.style.color='white';" onmouseout="this.style.background='rgba(16, 185, 129, 0.1)';this.style.color='#10b981';">
                                <i data-lucide="wrench" style="width:13px; height:13px;"></i> Apply Patch
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center; padding:2.5rem 1rem; color:var(--text-muted);">
                        <div style="display:flex; justify-content:center; margin-bottom:0.5rem; color:#10b981;"><i data-lucide="check-circle" style="width:36px; height:36px;"></i></div>
                        <div style="font-weight:800; font-size:0.9rem; color:var(--text-main);">No Critical Predictive Issues Detected</div>
                        <div style="font-size:0.78rem; margin-top:2px;">All database queries, log buffers, and system configurations are running at optimal parameters.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Interactive Line-Specific Code Fix Modal --}}
<div class="it-modal-overlay" id="codeFixModal">
    <div class="it-modal-card">
        <div style="padding:1.25rem 1.5rem; border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; background:rgba(2, 132, 199, 0.04);">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:32px; height:32px; border-radius:8px; background:rgba(2, 132, 199, 0.1); display:flex; align-items:center; justify-content:center; color:#0284c7;">
                    <i data-lucide="file-code" style="width:18px; height:18px;"></i>
                </div>
                <div>
                    <div style="font-size:0.95rem; font-weight:900; color:var(--text-main);" id="modalIssueTitle">Line Code Fix Remediation</div>
                    <div style="font-size:0.75rem; color:var(--text-muted); font-weight:700; font-family:monospace;" id="modalTargetFile">Target: -</div>
                </div>
            </div>
            <button onclick="closeCodeFixModal()" style="border:none; background:none; color:var(--text-muted); cursor:pointer; padding:4px;">
                <i data-lucide="x" style="width:20px; height:20px;"></i>
            </button>
        </div>

        <div style="padding:1.5rem;">
            <div style="font-size:0.82rem; color:var(--text-muted); margin-bottom:1rem; line-height:1.5;" id="modalIssueDescription">
                Remediation instructions for target file code fix.
            </div>

            {{-- Diff Preview --}}
            <div style="font-size:0.75rem; font-weight:800; text-transform:uppercase; color:var(--text-muted); letter-spacing:0.06em; margin-bottom:0.5rem;">
                Code Line Replacement Preview
            </div>

            <div style="background:#0f172a; padding:1rem; border-radius:12px; margin-bottom:1.5rem;">
                <div class="diff-old" id="modalDiffOld">Old Code</div>
                <div class="diff-new" id="modalDiffNew">New Fixed Code</div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
                <button onclick="closeCodeFixModal()" style="padding:0.55rem 1.1rem; border-radius:10px; font-weight:800; font-size:0.8rem; background:rgba(100,116,139,0.1); color:var(--text-muted); border:none; cursor:pointer;">
                    Close
                </button>
                <button id="modalBtnPatch" onclick="" style="padding:0.55rem 1.25rem; border-radius:10px; font-weight:800; font-size:0.8rem; background:#0284c7; color:white; border:none; cursor:pointer; display:flex; align-items:center; gap:6px;">
                    <i data-lucide="wrench" style="width:14px; height:14px;"></i> Apply Code Patch Now
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') lucide.createIcons();

        // Telemetry auto-refresh every 15 seconds
        setInterval(refreshTelemetryGauges, 15000);
    });

    async function refreshTelemetryGauges() {
        try {
            const res = await fetch('{{ route("it-hub.telemetry") }}');
            const data = await res.json();

            if (data.health_score !== undefined) {
                document.getElementById('statHealthScore').innerText = data.health_score + '%';
                const bar = document.getElementById('healthScoreBar');
                if (bar) {
                    bar.style.width = data.health_score + '%';
                    bar.style.background = data.health_score >= 80 ? '#10b981' : (data.health_score >= 60 ? '#f59e0b' : '#ef4444');
                }
            }
            if (data.db_latency_ms !== undefined) {
                document.getElementById('statDbLatency').innerHTML = `${data.db_latency_ms} <span style="font-size:1.1rem; font-weight:700; color:var(--text-muted);">ms</span>`;
            }
            if (data.transfer_speed_kbps !== undefined) {
                document.getElementById('statTransferSpeed').innerHTML = `${data.transfer_speed_kbps.toLocaleString()} <span style="font-size:1.1rem; font-weight:700; color:var(--text-muted);">KB/s</span>`;
            }
            if (data.memory_used_mb !== undefined) {
                document.getElementById('statMemoryUsed').innerHTML = `${data.memory_used_mb} MB <span style="font-size:0.85rem; font-weight:700; color:var(--text-muted);">/ ${data.memory_limit}</span>`;
            }
        } catch (e) {
            console.error('Telemetry refresh error:', e);
        }
    }

    async function runSystemScan() {
        const btn = document.getElementById('btnScanNow');
        const icon = document.getElementById('scanIcon');
        if (btn) btn.disabled = true;
        if (icon) icon.style.animation = 'spin 1s linear infinite';

        try {
            const res = await fetch('{{ route("it-hub.scan") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await res.json();
            if (data.success && data.diagnostics) {
                Swal.fire({
                    title: 'Diagnostic Scan Complete!',
                    text: `System Health Score: ${data.diagnostics.health_score}% | Latency: ${data.diagnostics.db_latency_ms}ms`,
                    icon: 'success',
                    timer: 2500,
                    showConfirmButton: false
                });
                window.location.reload();
            }
        } catch (e) {
            Swal.fire('Scan Error', 'Unable to complete diagnostic scan.', 'error');
        } finally {
            if (btn) btn.disabled = false;
            if (icon) icon.style.animation = 'none';
        }
    }

    function openCodeFixModal(issue) {
        document.getElementById('modalIssueTitle').innerText = issue.title;
        document.getElementById('modalTargetFile').innerText = `${issue.file_path}:L${issue.line_number}`;
        document.getElementById('modalIssueDescription').innerText = issue.description + ' ' + issue.recommended_fix;
        document.getElementById('modalDiffOld').innerText = issue.diff_old || issue.code_snippet;
        document.getElementById('modalDiffNew').innerText = issue.diff_new || issue.recommended_fix;

        const btnPatch = document.getElementById('modalBtnPatch');
        if (issue.can_autofix) {
            btnPatch.style.display = 'inline-flex';
            btnPatch.onclick = () => { closeCodeFixModal(); applyAutomatedPatch(issue.id); };
        } else {
            btnPatch.style.display = 'none';
        }

        document.getElementById('codeFixModal').classList.add('active');
    }

    function closeCodeFixModal() {
        document.getElementById('codeFixModal').classList.remove('active');
    }

    async function applyAutomatedPatch(issueId) {
        const confirm = await Swal.fire({
            title: 'Apply Automated Code Patch?',
            text: 'Are you sure you want to execute code patch remediation for this diagnostic issue?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0284c7',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Apply Patch',
            cancelButtonText: 'Cancel'
        });
        if (!confirm.isConfirmed) return;

        try {
            const res = await fetch('{{ route("it-hub.apply-patch") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ issue_id: issueId })
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire('Patch Applied!', data.message, 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                Swal.fire('Patch Failure', data.message, 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Network error while executing patch.', 'error');
        }
    }
</script>
@endpush
@endsection
