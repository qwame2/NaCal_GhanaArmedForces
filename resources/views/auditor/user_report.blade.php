<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Individual User Activity & Trail Report - {{ $targetUser->name }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #0f172a;
            background: white;
            font-size: 11px;
            margin: 0;
            padding: 30px;
            line-height: 1.5;
        }

        .header-container {
            text-align: center;
            border-bottom: 2px double #0f172a;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .logo-placeholder {
            width: 50px;
            height: auto;
            margin-bottom: 8px;
        }

        .org-title {
            font-size: 15px;
            font-weight: 900;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin: 0;
            color: #0f172a;
        }

        .doc-title {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            margin: 6px 0 0 0;
            color: #475569;
            letter-spacing: 0.02em;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
            font-weight: 600;
            background: #f8fafc;
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .meta-item span {
            color: #64748b;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.04em;
        }

        .section-title {
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            background: #f1f5f9;
            padding: 7px 12px;
            border-left: 3.5px solid #0f172a;
            margin: 22px 0 10px 0;
        }

        .essay-card {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
            line-height: 1.6;
            font-size: 11px;
            color: #1e293b;
        }

        .essay-card p {
            margin: 0 0 10px 0;
        }

        .essay-card p:last-child {
            margin-bottom: 0;
        }

        .metric-boxes {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .metric-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
        }

        .metric-box .num {
            font-size: 16px;
            font-weight: 900;
            color: #0f172a;
        }

        .metric-box .lbl {
            font-size: 8.5px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 800;
            margin-top: 2px;
        }

        .audit-print-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .audit-print-table th {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            text-align: left;
            color: #334155;
        }

        .audit-print-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            font-size: 9.5px;
            color: #0f172a;
            vertical-align: top;
        }

        .sev-badge {
            font-weight: 800;
            font-size: 8px;
            text-transform: uppercase;
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
        }

        .sev-info { background: #e0f2fe; color: #0369a1; }
        .sev-warning { background: #fef3c7; color: #b45309; }
        .sev-danger { background: #fee2e2; color: #b91c1c; }
        .sev-success { background: #dcfce7; color: #15803d; }

        .signature-block {
            margin-top: 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            page-break-inside: avoid;
        }

        .sig-col {
            text-align: center;
        }

        .sig-line {
            border-top: 1.5px solid #0f172a;
            margin-top: 40px;
            padding-top: 6px;
            font-weight: 800;
            font-size: 10px;
            text-transform: uppercase;
            color: #334155;
        }

        .sig-subtitle {
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 700;
            margin-top: 2px;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 12mm 10mm;
            }

            html, body {
                height: auto !important;
                overflow: visible !important;
                padding: 0 !important;
                margin: 0 !important;
                background: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .page-break {
                page-break-before: always;
                break-before: page;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                display: block;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            thead {
                display: table-header-group;
            }
        }
    </style>
</head>
<body onload="window.print();">

    {{-- Screen Mode Button --}}
    <div class="no-print" style="position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; gap: 10px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #0f172a; color: white; border: none; border-radius: 8px; font-weight: 800; cursor: pointer; box-shadow: 0 10px 25px rgba(0,0,0,0.15); font-size: 12px; display: flex; align-items: center; gap: 8px;">
            🖨️ Print User Activity Report
        </button>
    </div>

    {{-- Header --}}
    <div class="header-container">
        <img src="{{ asset('img/NACOC1.png') }}" class="logo-placeholder" alt="Logo">
        <div class="org-title">{{ \App\Models\Setting::get('organization_name', 'NACOC') }}</div>
        <div class="doc-title">Comprehensive Individual User Audit & Activity Trail Report</div>
    </div>

    {{-- Personnel & Target User Metadata --}}
    <div class="meta-grid">
        <div>
            <div class="meta-item"><span>Subject Personnel:</span> <strong>{{ $targetUser->name }}</strong> ({{ '@' . $targetUser->username }})</div>
            <div class="meta-item"><span>Assigned Role:</span> {{ $targetUser->role }} {{ $targetUser->rank ? '(' . $targetUser->rank . ')' : '' }}</div>
            <div class="meta-item"><span>Department Unit:</span> {{ $targetUser->department ?: 'Unassigned' }}</div>
            <div class="meta-item"><span>Account Status:</span> {{ $targetUser->is_active ? 'ACTIVE' : 'DEACTIVATED' }} ({{ ucfirst($targetUser->registration_status) }})</div>
        </div>
        <div style="text-align: right;">
            <div class="meta-item"><span>Report Generated By:</span> {{ $auditor->name }} (Auditor)</div>
            <div class="meta-item"><span>Generated Date:</span> {{ now()->format('d/m/Y H:i:s') }}</div>
            <div class="meta-item"><span>Activity Log Scope:</span> {{ $firstActivity }} &rarr; {{ $lastActivity }}</div>
            <div class="meta-item"><span>Security Classification:</span> RESTRICTED / CONFIDENTIAL AUDIT</div>
        </div>
    </div>

    {{-- Key Activity Metrics Grid --}}
    <div class="metric-boxes">
        <div class="metric-box">
            <div class="num">{{ number_format($totalLogs) }}</div>
            <div class="lbl">Total System Trails</div>
        </div>
        <div class="metric-box">
            <div class="num">{{ number_format($securityLogs) }}</div>
            <div class="lbl">Security Events</div>
        </div>
        <div class="metric-box">
            <div class="num" style="color: {{ $dangerLogs->count() > 0 ? '#b91c1c' : '#0f172a' }};">{{ number_format($dangerLogs->count()) }}</div>
            <div class="lbl">High-Risk / Danger Events</div>
        </div>
        <div class="metric-box">
            <div class="num">{{ number_format($userRequisitions->count()) }}</div>
            <div class="lbl">Total Requisitions Handled</div>
        </div>
    </div>

    {{-- EXECUTIVE AUDIT ESSAY SYNTHESIS --}}
    <div class="section-title">I. Executive Summary & Audit Trail Essay Analysis</div>
    <div class="essay-card">
        <p>
            <strong>Personnel Background & Overview:</strong> 
            This document outlines the full digital footprint and System Audit Trail history for 
            <strong>{{ $targetUser->name }}</strong> (Username: <code>{{ $targetUser->username }}</code>), 
            currently assigned as a <strong>{{ $targetUser->role }}</strong> within the <strong>{{ $targetUser->department ?: 'Unassigned' }}</strong> department.
            Across the evaluated operational timeline, the system logged a total of <strong>{{ number_format($totalLogs) }} audit trail events</strong> 
            and <strong>{{ number_format($userRequisitions->count()) }} supply requisitions</strong> where this personnel was recorded as requester, authorizer, or processor.
        </p>

        <p>
            <strong>Authentication & Access Patterns:</strong> 
            The system recorded <strong>{{ number_format($authLogs) }} authentication events</strong> for this account. 
            The recorded activity spans from <strong>{{ $firstActivity }}</strong> to <strong>{{ $lastActivity }}</strong>.
            @if($loginCount > 0)
                The personnel completed {{ $loginCount }} authenticated session login(s).
            @endif
            @if($targetUser->is_online)
                The account is currently flagged as <strong>Active/Online</strong> in real-time.
            @else
                The account is currently offline.
            @endif
        </p>

        <p>
            <strong>Role & Department Governance History:</strong>
            @if($roleChangeLogs->count() > 0 || $deptChangeLogs->count() > 0)
                Security audit logs reveal administrative modifications for this account:
                @foreach($roleChangeLogs as $rLog)
                    Role change event recorded on {{ $rLog->created_at->format('d/m/Y H:i') }}: <em>{{ $rLog->description }}</em>.
                @endforeach
                @foreach($deptChangeLogs as $dLog)
                    Department reassignment recorded on {{ $dLog->created_at->format('d/m/Y H:i') }}: <em>{{ $dLog->description }}</em>.
                @endforeach
            @else
                No administrative role or department reassignments have been logged for this personnel during the tracked timeline.
            @endif
        </p>

        <p>
            <strong>Risk Assessment & Oversight Findings:</strong>
            @if($dangerLogs->count() > 0)
                <span style="color: #b91c1c; font-weight: 800;">CRITICAL AUDIT ALERT:</span> 
                The system detected <strong>{{ $dangerLogs->count() }} high-risk / danger event(s)</strong> tied to this user. 
                These include actions such as role elevations, administrative privilege modifications, or security override events. Close auditor scrutiny is recommended.
            @elseif($warningLogs->count() > 0)
                The audit trail contains <strong>{{ $warningLogs->count() }} warning-level event(s)</strong> (e.g. department reassignments or authorization changes). No critical security violations detected.
            @else
                The personnel's activity trail reflects standard operational compliance with zero high-risk or warning flags recorded.
            @endif
        </p>
    </div>

    {{-- TABULATED SYSTEM AUDIT TRAIL TABLE --}}
    <div class="section-title">II. Tabulated System Audit Trail Events</div>
    <table class="audit-print-table">
        <thead>
            <tr>
                <th style="width: 110px;">Timestamp</th>
                <th style="width: 80px;">Category</th>
                <th style="width: 140px;">Security Action</th>
                <th>Event Description & Metadata</th>
                <th style="width: 65px;">Severity</th>
                <th style="width: 85px;">IP Address</th>
            </tr>
        </thead>
        <tbody>
            @forelse($systemLogs as $log)
                @php
                    $sev = strtolower($log->severity);
                    $cls = match($sev) {
                        'danger', 'critical' => 'sev-danger',
                        'warning' => 'sev-warning',
                        'success' => 'sev-success',
                        default => 'sev-info'
                    };
                @endphp
                <tr>
                    <td style="font-family: monospace; font-size: 9px;">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    <td style="font-weight: 700;">{{ $log->event_type }}</td>
                    <td style="font-family: monospace; font-weight: 700; color: #0369a1;">{{ $log->action }}</td>
                    <td>{{ $log->description ?: $log->friendly_description }}</td>
                    <td><span class="sev-badge {{ $cls }}">{{ $log->severity }}</span></td>
                    <td style="font-family: monospace; font-size: 8.5px;">{{ $log->ip_address ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #64748b; font-style: italic; padding: 15px;">No system audit trail events logged for this user.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- TABULATED REQUISITIONS HISTORY --}}
    @if($userRequisitions->count() > 0)
    <div class="page-break"></div>
    <div class="section-title">III. Tabulated Supply Requisition & Pick-up History</div>
    <table class="audit-print-table">
        <thead>
            <tr>
                <th style="width: 80px;">Req #</th>
                <th style="width: 85px;">Date Requested</th>
                <th style="width: 110px;">Requester & Dept</th>
                <th>Approved Items & Quantities</th>
                <th>Purpose / Admin Notes</th>
                <th style="width: 100px;">Approval / Processed By</th>
                <th style="width: 85px;">Status</th>
                <th style="width: 90px;">Collection Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($userRequisitions as $req)
                <tr>
                    <td style="font-family: monospace; font-weight: 800; color: #0369a1;">{{ $req->unique_id ?: ('REQ-'.str_pad($req->id,5,'0',STR_PAD_LEFT)) }}</td>
                    <td>{{ $req->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <strong>{{ $req->requester_name ?: ($req->requester?->name ?: 'N/A') }}</strong><br>
                        <span style="font-size: 8px; color: #64748b;">{{ $req->department }}</span>
                    </td>
                    <td>
                        @if($req->items && $req->items->count() > 0)
                            <ul style="margin: 0; padding-left: 14px; list-style-type: square;">
                                @foreach($req->items as $item)
                                    <li>
                                        <strong>{{ $item->description ?: 'Unnamed Item' }}</strong> &mdash; 
                                        <span style="font-weight: 800; color: #059669;">{{ number_format($item->quantity_approved ?: $item->quantity_requested) }} {{ $item->unit }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <span style="color: #64748b; font-style: italic;">No items recorded</span>
                        @endif
                    </td>
                    <td>
                        {{ $req->purpose }}
                        @if($req->admin_notes)
                            <div style="font-size: 8px; color: #475569; margin-top: 2px;"><em>Notes: {{ $req->admin_notes }}</em></div>
                        @endif
                    </td>
                    <td>
                        @if($req->processor)
                            <strong>{{ $req->processor->name }}</strong><br>
                            <span style="font-size: 7.5px; color: #059669; font-weight: 800;">(Head of Stores)</span>
                        @elseif($req->stores_approved_by)
                            <strong>{{ $req->stores_approved_by }}</strong><br>
                            <span style="font-size: 7.5px; color: #0369a1; font-weight: 800;">(Authorizer)</span>
                        @else
                            <span style="color: #64748b; font-size: 8px;">Pending Review</span>
                        @endif
                    </td>
                    <td style="font-weight: 800; text-transform: uppercase;">{{ ucfirst(str_replace('_', ' ', $req->status)) }}</td>
                    <td style="vertical-align: middle;">
                        @if($req->collected_at)
                            <strong style="color: #059669;">{{ \Carbon\Carbon::parse($req->collected_at)->format('d/m/Y H:i') }}</strong>
                            @if($req->collector_name)
                                <div style="font-size: 7.5px; color: #64748b;">Picked up by: {{ $req->collector_name }}</div>
                            @endif
                        @else
                            <span style="color: #94a3b8; font-style: italic;">Not Collected</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif


</body>
</html>
