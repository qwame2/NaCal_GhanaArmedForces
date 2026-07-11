<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internal Audit Ledger Report - NACOC Central Store</title>
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
        <div class="doc-title">Internal Audit & Verification Ledger Report</div>
    </div>

    {{-- Metadata --}}
    <div class="meta-grid">
        <div>
            <div class="meta-item"><span>Generated By:</span> {{ $auditor->name }}</div>
            <div class="meta-item"><span>Department:</span> {{ $auditor->department ?? 'Internal Audit' }}</div>
            <div class="meta-item"><span>Rank / Role:</span> {{ $auditor->rank ? $auditor->rank . ' (Auditor)' : 'Auditor' }}</div>
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
            <div class="meta-item"><span>Audit Security Level:</span> CONFIDENTIAL</div>
        </div>
    </div>

    {{-- I. SYSTEM AUDIT TRAIL LOGS --}}
    <div class="section-title">I. System Audit Trail (Archived Event Log)</div>
    <table class="audit-print-table">
        <thead>
            <tr>
                <th style="width: 110px;">Timestamp</th>
                <th style="width: 100px;">User</th>
                <th style="width: 80px;">Category</th>
                <th style="width: 140px;">Action Event</th>
                <th>Detailed Description</th>
                <th style="width: 60px;">Severity</th>
            </tr>
        </thead>
        <tbody>
            @forelse($systemLogs as $log)
                <tr>
                    <td style="font-family: monospace;">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    <td>
                        @php
                            $roleDisplay = 'System Automated';
                            if ($log->user) {
                                if ($log->user->is_admin) {
                                    $roleDisplay = 'Head of Stores';
                                } elseif ($log->user->role === 'Main Admin') {
                                    $roleDisplay = 'Head of Admin(Authorizer)';
                                } elseif ($log->user->role === 'Department Head') {
                                    if ($log->user->department === 'Human Resource Management Department') {
                                        $roleDisplay = 'Dept Head(HR)';
                                    } elseif ($log->user->department === 'Welfare Department') {
                                        $roleDisplay = 'Head of Welfare';
                                    } else {
                                        $roleDisplay = 'Dept Head(' . $log->user->department . ')';
                                    }
                                } elseif ($log->user->role === 'Officer') {
                                    $roleDisplay = 'Store Officer';
                                } else {
                                    $roleDisplay = $log->user->role;
                                }
                                if ($log->user->rank) {
                                    $roleDisplay .= ' (' . $log->user->rank . ')';
                                }
                            }
                        @endphp
                        <strong>{{ $log->user ? $log->user->name : 'System Automated' }}</strong><br>
                        <span style="font-size: 8px; color: #4f46e5; font-weight: 800;">{{ $roleDisplay }}</span><br>
                        <span style="font-size: 8px; color: #64748b;">{{ $log->user ? '@' . $log->user->username : '' }}</span>
                    </td>
                    <td>{{ $log->event_type }}</td>
                    <td style="font-family: monospace; font-weight: 700; color: #334155;">{{ $log->action }}</td>
                    <td>{{ $log->friendly_description }}</td>
                    <td style="font-weight: 800; font-size: 8.5px; text-transform: uppercase;">{{ $log->severity }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #64748b; font-style: italic;">No audit trail events logged for this date range.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>

    {{-- II. RECEIVED ITEMS LOG --}}
    <div class="section-title">II. Received Items Ledger (Supplier / Donor Shipments)</div>
    <table class="audit-print-table">
        <thead>
            <tr>
                <th style="width: 70px;">Entry Date</th>
                <th style="width: 60px;">Batch ID</th>
                <th>Item Description</th>
                <th style="width: 120px;">Category Standard</th>
                <th style="width: 80px; text-align: right;">Qty Received</th>
                <th style="width: 80px; text-align: right;">Current Stock</th>
                <th style="width: 60px; text-align: right;">Discrepancy</th>
                <th>Supplier / Donor Registry Name</th>
            </tr>
        </thead>
        <tbody>
            @forelse($receivedItems as $item)
                <tr>
                    <td>{{ $item->entry_date ? \Carbon\Carbon::parse($item->entry_date)->format('d/m/Y') : '-' }}</td>
                    <td style="font-family: monospace; font-weight: 700;">#{{ $item->batch_id }}</td>
                    <td><strong>{{ $item->description }}</strong></td>
                    <td>{{ $ledgeMap[$item->ledge_category] ?? $item->ledge_category }}</td>
                    <td style="text-align: right; font-weight: 700;">{{ number_format($item->qty) }} {{ $item->unit }}</td>
                    <td style="text-align: right; font-weight: 700;">{{ number_format($item->stock_balance) }} {{ $item->unit }}</td>
                    <td style="text-align: right; font-weight: 700; color: {{ $item->variance > 0 ? '#ef4444' : '#0f172a' }};">{{ number_format($item->variance) }}</td>
                    <td>{{ $item->supplier_name ?: ($item->donor_name ?: 'System') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #64748b; font-style: italic;">No received items logged in this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- III. ISSUED ITEMS LOG --}}
    <div class="section-title">III. Issued Items Ledger (Deliveries & Loans)</div>
    <table class="audit-print-table">
        <thead>
            <tr>
                <th style="width: 70px;">Date Issued</th>
                <th>Item Description</th>
                <th style="width: 100px;">Category</th>
                <th style="width: 80px; text-align: right;">Qty Issued</th>
                <th>Beneficiary Department / Officer</th>
                <th style="width: 70px;">Issuance Type</th>
                <th>Requisition Authority</th>
            </tr>
        </thead>
        <tbody>
            @forelse($issuedItems as $item)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->issuance_date)->format('d/m/Y') }}</td>
                    <td><strong>{{ $item->description }}</strong></td>
                    <td>{{ $ledgeMap[$item->ledge_category] ?? $item->ledge_category }}</td>
                    <td style="text-align: right; font-weight: 700; vertical-align: middle;">
                        @if($item->issuance_type === 'Temporary' && $item->total_returned > 0)
                            @if($item->quantity == 0)
                                <span style="color: #16a34a;">{{ number_format($item->total_returned) }} {{ $item->unit }}</span><br>
                                <span style="font-size: 7.5px; color: #16a34a; font-weight: 800; text-transform: uppercase;">[Returned]</span>
                            @else
                                <span>{{ number_format($item->quantity + $item->total_returned) }} {{ $item->unit }}</span><br>
                                <span style="font-size: 7.5px; color: #d97706; font-weight: 800; text-transform: uppercase;">[Partial Return]</span><br>
                                <span style="font-size: 7px; color: #64748b; font-weight: 600;">({{ number_format($item->quantity) }} outstanding)</span>
                            @endif
                        @else
                            {{ number_format($item->quantity) }} {{ $item->unit }}
                        @endif
                    </td>
                    <td><strong>{{ $item->beneficiary }}</strong></td>
                    <td>{{ $item->issuance_type }}</td>
                    <td style="color: #64748b; font-size: 8.5px; line-height: 1.3; vertical-align: middle;">
                        @if($item->origin_approved_by || $item->stores_approved_by || $item->dg_approved_by || $item->final_approved_by || $item->store_officer_name)
                            @if($item->origin_approved_by)
                                <div>{{ $item->origin_approved_by }} <span style="font-size: 7.5px; font-weight: 800;">(Dept Head)</span></div>
                            @endif
                            @if($item->stores_approved_by)
                                <div style="margin-top: 1.5px;">{{ $item->stores_approved_by }} <span style="font-size: 7.5px; font-weight: 800;">(Head of Admin(Authorizer))</span></div>
                            @endif
                            @if($item->dg_approved_by)
                                <div style="margin-top: 1.5px;">{{ $item->dg_approved_by }} <span style="font-size: 7.5px; font-weight: 800;">(Director General)</span></div>
                            @endif
                            @if($item->final_approved_by)
                                <div style="margin-top: 1.5px;">{{ $item->final_approved_by }} <span style="font-size: 7.5px; font-weight: 800;">(Head of Stores)</span></div>
                            @endif
                            @if($item->store_officer_name)
                                <div style="margin-top: 1.5px;">{{ $item->store_officer_name }} <span style="font-size: 7.5px; font-weight: 800;">(Store Officer)</span></div>
                            @endif
                        @else
                            {{ $item->authority ?: 'N/A' }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #64748b; font-style: italic;">No issued items logged in this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- IV. RETURNED ITEMS LOG --}}
    <div class="section-title">IV. Returned Items Category (Temporary Loan Returns)</div>
    <table class="audit-print-table">
        <thead>
            <tr>
                <th style="width: 100px;">Date Returned</th>
                <th>Item Description</th>
                <th style="width: 100px;">Category</th>
                <th style="width: 80px; text-align: right;">Qty Returned</th>
                <th>Borrowing Department</th>
                <th>Auditor Verification Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($returnedItems as $item)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->return_date)->format('d/m/Y') }}</td>
                    <td><strong>{{ $item->description }}</strong></td>
                    <td>{{ $ledgeMap[$item->ledge_category] ?? $item->ledge_category }}</td>
                    <td style="text-align: right; font-weight: 700;">{{ number_format($item->returned_qty) }}</td>
                    <td>{{ $item->beneficiary }}</td>
                    <td>{{ $item->remarks ?: 'Verified returned in clean condition.' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #64748b; font-style: italic;">No returned assets logged for this period.</td>
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
            <div class="sig-line" style="margin-top: 60px;">{{ $auditor->name }}</div>
            <div class="sig-subtitle">Reporting Auditor</div>
            <div style="font-size: 8px; color: #94a3b8; margin-top: 4px;">Internal Audit & Oversight Unit</div>
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
            <div style="font-size: 8px; color: #94a3b8; margin-top: 4px;">Strategic Command Oversight Unit</div>
        </div>
    </div>

</body>
</html>
