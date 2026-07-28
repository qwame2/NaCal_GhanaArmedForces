@extends('layouts.dashboard')

@section('content')
<style>
    .ds-page { padding: 1.75rem 2rem; background: #f8fafc; min-height: 100vh; }

    /* Header bar */
    .ds-header {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;
    }
    .ds-header-left { display: flex; align-items: center; gap: 14px; }
    .ds-icon-wrap {
        width: 48px; height: 48px; border-radius: 14px;
        background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
        display: flex; align-items: center; justify-content: center; color: #fff;
        box-shadow: 0 6px 18px rgba(37,99,235,0.3);
    }
    .ds-title   { font-size: 1.55rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em; margin: 0; }
    .ds-subtitle{ font-size: 0.76rem; color: #64748b; font-weight: 700; margin-top: 2px; }

    /* Score banner */
    #dsBanner {
        display: none; background: linear-gradient(135deg,#0f172a 0%,#1e3a5f 100%);
        border-radius: 18px; padding: 1.25rem 1.5rem; margin-bottom: 1.75rem;
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;
    }
    .ds-score-ring { position: relative; width: 72px; height: 72px; flex-shrink: 0; }
    .ds-score-ring svg { transform: rotate(-90deg); }
    .ds-score-num {
        position: absolute; inset: 0; display: flex; align-items: center;
        justify-content: center; font-size: 1.1rem; font-weight: 900; color: #fff;
    }
    .ds-stat { text-align: center; }
    .ds-stat-val { font-size: 1.4rem; font-weight: 900; color: #fff; }
    .ds-stat-lbl { font-size: 0.68rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; }

    /* Progress bar */
    #dsProgressWrap { background: #e2e8f0; border-radius: 99px; height: 6px; margin-bottom: 1.75rem; overflow: hidden; }
    #dsProgressBar  { height: 6px; background: linear-gradient(90deg,#2563eb,#06b6d4); width: 0%; border-radius: 99px; transition: width 2s ease; }

    /* Status strip */
    #dsStatusStrip {
        display: flex; align-items: center; gap: 10px;
        background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
        padding: 0.65rem 1.1rem; margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    #dsSpinner {
        width: 14px; height: 14px; border: 2px solid #2563eb;
        border-top-color: transparent; border-radius: 50%; animation: spin 0.7s linear infinite;
        flex-shrink: 0;
    }
    #dsStatusText   { font-size: 0.8rem; font-weight: 700; color: #475569; }
    #dsDuration     { margin-left: auto; font-size: 0.72rem; font-weight: 800; color: #94a3b8; }
    #dsTimestamp    { font-size: 0.72rem; font-weight: 700; color: #94a3b8; }

    /* Section cards */
    .ds-section {
        background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px;
        overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 1.25rem;
    }
    .ds-section-header {
        padding: 1rem 1.25rem; display: flex; align-items: center;
        justify-content: space-between; border-bottom: 1px solid #f1f5f9;
    }
    .ds-section-title {
        display: flex; align-items: center; gap: 10px;
        font-size: 0.9rem; font-weight: 900; color: #0f172a;
    }
    .ds-section-icon {
        width: 32px; height: 32px; border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
    }
    .ds-section-meta { display: flex; align-items: center; gap: 8px; }
    .ds-pct-badge {
        font-size: 0.75rem; font-weight: 900; padding: 4px 12px;
        border-radius: 99px;
    }

    /* Check rows grid */
    .ds-checks-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    }
    .ds-check-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 0.55rem 1.25rem; border-bottom: 1px solid #f8fafc;
        border-right: 1px solid #f8fafc;
    }
    .ds-check-row:nth-child(even) { background: #fafafa; }
    .ds-check-name { font-size: 0.76rem; font-weight: 700; color: #334155; }
    .ds-check-right { display: flex; align-items: center; gap: 8px; }
    .ds-check-val   { font-size: 0.71rem; color: #64748b; font-weight: 600; font-family: monospace; }
    .ds-pass { font-size: 0.68rem; font-weight: 900; padding: 2px 8px; border-radius: 99px; background: rgba(16,185,129,0.1); color: #10b981; }
    .ds-fail { font-size: 0.68rem; font-weight: 900; padding: 2px 8px; border-radius: 99px; background: rgba(239,68,68,0.1);  color: #ef4444; }

    /* Placeholder */
    .ds-placeholder { text-align: center; padding: 4rem 2rem; color: #94a3b8; }
    .ds-placeholder-icon { font-size: 3rem; margin-bottom: 0.75rem; display: inline-block; animation: spin 2s linear infinite; }

    @keyframes spin { to { transform: rotate(360deg); } }
</style>

<div class="ds-page">

    {{-- ── PAGE HEADER ─────────────────────────────────────────────────────── --}}
    <div class="ds-header">
        <div class="ds-header-left">
            <a href="{{ route('it-hub.dashboard') }}"
               style="width:36px; height:36px; border-radius:10px; background:#fff; border:1px solid #e2e8f0;
                      display:flex; align-items:center; justify-content:center; color:#64748b;
                      text-decoration:none; box-shadow:0 2px 6px rgba(0,0,0,0.06);">
                <i data-lucide="arrow-left" style="width:17px;"></i>
            </a>
            <div class="ds-icon-wrap">
                <i data-lucide="scan-line" style="width:24px;"></i>
            </div>
            <div>
                <h1 class="ds-title">Deep Diagnostic Scan</h1>
                <div class="ds-subtitle">System &bull; Server &bull; Database &bull; Application &bull; API Endpoints</div>
            </div>
        </div>
        <button id="btnRescan" onclick="runDeepScan()"
            style="display:inline-flex; align-items:center; gap:8px; padding:0.65rem 1.3rem;
                   background:#2563eb; color:#fff; border:none; border-radius:12px;
                   font-weight:800; font-size:0.82rem; cursor:pointer;
                   box-shadow:0 4px 14px rgba(37,99,235,0.3);">
            <i data-lucide="refresh-cw" id="rescanIcon" style="width:16px;"></i>
            Re-Run Scan
        </button>
    </div>

    {{-- ── SCORE BANNER ─────────────────────────────────────────────────────── --}}
    <div id="dsBanner" style="display:none; background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 100%);
         border-radius:18px; padding:1.25rem 1.75rem; margin-bottom:1.75rem;
         box-shadow:0 10px 30px rgba(15,23,42,0.3);">
        <div style="display:flex; align-items:center; gap:1.5rem; flex-wrap:wrap;">
            <div class="ds-score-ring">
                <svg width="72" height="72" viewBox="0 0 36 36">
                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                          fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="3"/>
                    <path id="dsScoringArc"
                          stroke-dasharray="0, 100"
                          d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                          fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round"/>
                </svg>
                <div class="ds-score-num" id="dsBannerScore">—</div>
            </div>
            <div>
                <div style="font-size:1.25rem; font-weight:900; color:#fff;">Overall Health Score</div>
                <div id="dsBannerLabel" style="font-size:0.8rem; color:#94a3b8; font-weight:700; margin-top:2px;"></div>
            </div>
        </div>
        <div style="display:flex; gap:2rem; flex-wrap:wrap;">
            <div class="ds-stat">
                <div class="ds-stat-val" id="dsBannerPassed">—</div>
                <div class="ds-stat-lbl">Passed</div>
            </div>
            <div class="ds-stat">
                <div class="ds-stat-val" id="dsBannerFailed">—</div>
                <div class="ds-stat-lbl">Failed</div>
            </div>
            <div class="ds-stat">
                <div class="ds-stat-val" id="dsBannerTotal">—</div>
                <div class="ds-stat-lbl">Total Checks</div>
            </div>
            <div class="ds-stat">
                <div class="ds-stat-val" id="dsBannerMs">—</div>
                <div class="ds-stat-lbl">Duration</div>
            </div>
        </div>
    </div>

    {{-- ── PROGRESS BAR ─────────────────────────────────────────────────────── --}}
    <div id="dsProgressWrap">
        <div id="dsProgressBar"></div>
    </div>

    {{-- ── STATUS STRIP ─────────────────────────────────────────────────────── --}}
    <div id="dsStatusStrip">
        <span id="dsSpinner"></span>
        <span id="dsStatusText">Initialising deep scan engine…</span>
        <span id="dsDuration" style="margin-left:auto;"></span>
        <span id="dsTimestamp"></span>
    </div>

    {{-- ── RESULTS BODY ─────────────────────────────────────────────────────── --}}
    <div id="dsBody">
        <div class="ds-placeholder">
            <div class="ds-placeholder-icon">⟳</div>
            <div style="font-weight:800; font-size:1rem; color:#475569;">Running comprehensive scan across all layers…</div>
            <div style="font-size:0.8rem; margin-top:0.4rem;">Checking system resources, PHP extensions, database integrity, and all API endpoints.</div>
        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof lucide !== 'undefined') lucide.createIcons();
    runDeepScan();
});

const SECTION_ICONS = {
    system:      'monitor',
    server:      'server',
    database:    'database',
    application: 'layers',
    api:         'activity',
};
const SECTION_COLORS = {
    system:      '#2563eb',
    server:      '#7c3aed',
    database:    '#06b6d4',
    application: '#10b981',
    api:         '#f59e0b',
};

async function runDeepScan() {
    const btn      = document.getElementById('btnRescan');
    const icon     = document.getElementById('rescanIcon');
    const body     = document.getElementById('dsBody');
    const progress = document.getElementById('dsProgressBar');
    const statusTxt= document.getElementById('dsStatusText');
    const spinner  = document.getElementById('dsSpinner');
    const banner   = document.getElementById('dsBanner');
    const durationEl  = document.getElementById('dsDuration');
    const timestampEl = document.getElementById('dsTimestamp');

    // Reset
    btn.disabled = true; btn.style.opacity = '0.7';
    icon.style.animation = 'spin 1s linear infinite';
    banner.style.display = 'none';
    progress.style.width = '0%';
    progress.style.background = 'linear-gradient(90deg,#2563eb,#06b6d4)';
    spinner.style.display = 'inline-block';
    statusTxt.textContent = 'Initialising deep scan engine…';
    durationEl.textContent = '';
    timestampEl.textContent = '';
    body.innerHTML = `<div class="ds-placeholder">
        <div class="ds-placeholder-icon" style="animation:spin 1.5s linear infinite; display:inline-block;">⟳</div>
        <div style="font-weight:800; font-size:1rem; color:#475569; margin-top:0.5rem;">Running comprehensive scan across all layers…</div>
        <div style="font-size:0.8rem; margin-top:0.4rem; color:#94a3b8;">Checking system resources, PHP extensions, database integrity, and all API endpoints.</div>
    </div>`;

    setTimeout(() => { progress.style.width = '55%'; }, 100);

    try {
        const res  = await fetch('{{ route("it-hub.deep-scan") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
        const data = await res.json();

        progress.style.width = '100%';

        if (!data.success) {
            statusTxt.textContent = 'Scan failed — server returned an error.';
            spinner.style.display = 'none';
            body.innerHTML = `<div class="ds-placeholder" style="color:#ef4444;">Scan returned an error. Please try again.</div>`;
            return;
        }

        // ── Banner ─────────────────────────────────────────────────────────────
        const sc      = data.overall_score;
        const scColor = sc >= 90 ? '#10b981' : sc >= 70 ? '#f59e0b' : '#ef4444';
        const failed  = data.total - data.passed;

        document.getElementById('dsBannerScore').textContent  = sc + '%';
        document.getElementById('dsBannerLabel').textContent  = sc >= 90 ? 'System Optimal' : sc >= 70 ? 'Warnings Detected' : 'Critical Issues Found';
        document.getElementById('dsBannerPassed').textContent = data.passed;
        document.getElementById('dsBannerFailed').textContent = failed;
        document.getElementById('dsBannerTotal').textContent  = data.total;
        document.getElementById('dsBannerMs').textContent     = data.elapsed_ms + 'ms';

        const arc = document.getElementById('dsScoringArc');
        arc.setAttribute('stroke-dasharray', `${sc}, 100`);
        arc.setAttribute('stroke', scColor);
        banner.style.display = 'flex';
        banner.style.justifyContent = 'space-between';
        banner.style.alignItems = 'center';
        banner.style.flexWrap = 'wrap';
        banner.style.gap = '1rem';

        // ── Sections ──────────────────────────────────────────────────────────
        let html = '';
        for (const [key, section] of Object.entries(data.results)) {
            const passed   = section.checks.filter(c => c.pass).length;
            const total    = section.checks.length;
            const pct      = total > 0 ? Math.round((passed / total) * 100) : 0;
            const color    = SECTION_COLORS[key]  || '#64748b';
            const iconName = SECTION_ICONS[key]   || 'check-circle';
            const badgeClr = pct >= 90 ? '#10b981' : pct >= 70 ? '#f59e0b' : '#ef4444';

            const rows = section.checks.map((c, i) => {
                const badge = c.pass
                    ? `<span class="ds-pass">✓ PASS</span>`
                    : `<span class="ds-fail">✗ FAIL</span>`;
                return `<div class="ds-check-row">
                    <span class="ds-check-name">${c.name}</span>
                    <div class="ds-check-right">
                        <span class="ds-check-val">${c.value}</span>
                        ${badge}
                    </div>
                </div>`;
            }).join('');

            html += `
            <div class="ds-section">
                <div class="ds-section-header" style="background:linear-gradient(135deg,${color}10 0%,${color}05 100%);">
                    <div class="ds-section-title">
                        <div class="ds-section-icon" style="background:${color}20; color:${color};">
                            <i data-lucide="${iconName}" style="width:16px; height:16px;"></i>
                        </div>
                        ${section.label}
                    </div>
                    <div class="ds-section-meta">
                        <span style="font-size:0.72rem; font-weight:800; color:#64748b;">${passed}/${total} passed</span>
                        <span class="ds-pct-badge" style="color:${badgeClr}; background:${badgeClr}18;">${pct}%</span>
                    </div>
                </div>
                <div class="ds-checks-grid">${rows}</div>
            </div>`;
        }
        body.innerHTML = html;

        // ── Status strip ──────────────────────────────────────────────────────
        spinner.style.display = 'none';
        statusTxt.textContent = `Scan complete — ${data.passed}/${data.total} checks passed`;
        durationEl.textContent  = `Duration: ${data.elapsed_ms}ms`;
        timestampEl.textContent = `Completed at ${data.timestamp}`;

        if (typeof lucide !== 'undefined') lucide.createIcons();

    } catch (e) {
        progress.style.width = '100%';
        progress.style.background = '#ef4444';
        spinner.style.display = 'none';
        statusTxt.textContent = 'Deep scan error — check server connectivity.';
        body.innerHTML = `<div class="ds-placeholder" style="color:#ef4444;">${e.message}</div>`;
    } finally {
        btn.disabled = false; btn.style.opacity = '1';
        icon.style.animation = 'none';
    }
}
</script>
@endpush
@endsection
