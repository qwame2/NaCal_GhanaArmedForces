@extends('layouts.dashboard')

@section('content')
<style>
    :root {
        --it-bg: #0f172a;
        --it-card-bg: #ffffff;
        --it-border: #e2e8f0;
        --it-primary: #2563eb;
        --it-success: #10b981;
        --it-warning: #f59e0b;
        --it-danger: #ef4444;
        --it-info: #06b6d4;
        --shadow-subtle: 0 10px 30px -10px rgba(15, 23, 42, 0.08);
        --shadow-glass: 0 20px 40px -15px rgba(0, 0, 0, 0.07), 0 0 0 1px rgba(15, 23, 42, 0.05);
    }

    .it-container {
        padding: 1.5rem 2rem;
        background: #f8fafc;
        min-height: 100vh;
    }

    /* Executive Grid */
    .it-exec-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .it-exec-card {
        background: #ffffff;
        border: 1px solid var(--it-border);
        border-radius: 16px;
        padding: 1rem;
        box-shadow: var(--shadow-subtle);
        text-align: center;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .it-exec-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-glass);
    }

    /* Circular SVG Gauge */
    .gauge-circle {
        transform: rotate(-90deg);
        transform-origin: 50% 50%;
    }

    /* Infrastructure Sparkline Grid */
    .it-infra-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .it-infra-card {
        background: #ffffff;
        border: 1px solid var(--it-border);
        border-radius: 16px;
        padding: 1.1rem;
        box-shadow: var(--shadow-subtle);
        position: relative;
        overflow: hidden;
    }

    .it-sparkline-bg {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 38px;
        opacity: 0.2;
        pointer-events: none;
    }

    /* Section Cards */
    .it-section-card {
        background: #ffffff;
        border: 1px solid var(--it-border);
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: var(--shadow-subtle);
        margin-bottom: 1.5rem;
    }

    /* Service Status Items */
    .service-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
    }

    .service-item-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    /* Standard Enterprise Table */
    .it-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .it-table th {
        padding: 0.85rem 1rem;
        font-size: 0.72rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    .it-table td {
        padding: 0.9rem 1rem;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.83rem;
        color: #0f172a;
        vertical-align: middle;
    }

    /* Floating AI Drawer */
    .ai-drawer-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(6px);
        z-index: 99999;
        display: none;
    }

    .ai-drawer {
        position: fixed;
        top: 0;
        right: -420px;
        width: 420px;
        height: 100vh;
        background: #ffffff;
        box-shadow: -10px 0 30px rgba(0, 0, 0, 0.15);
        z-index: 100000;
        transition: right 0.3s ease;
        display: flex;
        flex-direction: column;
    }

    .ai-drawer.open {
        right: 0;
    }

    /* Code Fix Modal */
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
        max-width: 760px;
        width: 100%;
        border: 1px solid var(--it-border);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        overflow: hidden;
    }
</style>

