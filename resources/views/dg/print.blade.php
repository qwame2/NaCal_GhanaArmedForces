<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executive oversight & Command Ledger - NACOC Central Store</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1e293b;
            background: white;
            font-size: 11px;
            margin: 0;
            padding: 30px;
            line-height: 1.4;
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
            margin-bottom: 25px;
            font-weight: 600;
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
            padding: 6px 10px;
            border-left: 3px solid #0f172a;
            margin: 25px 0 10px 0;
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

        .signature-block {
            margin-top: 50px;
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

    {{-- Floating Print Button for Screen Mode --}}
    <div class="no-print" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #0f172a; color: white; border: none; border-radius: 8px; font-weight: 800; cursor: pointer; box-shadow: 0 10px 25px rgba(0,0,0,0.15); font-size: 12px; display: flex; align-items: center; gap: 8px;">
            🖨️ Trigger Browser Print
        </button>
    </div>

    {{-- Header --}}
    <div class="header-container">
        <img src="{{ asset('img/NACOC1.png') }}" class="logo-placeholder" alt="Logo">
        <div class="org-title">{{ \App\Models\Setting::get('organization_name', 'NACOC') }}</div>
        <div class="doc-title">Executive Command Oversight & Verification Report</div>
    </div>

    {{-- Metadata --}}
    <div class="meta-grid">
        <div>
            <div class="meta-item"><span>Generated By:</span> {{ $dg->name }}</div>
            <div class="meta-item"><span>Department:</span> {{ $dg->department ?? 'Executive Directorate' }}</div>
            <div class="meta-item"><span>Rank / Role:</span> {{ $dg->rank ? $dg->rank . ' (Director General)' : 'Director General (DG)' }}</div>
        </div>
        <div style="text-align: right;">
            <div class="meta-item"><span>Date Generated:</span> {{ now()->format('d/m/Y H:i') }}</div>
            <div class="meta-item"><span>Date Scope:</span> 
                @if(request('date_from') && request('date_to'))
                    {{ \Carbon\Carbon::parse(request('date_from'))->format('d/m/Y') }} to {{ \Carbon\Carbon::parse(request('date_to'))->format('d/m/Y') }}
                @elseif(request('date_from'))
                    From {{ \Carbon\Carbon::parse(request('date_from'))->format('d/m/Y') }}
                @elseif(request('date_to'))
                    Up to {{ \Carbon\Carbon::parse(request('date_to'))->format('d/m/Y') }}
                @else
                    All System History
                @endif
            </div>
            <div class="meta-item"><span>Oversight Classification:</span> STRICTLY CONFIDENTIAL - EXECUTIVE ONLY</div>
        </div>
    </div>

    {{-- I. SYSTEM AUDIT TRAIL LOGS --}}
    <div class="section-title">I. System Audit Trail (Event Logs)</div>
    <table class="audit-print-table">
        <thead>
            <tr>
                <th style="width: 110px;">Timestamp</th>
                <th style="width: 110px;">User / Role</th>
                <th style="width: 80px;">Category</th>
                <th style="width: 130px;">Action Event</th>
                <th>Description</th>
                <th style="width: 60px;">Severity</th>
            </tr>
        </thead>
        <tbody>
            @forelse($systemLogs as $log)
                <tr>
                    <td style="font-family: monospace;">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    <td>
                        <strong>{{ $log->user ? $log->user->name : 'System Automated' }}</strong><br>
                        <span style="font-size: 8px; color: #4f46e5; font-weight: 800;">{{ $log->user ? $log->user->role : 'Automated' }}</span><br>
                        <span style="font-size: 8px; color: #64748b;">{{ $log->user ? '@' . $log->user->username : '' }}</span>
                    </td>
                    <td>{{ $log->event_type }}</td>
                    <td style="font-family: monospace; font-weight: 700; color: #334155;">{{ $log->action }}</td>
                    <td>{{ $log->description }}</td>
                    <td style="font-weight: 800; font-size: 8.5px; text-transform: uppercase;">{{ $log->severity }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #64748b; font-style: italic;">No audit trail events logged.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>

    {{-- II. STOCK BALANCE REGISTRY --}}
    <div class="section-title">II. Stock Balance Oversight Registry</div>
    <table class="audit-print-table">
        <thead>
            <tr>
                <th style="width: 75px;">Entry Date</th>
                <th>Item Description</th>
                <th style="width: 120px;">Category Standard</th>
                <th style="width: 80px; text-align: right;">Qty Received</th>
                <th style="width: 80px; text-align: right;">Stock Bal.</th>
                <th style="width: 60px; text-align: right;">Discrepancy</th>
                <th style="width: 85px;">Acquisition</th>
                <th>Supplier / Donor Registry Name</th>
            </tr>
        </thead>
        <tbody>
            @forelse($receivedItems as $item)
                <tr>
                    <td>{{ $item->entry_date ? \Carbon\Carbon::parse($item->entry_date)->format('d/m/Y') : '-' }}</td>
                    <td><strong>{{ $item->description }}</strong></td>
                    <td>{{ $ledgeMap[$item->ledge_category] ?? $item->ledge_category }}</td>
                    <td style="text-align: right; font-weight: 700;">{{ number_format($item->qty) }} {{ $item->unit }}</td>
                    <td style="text-align: right; font-weight: 700; color: #4f46e5;">{{ number_format($item->stock_balance) }} {{ $item->unit }}</td>
                    <td style="text-align: right; font-weight: 700; color: {{ $item->variance > 0 ? '#ef4444' : '#0f172a' }};">{{ number_format($item->variance) }}</td>
                    <td>{{ $item->acquisition_type }}</td>
                    <td>{{ $item->supplier_name ?: ($item->donor_name ?: 'System') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #64748b; font-style: italic;">No stock entries logged in this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>

    {{-- III. STAFF REQUISITIONS REGISTRY --}}
    <div class="section-title">III. Staff Requisitions Registry</div>
    <table class="audit-print-table">
        <thead>
            <tr>
                <th style="width: 75px;">Requisition ID</th>
                <th>Requester</th>
                <th style="width: 110px;">Department</th>
                <th>Purpose</th>
                <th style="width: 65px;">Priority</th>
                <th style="width: 65px;">Usage</th>
                <th style="width: 100px;">Status</th>
                <th style="width: 90px;">Date Requested</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requisitions as $req)
                <tr>
                    <td style="font-family: monospace; font-weight: 700;">{{ $req->unique_id }}</td>
                    <td>
                        <strong>{{ $req->requester_name }}</strong><br>
                        <span style="font-size: 8px; color: #64748b;">{{ $req->rank_or_title ?: 'None' }}</span>
                    </td>
                    <td>{{ $req->department }}</td>
                    <td>{{ $req->purpose }}</td>
                    <td>{{ strtoupper($req->priority) }}</td>
                    <td>{{ strtoupper($req->usage_type) }}</td>
                    <td><strong>{{ $req->status_badge['label'] }}</strong></td>
                    <td>{{ $req->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #64748b; font-style: italic;">No staff requisitions logged.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- IV. USER PRESENCE OVERVIEW --}}
    <div class="section-title">IV. System Active User Accounts Overview</div>
    <table class="audit-print-table">
        <thead>
            <tr>
                <th>Personnel Name</th>
                <th>Username</th>
                <th>System Role</th>
                <th>Department</th>
                <th>Rank / Title</th>
                <th style="width: 80px;">Account Status</th>
                <th style="width: 80px;">Activity Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $usr)
                <tr>
                    <td><strong>{{ $usr->name }}</strong></td>
                    <td style="font-family: monospace;">{{ '@' . $usr->username }}</td>
                    <td>{{ $usr->role }}</td>
                    <td>{{ $usr->department ?: 'Unassigned' }}</td>
                    <td>{{ $usr->rank ?: 'None' }}</td>
                    <td>{{ $usr->is_active ? 'Active' : 'Suspended' }}</td>
                    <td>{{ $usr->is_online ? 'Online' : 'Offline' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #64748b; font-style: italic;">No approved personnel found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Signature Sign-Off Block --}}
    @php
        $headOfStores = \App\Models\User::where('is_admin', true)->where('role', 'Head of Stores')->where('is_active', true)->first();
    @endphp
    <div class="signature-block">
        <div class="sig-col">
            <div class="sig-line" style="margin-top: 60px;">{{ $dg->name }}</div>
            <div class="sig-subtitle">Director General (DG)</div>
            <div style="font-size: 8px; color: #94a3b8; margin-top: 4px;">Executive Directorate</div>
        </div>
        <div class="sig-col" style="position: relative;">
            @if($headOfStores && $headOfStores->signature)
                <div style="height: 40px; display: flex; align-items: flex-end; justify-content: center; margin-bottom: -25px; position: relative; z-index: 5; pointer-events: none;">
                    <img src="{{ Storage::url($headOfStores->signature) }}" style="max-height: 55px; max-width: 180px; object-fit: contain; mix-blend-mode: multiply;" alt="Head of Stores Signature">
                </div>
            @else
                <div style="height: 40px;"></div>
            @endif
            <div class="sig-line" style="margin-top: 20px;">COMMANDER, CENTRAL STORES</div>
            <div class="sig-subtitle">Authorized Approving Authority</div>
            <div style="font-size: 8px; color: #94a3b8; margin-top: 4px;">Central Stores Command Unit</div>
        </div>
    </div>

</body>
</html>