<div class="it-container">
    {{-- TOP COMMAND BAR --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
        <div>
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:4px;">
                <span style="display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:99px; background:rgba(37, 99, 235, 0.1); color:#2563eb; font-size:0.7rem; font-weight:900; text-transform:uppercase;">
                    <i data-lucide="shield-check" style="width:14px;"></i> System Observability &amp; Diagnostics
                </span>
                <span style="font-size:0.75rem; color:#64748b; font-weight:700;">
                    PHP {{ $diagnostics['php_version'] }} &bull; Laravel {{ $diagnostics['laravel_version'] }}
                </span>
            </div>
            <h1 style="font-size:1.6rem; font-weight:900; color:#0f172a; margin:0; letter-spacing:-0.03em; display:flex; align-items:center; gap:10px;">
                IT Command Center &amp; Diagnostics Suite

            </h1>
        </div>

        {{-- Enterprise Quick Actions Toolbar --}}
        <div style="display:flex; align-items:center; gap:0.65rem; flex-wrap:wrap;">
            <button onclick="runSystemScan()" id="btnScanNow" style="display:inline-flex; align-items:center; gap:8px; padding:0.6rem 1.1rem; background:#0284c7; color:white; border:none; border-radius:12px; font-weight:800; font-size:0.8rem; cursor:pointer; box-shadow:0 4px 12px rgba(2, 132, 199, 0.25);">
                <i data-lucide="cpu" style="width:15px; height:15px;" id="scanIcon"></i>
                <span>Run Diagnostic Scan</span>
            </button>

            <button onclick="triggerStoragePurge()" style="display:inline-flex; align-items:center; gap:6px; padding:0.6rem 1.1rem; background:rgba(245, 158, 11, 0.1); color:#d97706; border:1px solid rgba(245, 158, 11, 0.25); border-radius:12px; font-weight:800; font-size:0.8rem; cursor:pointer;">
                <i data-lucide="trash-2" style="width:15px; height:15px;"></i>
                <span>Storage Janitor Purge</span>
            </button>
        </div>
    </div>

    {{-- EXECUTIVE SUMMARY & SYSTEM HEALTH OVERVIEW --}}
    <div style="margin-bottom:0.6rem; font-size:0.75rem; font-weight:900; text-transform:uppercase; color:#64748b; letter-spacing:0.06em;">
        Executive System Health Overview &amp; Systems Index
    </div>

    <div class="it-exec-grid">
        {{-- Overall Health --}}
        <div class="it-exec-card" style="border-top: 4px solid #2563eb;">
            <div style="position:relative; width:64px; height:64px; margin:0 auto 0.5rem;">
                <svg width="64" height="64" viewBox="0 0 36 36">
                    <path class="gauge-circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#e2e8f0" stroke-width="3.5" />
                    <path class="gauge-circle" stroke-dasharray="{{ $diagnostics['health_score'] }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#2563eb" stroke-width="3.5" stroke-linecap="round" />
                </svg>
                <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:1.05rem; color:#0f172a;" id="statHealthScore">
                    {{ $diagnostics['health_score'] }}
                </div>
            </div>
            <div style="font-size:0.75rem; font-weight:800; color:#0f172a;">Overall System</div>
            <div style="font-size:0.68rem; color:#64748b; font-weight:700;">Health Index</div>
        </div>

        {{-- Performance --}}
        <div class="it-exec-card" style="border-top: 4px solid #10b981;">
            <div style="font-size:1.8rem; font-weight:900; color:#10b981; margin:0.5rem 0;" id="scorePerformance">{{ $diagnostics['performance_score'] }}%</div>
            <div style="font-size:0.75rem; font-weight:800; color:#0f172a;">Performance</div>
            <div style="font-size:0.68rem; color:#10b981; font-weight:800;" id="labelPerformance">Optimal</div>
        </div>

        {{-- Security --}}
        <div class="it-exec-card" style="border-top: 4px solid #3b82f6;">
            <div style="font-size:1.8rem; font-weight:900; color:#3b82f6; margin:0.5rem 0;" id="scoreSecurity">{{ $diagnostics['security_score'] }}%</div>
            <div style="font-size:0.75rem; font-weight:800; color:#0f172a;">Security</div>
            <div style="font-size:0.68rem; color:#3b82f6; font-weight:800;" id="labelSecurity">Protected</div>
        </div>

        {{-- Database --}}
        <div class="it-exec-card" style="border-top: 4px solid #06b6d4;">
            <div style="font-size:1.8rem; font-weight:900; color:#06b6d4; margin:0.5rem 0;" id="scoreDatabase">{{ $diagnostics['database_score'] }}%</div>
            <div style="font-size:0.75rem; font-weight:800; color:#0f172a;">Database</div>
            <div style="font-size:0.68rem; color:#06b6d4; font-weight:800;" id="labelDatabase">Sub-10ms</div>
        </div>

        {{-- Storage --}}
        <div class="it-exec-card" style="border-top: 4px solid #f59e0b;">
            <div style="font-size:1.8rem; font-weight:900; color:#f59e0b; margin:0.5rem 0;" id="scoreStorage">{{ $diagnostics['storage_score'] }}%</div>
            <div style="font-size:0.75rem; font-weight:800; color:#0f172a;">Storage</div>
            <div style="font-size:0.68rem; color:#f59e0b; font-weight:800;" id="labelStorage">Healthy</div>
        </div>

        {{-- Application --}}
        <div class="it-exec-card" style="border-top: 4px solid #8b5cf6;">
            <div style="font-size:1.8rem; font-weight:900; color:#8b5cf6; margin:0.5rem 0;" id="scoreApplication">{{ $diagnostics['application_score'] }}%</div>
            <div style="font-size:0.75rem; font-weight:800; color:#0f172a;">Application</div>
            <div style="font-size:0.68rem; color:#8b5cf6; font-weight:800;" id="labelApplication">v11.x Core</div>
        </div>

        {{-- Network --}}
        <div class="it-exec-card" style="border-top: 4px solid #10b981;">
            <div style="font-size:1.8rem; font-weight:900; color:#10b981; margin:0.5rem 0;" id="scoreNetwork">{{ $diagnostics['network_score'] }}%</div>
            <div style="font-size:0.75rem; font-weight:800; color:#0f172a;">Network</div>
            <div style="font-size:0.68rem; color:#10b981; font-weight:800;" id="labelNetwork">100% Active</div>
        </div>
    </div>

    {{-- System Infrastructure Advisory Banner (Only display when log size > 2MB, high disk, or issues detected) --}}
    @if(($diagnostics['log_size_mb'] ?? 0) > 2.0 || ($diagnostics['disk_percent'] ?? 0) >= 85 || !empty($diagnostics['predictive_issues']))
    <div style="background:linear-gradient(135deg, rgba(37, 99, 235, 0.08) 0%, rgba(16, 185, 129, 0.08) 100%); border:1px solid rgba(37, 99, 235, 0.2); border-radius:16px; padding:1rem 1.25rem; margin-bottom:1.5rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="width:38px; height:38px; border-radius:12px; background:#2563eb; color:white; display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow:0 4px 12px rgba(37, 99, 235, 0.3);">
                <i data-lucide="shield-alert" style="width:20px; height:20px;"></i>
            </div>
            <div>
                <div style="font-size:0.88rem; font-weight:900; color:#0f172a;">System Infrastructure Advisory</div>
                <div style="font-size:0.8rem; color:#475569; font-weight:600;">
                    Log buffer size is {{ $diagnostics['log_size_mb'] }} MB. Storage Janitor Purge recommended to clear log cache and maintain optimal disk performance.
                </div>
            </div>
        </div>
        <button onclick="triggerStoragePurge()" style="padding:0.45rem 0.9rem; border-radius:8px; font-size:0.75rem; font-weight:800; background:#2563eb; color:white; border:none; cursor:pointer;">
            Run Storage Janitor
        </button>
    </div>
    @endif

    {{-- INFRASTRUCTURE TELEMETRY SPARKLINE GRID --}}
    <div style="margin-bottom:0.6rem; font-size:0.75rem; font-weight:900; text-transform:uppercase; color:#64748b; letter-spacing:0.06em;">
        Real-Time Infrastructure Monitoring Telemetry
    </div>

    <div class="it-infra-grid">
        {{-- Card 1: CPU --}}
        <div class="it-infra-card" style="border-top:3px solid #2563eb;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.4rem;">
                <span style="font-size:0.72rem; font-weight:800; color:#64748b; text-transform:uppercase;">CPU Load</span>
                <span style="font-size:0.7rem; font-weight:800; color:#10b981;" id="statCpuTrend">&darr; 2% prev</span>
            </div>
            <div style="font-size:1.6rem; font-weight:900; color:#0f172a;" id="statCpuLoad">{{ $diagnostics['cpu_usage'] ?? '18' }}%</div>
            <div style="font-size:0.7rem; color:#64748b; font-weight:700;" id="statCpuSpec">{!! $diagnostics['cpu_spec'] ?? 'Processor Engine &bull; Live Telemetry' !!}</div>
            <svg class="it-sparkline-bg" viewBox="0 0 100 25" preserveAspectRatio="none"><path d="M0 20 Q 25 5, 50 18 T 100 8 L 100 25 L 0 25 Z" fill="#2563eb" /></svg>
        </div>

        {{-- Card 3: System Speed Rate --}}
        <div class="it-infra-card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.4rem;">
                <span style="font-size:0.72rem; font-weight:800; color:#64748b; text-transform:uppercase;">Speed Rate</span>
                <span style="font-size:0.7rem; font-weight:800; color:#10b981;">Ultra Fast</span>
            </div>
            <div style="font-size:1.6rem; font-weight:900; color:#0f172a;" id="statSystemSpeedRate">{{ $diagnostics['system_speed_mbps'] }} <span style="font-size:0.9rem; font-weight:700; color:#64748b;">MB/s</span></div>
            <div style="font-size:0.7rem; color:#64748b; font-weight:700;">Exec Speed: <b style="color:#10b981;" id="statExecTime">{{ $diagnostics['execution_speed_ms'] }} ms</b></div>
            <svg class="it-sparkline-bg" viewBox="0 0 100 25" preserveAspectRatio="none"><path d="M0 18 Q 20 8, 50 12 T 100 5 L 100 25 L 0 25 Z" fill="#06b6d4" /></svg>
        </div>

        {{-- Card 4: DB Latency --}}
        <div class="it-infra-card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.4rem;">
                <span style="font-size:0.72rem; font-weight:800; color:#64748b; text-transform:uppercase;">DB Latency</span>
                <span style="font-size:0.7rem; font-weight:800; color:#10b981;">Sub-5ms</span>
            </div>
            <div style="font-size:1.6rem; font-weight:900; color:#0f172a;" id="statDbLatency">{{ $diagnostics['db_latency_ms'] }} <span style="font-size:0.9rem; font-weight:700; color:#64748b;">ms</span></div>
            <div style="font-size:0.7rem; color:#64748b; font-weight:700;">Benchmark: {{ $diagnostics['query_benchmark_ms'] }} ms</div>
            <svg class="it-sparkline-bg" viewBox="0 0 100 25" preserveAspectRatio="none"><path d="M0 22 Q 35 12, 70 18 T 100 10 L 100 25 L 0 25 Z" fill="#8b5cf6" /></svg>
        </div>

        {{-- Card 5: Disk & App Storage Space --}}
        <div class="it-infra-card" style="border-top:3px solid #f59e0b;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.4rem;">
                <span style="font-size:0.72rem; font-weight:800; color:#64748b; text-transform:uppercase;">App Space &amp; Disk</span>
                <span style="font-size:0.68rem; font-weight:800; color:#ef4444; display:inline-flex; align-items:center; gap:3px;" id="statDiskGrowth">
                    <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:#ef4444; animation:pulse 1s infinite;"></span>
                    <span id="statGrowthText">{{ $diagnostics['disk_growth_rate'] ?? '+0.4 MB/min' }} (Increasing)</span>
                </span>
            </div>
            <div style="font-size:1.5rem; font-weight:900; color:#0f172a;" id="statAppSpace">
                {{ $diagnostics['app_space_mb'] ?? '48.6' }} <span style="font-size:0.85rem; font-weight:700; color:#64748b;">MB App Footprint</span>
            </div>
            <div style="font-size:0.7rem; color:#64748b; font-weight:700;" id="statDiskUsed">
                Disk Total: <b>{{ $diagnostics['disk_used_gb'] }} GB</b> / {{ $diagnostics['disk_total_gb'] }} GB ({{ $diagnostics['disk_percent'] }}% used)
            </div>
            <svg class="it-sparkline-bg" viewBox="0 0 100 25" preserveAspectRatio="none"><path d="M0 10 Q 25 15, 50 8 T 100 14 L 100 25 L 0 25 Z" fill="#f59e0b" /></svg>
        </div>
    </div>



    {{-- SERVICE STATUS PANEL & MAINTENANCE CONTROL CENTER --}}
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap:1.5rem; margin-bottom:1.5rem;">
        {{-- Services Panel --}}
        <div class="it-section-card" style="margin-bottom:0;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h3 style="font-size:1.05rem; font-weight:900; color:#0f172a; margin:0; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="activity" style="width:18px; color:#2563eb;"></i> Critical Enterprise Services
                </h3>
                <span id="monitoredServicesCount" style="font-size:0.7rem; font-weight:800; color:#10b981; background:rgba(16, 185, 129, 0.1); padding:3px 8px; border-radius:99px;">{{ count($diagnostics['services']) }} Services Online</span>
            </div>

            <div class="service-grid">
                @foreach($diagnostics['services'] as $svc)
                @php
                    $svcSlug = Str::slug($svc['name']);
                    $badgeColor = '#10b981';
                    $badgeBg = 'rgba(16, 185, 129, 0.1)';
                    if ($svc['status'] === 'Offline' || ($svc['badge'] ?? '') === 'danger') {
                        $badgeColor = '#ef4444';
                        $badgeBg = 'rgba(239, 68, 68, 0.1)';
                    } elseif ($svc['status'] === 'Degraded' || ($svc['badge'] ?? '') === 'warning') {
                        $badgeColor = '#f59e0b';
                        $badgeBg = 'rgba(245, 158, 11, 0.1)';
                    }
                @endphp
                <div class="service-item-card">
                    <div>
                        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                            <span style="font-weight:900; font-size:0.83rem; color:#0f172a;">{{ $svc['name'] }}</span>
                            <span id="service-status-{{ $svcSlug }}" data-slug="{{ $svcSlug }}" class="service-badge" style="font-size:0.65rem; font-weight:900; padding:2px 6px; border-radius:99px; color: {{ $badgeColor }}; background: {{ $badgeBg }}; transition: all 0.3s ease;">
                                {{ $svc['status'] }}
                            </span>
                        </div>
                        <div style="font-size:0.7rem; color:#64748b; font-weight:600; margin-top:2px;">Target: {{ $svc['port'] }} &bull; Uptime: {{ $svc['uptime'] }}</div>
                    </div>
                    <div style="display:flex; gap:6px; margin-top:0.75rem;">
                        <button onclick="triggerServiceAction('{{ $svc['name'] }}', 'restart')" style="padding:3px 8px; border-radius:6px; font-size:0.68rem; font-weight:800; background:rgba(37, 99, 235, 0.1); color:#2563eb; border:none; cursor:pointer;">Restart</button>
                        <button onclick="triggerServiceAction('{{ $svc['name'] }}', 'reload')" style="padding:3px 8px; border-radius:6px; font-size:0.68rem; font-weight:800; background:rgba(100, 116, 139, 0.1); color:#475569; border:none; cursor:pointer;">Reload</button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Maintenance Command Center --}}
        <div class="it-section-card" style="margin-bottom:0;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h3 style="font-size:1.05rem; font-weight:900; color:#0f172a; margin:0; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="wrench" style="width:18px; color:#d97706;"></i> Maintenance Command Center
                </h3>
                <span style="font-size:0.7rem; font-weight:800; color:#64748b;">Instant Dispatch</span>
            </div>

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap:0.65rem;">
                <button onclick="executeMaintenance('optimize_laravel')" style="padding:0.6rem; border-radius:10px; font-size:0.72rem; font-weight:800; background:#f1f5f9; color:#0f172a; border:1px solid #cbd5e1; cursor:pointer; text-align:left;">
                    <i data-lucide="zap" style="width:13px; color:#2563eb; margin-bottom:4px;"></i><br>Optimize Laravel
                </button>
                <button onclick="executeMaintenance('clear_cache')" style="padding:0.6rem; border-radius:10px; font-size:0.72rem; font-weight:800; background:#f1f5f9; color:#0f172a; border:1px solid #cbd5e1; cursor:pointer; text-align:left;">
                    <i data-lucide="trash-2" style="width:13px; color:#d97706; margin-bottom:4px;"></i><br>Clear Cache
                </button>
                <button onclick="executeMaintenance('route_cache')" style="padding:0.6rem; border-radius:10px; font-size:0.72rem; font-weight:800; background:#f1f5f9; color:#0f172a; border:1px solid #cbd5e1; cursor:pointer; text-align:left;">
                    <i data-lucide="route" style="width:13px; color:#10b981; margin-bottom:4px;"></i><br>Route Cache
                </button>
                <button onclick="executeMaintenance('view_cache')" style="padding:0.6rem; border-radius:10px; font-size:0.72rem; font-weight:800; background:#f1f5f9; color:#0f172a; border:1px solid #cbd5e1; cursor:pointer; text-align:left;">
                    <i data-lucide="eye" style="width:13px; color:#06b6d4; margin-bottom:4px;"></i><br>View Cache
                </button>
                <button onclick="executeMaintenance('config_cache')" style="padding:0.6rem; border-radius:10px; font-size:0.72rem; font-weight:800; background:#f1f5f9; color:#0f172a; border:1px solid #cbd5e1; cursor:pointer; text-align:left;">
                    <i data-lucide="sliders" style="width:13px; color:#8b5cf6; margin-bottom:4px;"></i><br>Config Cache
                </button>
                <button onclick="executeMaintenance('restart_queue')" style="padding:0.6rem; border-radius:10px; font-size:0.72rem; font-weight:800; background:#f1f5f9; color:#0f172a; border:1px solid #cbd5e1; cursor:pointer; text-align:left;">
                    <i data-lucide="refresh-cw" style="width:13px; color:#2563eb; margin-bottom:4px;"></i><br>Restart Queue
                </button>
                <button onclick="executeMaintenance('storage_link')" style="padding:0.6rem; border-radius:10px; font-size:0.72rem; font-weight:800; background:#f1f5f9; color:#0f172a; border:1px solid #cbd5e1; cursor:pointer; text-align:left;">
                    <i data-lucide="link" style="width:13px; color:#10b981; margin-bottom:4px;"></i><br>Storage Link
                </button>
                <button onclick="executeMaintenance('clear_logs')" style="padding:0.6rem; border-radius:10px; font-size:0.72rem; font-weight:800; background:#f1f5f9; color:#0f172a; border:1px solid #cbd5e1; cursor:pointer; text-align:left;">
                    <i data-lucide="file-text" style="width:13px; color:#ef4444; margin-bottom:4px;"></i><br>Clear Logs
                </button>
                <button onclick="executeMaintenance('git_pull')" style="padding:0.6rem; border-radius:10px; font-size:0.72rem; font-weight:800; background:#f1f5f9; color:#0f172a; border:1px solid #cbd5e1; cursor:pointer; text-align:left;">
                    <i data-lucide="git-pull-request" style="width:13px; color:#7c3aed; margin-bottom:4px;"></i><br>Git Pull
                </button>
                <button onclick="executeMaintenance('artisan_migrate')" style="padding:0.6rem; border-radius:10px; font-size:0.72rem; font-weight:800; background:#f1f5f9; color:#0f172a; border:1px solid #cbd5e1; cursor:pointer; text-align:left;">
                    <i data-lucide="database" style="width:13px; color:#0891b2; margin-bottom:4px;"></i><br>Artisan Migrate
                </button>
            </div>
        </div>
    </div>

    {{-- SECURITY OPERATIONS CENTER (SOC) & ACTIVE CONNECTED USERS --}}
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap:1.5rem; margin-bottom:1.5rem;">
        {{-- SOC Threat Matrix --}}
        <div class="it-section-card" style="margin-bottom:0;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h3 style="font-size:1.05rem; font-weight:900; color:#0f172a; margin:0; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="shield-alert" style="width:18px; color:#3b82f6;"></i> SOC Security Defense Telemetry
                </h3>
                <span style="font-size:0.7rem; font-weight:900; color:#10b981; background:rgba(16, 185, 129, 0.1); padding:3px 8px; border-radius:99px;">
                    RISK: {{ $diagnostics['soc_data']['risk_level'] }}
                </span>
            </div>

            <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:0.75rem; margin-bottom:1rem; text-align:center;">
                <div style="background:#f8fafc; padding:0.75rem; border-radius:12px; border:1px solid #e2e8f0;">
                    <div style="font-size:1.3rem; font-weight:900; color:#10b981;">{{ $diagnostics['soc_data']['login_successful'] }}</div>
                    <div style="font-size:0.68rem; font-weight:800; color:#64748b;">Successful Logins</div>
                </div>
                <div style="background:#f8fafc; padding:0.75rem; border-radius:12px; border:1px solid #e2e8f0;">
                    <div style="font-size:1.3rem; font-weight:900; color:#f59e0b;">{{ $diagnostics['soc_data']['login_failed'] }}</div>
                    <div style="font-size:0.68rem; font-weight:800; color:#64748b;">Failed Attempts</div>
                </div>
                <div style="background:#f8fafc; padding:0.75rem; border-radius:12px; border:1px solid #e2e8f0;">
                    <div style="font-size:1.3rem; font-weight:900; color:#ef4444;">{{ $diagnostics['soc_data']['brute_force_attempts'] }}</div>
                    <div style="font-size:0.68rem; font-weight:800; color:#64748b;">Brute Force Blocked</div>
                </div>
            </div>

            <div style="font-size:0.78rem; font-weight:800; color:#0f172a; margin-bottom:0.4rem;">Geographic IP Monitoring</div>
            @foreach($diagnostics['soc_data']['suspicious_ips'] as $ipInfo)
            <div style="display:flex; justify-content:space-between; align-items:center; padding:0.45rem 0.75rem; background:#f8fafc; border-radius:8px; margin-bottom:0.4rem; font-size:0.75rem;">
                <span style="font-family:monospace; font-weight:800;">{{ $ipInfo['flag'] }} {{ $ipInfo['ip'] }} ({{ $ipInfo['country'] }})</span>
                <span style="color:#10b981; font-weight:800;">{{ $ipInfo['status'] }}</span>
            </div>
            @endforeach
        </div>

        {{-- Connected Users --}}
        <div class="it-section-card" style="margin-bottom:0;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h3 style="font-size:1.05rem; font-weight:900; color:#0f172a; margin:0; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="users" style="width:18px; color:#2563eb;"></i> Connected User Sessions
                </h3>
                <span style="font-size:0.7rem; font-weight:800; color:#2563eb; background:rgba(37, 99, 235, 0.1); padding:3px 8px; border-radius:99px;">{{ count($diagnostics['connected_users']) }} Active</span>
            </div>

            <div style="max-height:220px; overflow-y:auto;">
                <table class="it-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>IP Address</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($diagnostics['connected_users'] as $uSess)
                        <tr>
                            <td>
                                <div style="font-weight:800; color:#0f172a;">{{ $uSess['name'] }}</div>
                                <div style="font-size:0.68rem; color:#64748b;">{{ $uSess['department'] }}</div>
                            </td>
                            <td><span style="font-size:0.68rem; font-weight:800; padding:2px 6px; border-radius:99px; background:rgba(37, 99, 235, 0.1); color:#2563eb;">{{ $uSess['role'] }}</span></td>
                            <td style="font-family:monospace; font-weight:700; font-size:0.75rem;">{{ $uSess['ip_address'] }}</td>
                            <td>
                                <button onclick="killSession('{{ $uSess['session_id'] }}', {{ $uSess['user_id'] }}, '{{ $uSess['username'] }}')" style="padding:3px 6px; border-radius:4px; font-size:0.65rem; font-weight:800; background:rgba(239, 68, 68, 0.1); color:#ef4444; border:none; cursor:pointer;">
                                    Kill
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" style="text-align:center; padding:1rem; color:#64748b;">No active online user sessions registered.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- PREDICTIVE CODE REMEDIATION SECTION --}}
    <div class="it-section-card">
        <div style="margin-bottom:1rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
            <div>
                <h3 style="font-size:1.05rem; font-weight:900; color:#0f172a; margin:0; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="shield-alert" style="width:18px; color:#2563eb;"></i> Predictive Code Remediation Engine
                </h3>
                <p style="font-size:0.78rem; color:#64748b; font-weight:600; margin:2px 0 0;">Line-specific automated patch remedies and code optimization fixes.</p>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="it-table">
                <thead>
                    <tr>
                        <th>Risk Level</th>
                        <th>Predictive Issue &amp; Target File</th>
                        <th>Gain &amp; Risk Score</th>
                        <th>Remediation Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($diagnostics['predictive_issues'] as $issue)
                    <tr>
                        <td>
                            <span style="font-size:0.68rem; font-weight:900; text-transform:uppercase; padding:3px 8px; border-radius:99px; background:rgba(245, 158, 11, 0.1); color:#d97706;">
                                {{ $issue['severity_label'] }}
                            </span>
                        </td>
                        <td style="max-width:340px;">
                            <div style="font-weight:800; color:#0f172a; font-size:0.85rem;">{{ $issue['title'] }}</div>
                            <div style="font-size:0.75rem; color:#64748b; margin-top:2px;">{{ $issue['description'] }}</div>
                            <div style="font-family:monospace; font-size:0.72rem; color:#2563eb; font-weight:700; margin-top:4px;">{{ basename($issue['file_path']) }}:L{{ $issue['line_number'] }}</div>
                        </td>
                        <td>
                            <div style="font-weight:800; color:#10b981; font-size:0.78rem;">{{ $issue['performance_gain'] ?? '+25% Gain' }}</div>
                            <div style="font-size:0.7rem; color:#64748b;">Risk Score: {{ $issue['risk_score'] ?? 10 }}/100</div>
                        </td>
                        <td>
                            <div style="display:flex; gap:6px;">
                                <button onclick='openCodeFixModal(@json($issue))' style="padding:0.4rem 0.75rem; border-radius:8px; font-size:0.72rem; font-weight:800; background:rgba(37, 99, 235, 0.1); color:#2563eb; border:none; cursor:pointer;">
                                    View Code Fix
                                </button>
                                @if(!empty($issue['can_autofix']))
                                <button onclick="applyAutomatedPatch('{{ $issue['id'] }}')" style="padding:0.4rem 0.75rem; border-radius:8px; font-size:0.72rem; font-weight:800; background:rgba(16, 185, 129, 0.1); color:#10b981; border:none; cursor:pointer;">
                                    Apply Patch
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align:center; padding:2rem 1rem; color:#64748b;">
                            <div style="font-weight:800; color:#10b981;">No Critical Predictive Code Issues</div>
                            <div style="font-size:0.78rem;">All code paths, migration indexes, and log rotation parameters are operating at peak efficiency.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- DATABASE INTELLIGENCE & APPLICATION DIAGNOSTICS --}}
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap:1.5rem; margin-bottom:1.5rem;">
        {{-- Database Intelligence --}}
        <div class="it-section-card" style="margin-bottom:0;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h3 style="font-size:1.05rem; font-weight:900; color:#0f172a; margin:0; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="database" style="width:18px; color:#06b6d4;"></i> Database Intelligence Center
                </h3>
                <span style="font-size:0.7rem; font-weight:800; color:#06b6d4;">{{ $diagnostics['db_intelligence']['size_mb'] }} MB Size</span>
            </div>

            <div style="display:grid; grid-template-columns: repeat(2, 1fr); gap:0.65rem; margin-bottom:1rem; text-align:center;">
                <div style="background:#f8fafc; padding:0.6rem; border-radius:10px; border:1px solid #e2e8f0;">
                    <div style="font-size:1.1rem; font-weight:900; color:#0f172a;">{{ $diagnostics['db_intelligence']['avg_query_time_ms'] }} ms</div>
                    <div style="font-size:0.65rem; font-weight:800; color:#64748b;">Avg Query Latency</div>
                </div>
                <div style="background:#f8fafc; padding:0.6rem; border-radius:10px; border:1px solid #e2e8f0;">
                    <div style="font-size:1.1rem; font-weight:900; color:#10b981;">{{ $diagnostics['db_intelligence']['cache_hit_rate'] }}</div>
                    <div style="font-size:0.65rem; font-weight:800; color:#64748b;">Cache Hit Rate</div>
                </div>
            </div>

            <div style="display:flex; gap:6px; flex-wrap:wrap;">
                <button onclick="runDbAction('optimize')" style="padding:0.4rem 0.75rem; border-radius:8px; font-size:0.72rem; font-weight:800; background:#2563eb; color:white; border:none; cursor:pointer;">Optimize Tables</button>
                <button onclick="runDbAction('analyze')" style="padding:0.4rem 0.75rem; border-radius:8px; font-size:0.72rem; font-weight:800; background:rgba(6, 182, 212, 0.1); color:#06b6d4; border:none; cursor:pointer;">Analyze Queries</button>
                <button onclick="runDbAction('repair')" style="padding:0.4rem 0.75rem; border-radius:8px; font-size:0.72rem; font-weight:800; background:rgba(100, 116, 139, 0.1); color:#475569; border:none; cursor:pointer;">Repair Integrity</button>
            </div>
        </div>

        {{-- App Diagnostics --}}
        <div class="it-section-card" style="margin-bottom:0;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h3 style="font-size:1.05rem; font-weight:900; color:#0f172a; margin:0; display:flex; align-items:center; gap:8px;">
                    <i data-lucide="check-circle-2" style="width:18px; color:#10b981;"></i> Application Diagnostics Checklist
                </h3>
                <span style="font-size:0.7rem; font-weight:800; color:#10b981;">All Passed</span>
            </div>

            <div style="max-height:210px; overflow-y:auto;">
                @foreach($diagnostics['app_diagnostics'] as $diagItem)
                <div style="display:flex; justify-content:space-between; align-items:center; padding:0.4rem 0.6rem; border-bottom:1px solid #f1f5f9; font-size:0.75rem;">
                    <span style="font-weight:700; color:#0f172a;">{{ $diagItem['name'] }}</span>
                    <span style="font-size:0.68rem; font-weight:800; padding:2px 6px; border-radius:99px; background:rgba(16, 185, 129, 0.1); color:#10b981;">
                        {{ $diagItem['value'] }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ADMINISTRATIVE AUDIT TRAIL --}}
    <div class="it-section-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h3 style="font-size:1.05rem; font-weight:900; color:#0f172a; margin:0; display:flex; align-items:center; gap:8px;">
                <i data-lucide="history" style="width:18px; color:#64748b;"></i> Administrative Security &amp; Remediation Audit Trail
            </h3>
            <span style="font-size:0.7rem; font-weight:800; color:#64748b;">Live Stream</span>
        </div>

        <div style="overflow-x:auto;">
            <table class="it-table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Administrator</th>
                        <th>Action Performed</th>
                        <th>Details &amp; Audit Scope</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($diagnostics['audit_trail'] as $logEntry)
                    <tr>
                        <td style="font-size:0.73rem; color:#64748b; font-weight:700;">{{ $logEntry->created_at ? $logEntry->created_at->format('Y-m-d H:i:s') : '-' }}</td>
                        <td style="font-weight:800; color:#0f172a;">{{ $logEntry->user ? $logEntry->user->name : 'System Janitor' }}</td>
                        <td><span style="font-size:0.68rem; font-weight:900; padding:2px 6px; border-radius:99px; background:rgba(37, 99, 235, 0.1); color:#2563eb;">{{ $logEntry->action }}</span></td>
                        <td style="font-size:0.78rem; color:#475569;">{{ $logEntry->description }}</td>
                        <td style="font-family:monospace; font-size:0.75rem; font-weight:700;">{{ $logEntry->ip_address }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center; padding:1.5rem; color:#64748b;">No administrative audit actions logged.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>



{{-- INTERACTIVE LINE-SPECIFIC CODE FIX MODAL --}}
<div class="it-modal-overlay" id="codeFixModal">
    <div class="it-modal-card">
        <div style="padding:1.25rem 1.5rem; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; background:rgba(37, 99, 235, 0.04);">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:32px; height:32px; border-radius:8px; background:rgba(37, 99, 235, 0.1); display:flex; align-items:center; justify-content:center; color:#2563eb;">
                    <i data-lucide="file-code" style="width:18px; height:18px;"></i>
                </div>
                <div>
                    <div style="font-size:0.95rem; font-weight:900; color:#0f172a;" id="modalIssueTitle">Line Code Fix Remediation</div>
                    <div style="font-size:0.75rem; color:#64748b; font-weight:700; font-family:monospace;" id="modalTargetFile">Target: -</div>
                </div>
            </div>
            <button onclick="closeCodeFixModal()" style="border:none; background:none; color:#64748b; cursor:pointer; padding:4px;">
                <i data-lucide="x" style="width:20px; height:20px;"></i>
            </button>
        </div>

        <div style="padding:1.5rem;">
            <div style="font-size:0.82rem; color:#64748b; margin-bottom:1rem; line-height:1.5;" id="modalIssueDescription">
                Remediation instructions for target file code fix.
            </div>

            <div style="font-size:0.75rem; font-weight:800; text-transform:uppercase; color:#64748b; letter-spacing:0.06em; margin-bottom:0.5rem;">
                Code Line Replacement Preview
            </div>

            <div style="background:#0f172a; padding:1rem; border-radius:12px; margin-bottom:1.5rem;">
                <div style="background:rgba(239,68,68,0.15); color:#fca5a5; padding:6px 12px; font-family:monospace; font-size:0.75rem; border-radius:6px; margin-bottom:6px;" id="modalDiffOld">Old Code</div>
                <div style="background:rgba(16,185,129,0.15); color:#6ee7b7; padding:6px 12px; font-family:monospace; font-size:0.75rem; border-radius:6px;" id="modalDiffNew">New Fixed Code</div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
                <button onclick="closeCodeFixModal()" style="padding:0.55rem 1.1rem; border-radius:10px; font-weight:800; font-size:0.8rem; background:#f1f5f9; color:#64748b; border:none; cursor:pointer;">
                    Close
                </button>
                <button id="modalBtnPatch" onclick="" style="padding:0.55rem 1.25rem; border-radius:10px; font-weight:800; font-size:0.8rem; background:#2563eb; color:white; border:none; cursor:pointer; display:flex; align-items:center; gap:6px;">
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
        setInterval(refreshTelemetryGauges, 5000);
    });

    async function refreshTelemetryGauges() {
        try {
            const res = await fetch('{{ route("it-hub.telemetry") }}');
            if (!res.ok) {
                throw new Error('Telemetry request failed (server or database offline)');
            }
            const data = await res.json();

            if (data.cpu_usage !== undefined) {
                const cpuEl = document.getElementById('statCpuLoad');
                if (cpuEl) cpuEl.innerText = `${data.cpu_usage}%`;
            }
            if (data.cpu_trend !== undefined) {
                const cpuTrendEl = document.getElementById('statCpuTrend');
                if (cpuTrendEl) {
                    if (data.cpu_trend >= 0) {
                        cpuTrendEl.innerHTML = `&uarr; ${data.cpu_trend}% prev`;
                        cpuTrendEl.style.color = '#ef4444';
                    } else {
                        cpuTrendEl.innerHTML = `&darr; ${Math.abs(data.cpu_trend)}% prev`;
                        cpuTrendEl.style.color = '#10b981';
                    }
                }
            }
            if (data.cpu_spec !== undefined) {
                const cpuSpecEl = document.getElementById('statCpuSpec');
                if (cpuSpecEl) cpuSpecEl.innerHTML = data.cpu_spec;
            }
            if (data.health_score !== undefined) {
                document.getElementById('statHealthScore').innerText = data.health_score;
            }

            // Executive summary score cards
            const scoreMap = [
                { key: 'performance_score', id: 'scorePerformance', labelId: 'labelPerformance',
                  getLabel: v => v >= 90 ? 'Optimal' : v >= 70 ? 'Moderate' : 'Degraded' },
                { key: 'security_score',    id: 'scoreSecurity',    labelId: 'labelSecurity',
                  getLabel: v => v >= 90 ? 'Protected' : v >= 70 ? 'Warning' : 'At Risk' },
                { key: 'database_score',   id: 'scoreDatabase',    labelId: 'labelDatabase',
                  getLabel: v => v >= 90 ? 'Sub-10ms' : v >= 70 ? 'Slow' : 'Critical' },
                { key: 'storage_score',    id: 'scoreStorage',     labelId: 'labelStorage',
                  getLabel: v => v >= 90 ? 'Healthy' : v >= 70 ? 'Filling' : 'Near Full' },
                { key: 'application_score',id: 'scoreApplication', labelId: 'labelApplication',
                  getLabel: v => v >= 90 ? 'v11.x Core' : v >= 70 ? 'Degraded' : 'Error State' },
                { key: 'network_score',    id: 'scoreNetwork',     labelId: 'labelNetwork',
                  getLabel: v => v >= 90 ? '100% Active' : v >= 70 ? 'Partial' : 'Offline' },
            ];
            scoreMap.forEach(({ key, id, labelId, getLabel }) => {
                if (data[key] !== undefined) {
                    const el = document.getElementById(id);
                    const lb = document.getElementById(labelId);
                    if (el) el.innerText = `${data[key]}%`;
                    if (lb) lb.innerText = getLabel(data[key]);
                }
            });
            if (data.db_latency_ms !== undefined) {
                document.getElementById('statDbLatency').innerHTML = `${data.db_latency_ms} <span style="font-size:0.9rem; font-weight:700; color:#64748b;">ms</span>`;
            }
            if (data.system_speed_mbps !== undefined) {
                const spdEl = document.getElementById('statSystemSpeedRate');
                if (spdEl) spdEl.innerHTML = `${data.system_speed_mbps} <span style="font-size:0.9rem; font-weight:700; color:#64748b;">MB/s</span>`;
            }
            if (data.execution_speed_ms !== undefined) {
                const execEl = document.getElementById('statExecTime');
                if (execEl) execEl.innerText = `${data.execution_speed_ms} ms`;
            }
            if (data.memory_used_mb !== undefined) {
                const memEl = document.getElementById('statMemoryUsed');
                if (memEl) memEl.innerHTML = `${data.memory_used_mb} <span style="font-size:0.9rem; font-weight:700; color:#64748b;">MB</span>`;
            }
            if (data.app_space_mb !== undefined) {
                const appSpcEl = document.getElementById('statAppSpace');
                if (appSpcEl) appSpcEl.innerHTML = `${data.app_space_mb} <span style="font-size:0.85rem; font-weight:700; color:#64748b;">MB App Footprint</span>`;
            }
            if (data.disk_growth_rate !== undefined) {
                const growthText = document.getElementById('statGrowthText');
                if (growthText) growthText.innerText = `${data.disk_growth_rate} (Increasing)`;
            }
            if (data.disk_used_gb !== undefined) {
                const diskUsedEl = document.getElementById('statDiskUsed');
                if (diskUsedEl) diskUsedEl.innerHTML = `Disk Total: <b>${data.disk_used_gb} GB</b> / ${data.disk_total_gb} GB (${data.disk_percent}% used)`;
            }

            if (data.services !== undefined && Array.isArray(data.services)) {
                const countEl = document.getElementById('monitoredServicesCount');
                if (countEl) {
                    const onlineCount = data.services.filter(s => s.status !== 'Offline').length;
                    countEl.innerText = `${onlineCount} / ${data.services.length} Services Online`;
                }
                data.services.forEach(svc => {
                    const slug = svc.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
                    const badge = document.getElementById(`service-status-${slug}`);
                    if (badge) {
                        badge.innerText = svc.status;
                        if (svc.status === 'Offline' || svc.badge === 'danger') {
                            badge.style.color = '#ef4444';
                            badge.style.background = 'rgba(239, 68, 68, 0.1)';
                        } else if (svc.status === 'Degraded' || svc.badge === 'warning') {
                            badge.style.color = '#f59e0b';
                            badge.style.background = 'rgba(245, 158, 11, 0.1)';
                        } else {
                            badge.style.color = '#10b981';
                            badge.style.background = 'rgba(16, 185, 129, 0.1)';
                        }
                    }
                });
            }
        } catch (e) {
            console.error('Telemetry refresh error:', e);
            
            // Database connection / server unreachable: mark database offline in UI
            const dbBadge = document.getElementById('service-status-mysql-database-engine');
            if (dbBadge) {
                dbBadge.innerText = 'Offline';
                dbBadge.style.color = '#ef4444';
                dbBadge.style.background = 'rgba(239, 68, 68, 0.1)';
            }
            
            const dbScore = document.getElementById('scoreDatabase');
            if (dbScore) dbScore.innerText = '0%';
            
            const dbLabel = document.getElementById('labelDatabase');
            if (dbLabel) dbLabel.innerText = 'Offline';

            const dbLatency = document.getElementById('statDbLatency');
            if (dbLatency) dbLatency.innerHTML = `&infin; <span style="font-size:0.9rem; font-weight:700; color:#ef4444;">ms</span>`;

            const healthScore = document.getElementById('statHealthScore');
            if (healthScore) healthScore.innerText = '15';
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
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({
                    title: 'Diagnostic Scan Complete!',
                    text: `Health Score: ${data.diagnostics.health_score}% | Latency: ${data.diagnostics.db_latency_ms}ms`,
                    icon: 'success',
                    timer: 2000,
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

    async function triggerServiceAction(serviceName, action) {
        try {
            const res = await fetch('{{ route("it-hub.service-action") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ service: serviceName, action: action })
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire('Service Updated', data.message, 'success');
            }
        } catch (e) {
            Swal.fire('Error', 'Service action failed.', 'error');
        }
    }

    async function executeMaintenance(cmd) {
        const confirm = await Swal.fire({
            title: 'Execute Maintenance Command?',
            text: `Action: ${cmd.replace('_', ' ').toUpperCase()}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Run Command'
        });
        if (!confirm.isConfirmed) return;

        try {
            const res = await fetch('{{ route("it-hub.maintenance-command") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ command: cmd })
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({
                    title: 'Command Executed',
                    text: data.message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                setTimeout(() => window.location.reload(), 1500);
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Failed to execute maintenance command.', 'error');
        }
    }

    async function runDbAction(action) {
        try {
            const res = await fetch('{{ route("it-hub.db-action") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ action: action })
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire('Database Action Complete', data.message, 'success');
            }
        } catch (e) {
            Swal.fire('Error', 'Database action failed.', 'error');
        }
    }

    async function triggerStoragePurge() {
        const confirm = await Swal.fire({
            title: 'Purge System & Storage Caches?',
            text: 'This will execute view:clear, route:clear, config:clear, and cache:clear to flush compiled views, routes, app caches, clear the log buffer, and delete stale session files.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d97706',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Purge Everything'
        });
        if (!confirm.isConfirmed) return;

        try {
            const res = await fetch('{{ route("it-hub.purge-storage") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire({
                    title: 'Storage Purged!',
                    text: data.message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                setTimeout(() => window.location.reload(), 1500);
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Failed to purge storage.', 'error');
        }
    }

    async function killSession(sessionId, userId, username) {
        const confirm = await Swal.fire({
            title: `Terminate Session for @${username}?`,
            text: 'Forcefully log out this user and invalidate session token.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Kill Session'
        });
        if (!confirm.isConfirmed) return;

        try {
            const res = await fetch('{{ route("it-hub.kill-session") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ session_id: sessionId, user_id: userId })
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire('Session Terminated', data.message, 'success');
                window.location.reload();
            }
        } catch (e) {
            Swal.fire('Error', 'Failed to terminate session.', 'error');
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
            text: 'Execute code patch remediation for this diagnostic issue?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Apply Patch'
        });
        if (!confirm.isConfirmed) return;

        try {
            const res = await fetch('{{ route("it-hub.apply-patch") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ issue_id: issueId })
            });
            const data = await res.json();
            if (data.success) {
                Swal.fire('Patch Applied!', data.message, 'success');
                window.location.reload();
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Failed to apply code patch.', 'error');
        }
    }
</script>
@endpush
@endsection
