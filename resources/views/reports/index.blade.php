@extends('layouts.dashboard')

@section('content')
<div class="animate-slide-up report-container">
    <div class="page-header header-mesh" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; padding: 2.5rem; border-radius: 28px; border: 1px solid var(--border-color); box-shadow: 0 10px 40px rgba(0,0,0,0.03); position: relative; overflow: hidden; background: var(--bg-card);">
        <div style="position: absolute; top: -100px; right: -50px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 60%); z-index: 0;"></div>
        <div style="position: relative; z-index: 1;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <span style="background: rgba(99, 102, 241, 0.1); color: var(--primary); font-size: 0.7rem; font-weight: 800; padding: 0.35rem 1rem; border-radius: 99px; text-transform: uppercase;">Reporting Engine</span>
                <span style="color: var(--text-muted); font-size: 0.85rem; font-weight: 700;">Audit & Validation Tool</span>
            </div>
            <h2 style="font-size: 2.5rem; font-weight: 950; color: var(--text-main); margin: 0; letter-spacing: -0.04em;">Analytical <span style="color: var(--primary);">Reports</span></h2>
            <p style="color: var(--text-muted); font-size: 1rem; font-weight: 600; margin-top: 6px;">Aggregate logistics data across multiple time periods for official auditing.</p>
        </div>

        <div style="display: flex; gap: 1rem; position: relative; z-index: 1;">
            <!-- Period Controls -->
            <div class="period-toggle-group">
                <a href="{{ route('reports.index', ['period' => 'daily']) }}" class="period-btn {{ $period === 'daily' ? 'active' : '' }}">Daily</a>
                <a href="{{ route('reports.index', ['period' => 'monthly']) }}" class="period-btn {{ $period === 'monthly' ? 'active' : '' }}">Monthly</a>
                <a href="{{ route('reports.index', ['period' => 'yearly']) }}" class="period-btn {{ $period === 'yearly' ? 'active' : '' }}">Yearly</a>
            </div>
        </div>
    </div>

    <!-- Generated Report Title Bar -->
    <div class="print-header" style="display: none; margin-bottom: 2rem; border-bottom: 4px solid #1e3a8a; padding-bottom: 20px; text-align: center;">
        <img src="{{ asset('img/NACOC1.png') }}" style="width: 110px; margin-bottom: 12px;">
        <h1 style="font-size: 26pt; font-family: 'Times New Roman', serif; margin: 0; letter-spacing: 1.5px; color: #1e3a8a;">NARCOTICS CONTROL COMMISSION</h1>
        <h3 style="font-size: 15pt; font-family: 'Arial', sans-serif; font-weight: bold; margin: 8px 0 0; color: #3b82f6; text-transform: uppercase;">Official Inventory Operations Report</h3>
        <p style="font-size: 12pt; font-family: 'Times New Roman', serif; margin: 10px 0 0; color: #64748b; font-weight: bold;">{{ strtoupper($dateLabel) }}</p>

        <div style="display: flex; justify-content: center; gap: 40px; margin-top: 15px; font-family: 'Courier New', monospace; font-size: 10pt; color: #333;">
            <div><strong>REF NO:</strong> NACOC/INV/{{ date('Y/m') }}/{{ str_pad(rand(1,999), 3, '0', STR_PAD_LEFT) }}</div>
            <div><strong>DATE PRINTED:</strong> {{ date('d M Y') }}</div>
            <div><strong>STATUS:</strong> <span style="color: #dc2626; font-weight: bold;">CERTIFIED CLASSIFIED</span></div>
        </div>
    </div>

    <div class="print-actions-bar" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
        <h3 class="print-date-label" style="font-size: 1.25rem; font-weight: 900; color: var(--text-main); margin: 0;">{{ $dateLabel }}</h3>
        <button onclick="triggerPrintMode()" class="btn-primary" style="padding: 0.75rem 1.5rem; border-radius: 14px; border: none; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 10px 20px rgba(99, 102, 241, 0.25);">
            <i data-lucide="printer" style="width: 18px;"></i> Export or Print
        </button>
    </div>

    <!-- Quick Stats -->
    <div class="stats-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
        <div class="glass-card stat-card">
            <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                <i data-lucide="package-plus"></i>
            </div>
            <div style="flex: 1;">
                <div class="stat-label">Total Received Vol.</div>
                <div class="stat-value">{{ number_format((float)$totalReceivedQty) }} <span class="stat-unit">Units</span></div>
                <div class="stat-subtitle">Across {{ $totalReceivedBatches }} Registered Batches</div>
            </div>
        </div>

        <div class="glass-card stat-card">
            <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                <i data-lucide="package-minus"></i>
            </div>
            <div style="flex: 1;">
                <div class="stat-label">Total Issued Vol.</div>
                <div class="stat-value">{{ number_format((float)$totalIssuedQty) }} <span class="stat-unit">Units</span></div>
                <div class="stat-subtitle">Across {{ $totalIssuedBatches }} Disbursement Records</div>
            </div>
        </div>

        <div class="glass-card stat-card">
            <div class="stat-icon" style="background: rgba(99, 102, 241, 0.1); color: var(--primary);">
                <i data-lucide="activity"></i>
            </div>
            <div style="flex: 1;">
                <div class="stat-label">Net Movement</div>
                <div class="stat-value" style="color: var(--primary);">{{ number_format(max(0, (float)$totalReceivedQty - (float)$totalIssuedQty)) }} <span class="stat-unit">Units</span></div>
                <div class="stat-subtitle">Theoretical Surplus in Period</div>
            </div>
        </div>
    </div>

    <!-- Customizable Report Writer -->
    <div style="margin-bottom: 3rem; position: relative;">
        <div class="narrative-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;" class="hide-in-print">
            <div>
                <h4 style="font-size: 1.1rem; font-weight: 900; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="pen-tool" style="width: 20px; color: var(--primary);"></i>
                    Auditor's Narrative
                </h4>
                <p style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600; margin: 4px 0 0;">Add custom contextual remarks to be included in the final print generation.</p>
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <button type="button" onclick="autoGenerateReport()" class="btn-primary" style="padding: 0.5rem 1rem; border-radius: 12px; border: none; background: rgba(16, 185, 129, 0.1); color: #10b981; font-weight: 800; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="sparkles" style="width: 16px;"></i> Auto-Generate
                </button>
                <button type="button" onclick="clearNarrative()" class="btn-secondary" style="padding: 0.5rem 1.25rem; border-radius: 12px; border: none; background: rgba(239, 68, 68, 0.08); color: #ef4444; font-weight: 800; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.3s ease;" onmouseover="this.style.background='rgba(239, 68, 68, 0.15)'; this.style.transform='translateY(-1px)'" onmouseout="this.style.background='rgba(239, 68, 68, 0.08)'; this.style.transform='translateY(0)'">
                    <i data-lucide="rotate-ccw" style="width: 16px;"></i> Clear Setup
                </button>
            </div>
        </div>
        
        <div class="composer-container">
            <textarea id="reportNarrative" class="modern-textarea" placeholder="Start typing organizational goals, transaction anomalies, budget summaries, or operational conclusions for this period..."></textarea>
            <div id="printNarrativeView" class="print-only"></div>
        </div>
    </div>

    <!-- Detail Grids -->
    <div style="display: grid; grid-template-columns: 1fr; gap: 2.5rem;" class="detail-grids">
        
        <!-- Receivals Activity -->
        <div class="glass-card print-table-wrapper" style="padding: 1.5rem; border-radius: 16px; border: 1px solid var(--border-color);">
            <h4 style="font-size: 1.1rem; font-weight: 800; color: #10b981; margin: 0 0 1rem; display: flex; align-items: center; gap: 8px; border-bottom: 2px dashed rgba(16, 185, 129, 0.2); padding-bottom: 12px; text-transform: uppercase;">
                <i data-lucide="arrow-down-circle"></i> Stock Receipts Log
            </h4>
            
            @if($recentReceivals->count() > 0)
                <div style="max-height: 400px; overflow-y: auto;" class="custom-scroll table-responsive">
                    <table class="formal-table" style="width: 100%; text-align: left; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Item Description</th>
                                <th>Supplier / Source</th>
                                <th style="text-align: right;">Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentReceivals as $rec)
                            <tr>
                                <td data-label="Date" style="white-space: nowrap;">{{ \Carbon\Carbon::parse($rec->entry_date)->format('M d, Y') }}</td>
                                <td data-label="Item Description" style="font-weight: 600;">{{ $rec->description }}</td>
                                <td data-label="Supplier / Source">{{ preg_replace('/\s\[.*\]$/', '', $rec->supplier_name ?: 'System') }}</td>
                                <td data-label="Quantity" style="text-align: right; font-weight: 800; color: #10b981;">{{ $rec->qty }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="padding: 2rem; text-align: center; color: var(--text-muted);">
                    <em>No receipts located for this period.</em>
                </div>
            @endif
        </div>

        <!-- Issue Activity -->
        <div class="glass-card print-table-wrapper" style="padding: 1.5rem; border-radius: 16px; border: 1px solid var(--border-color);">
            <h4 style="font-size: 1.1rem; font-weight: 800; color: #f59e0b; margin: 0 0 1rem; display: flex; align-items: center; gap: 8px; border-bottom: 2px dashed rgba(245, 158, 11, 0.2); padding-bottom: 12px; text-transform: uppercase;">
                <i data-lucide="arrow-up-circle"></i> Disbursement & Allocations
            </h4>

            @if($recentIssues->count() > 0)
                <div style="max-height: 400px; overflow-y: auto;" class="custom-scroll table-responsive">
                    <table class="formal-table" style="width: 100%; text-align: left; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Item Description</th>
                                <th>Beneficiary / Department</th>
                                <th style="text-align: right;">Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentIssues as $iss)
                            <tr>
                                <td data-label="Date" style="white-space: nowrap;">{{ \Carbon\Carbon::parse($iss->entry_date)->format('M d, Y') }}</td>
                                <td data-label="Item Description" style="font-weight: 600;">{{ $iss->description }}</td>
                                <td data-label="Beneficiary">{{ $iss->beneficiary }}</td>
                                <td data-label="Quantity" style="text-align: right; font-weight: 800; color: #f59e0b;">{{ $iss->quantity }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="padding: 2rem; text-align: center; color: var(--text-muted);">
                    <em>No disbursements processed.</em>
                </div>
            @endif
        </div>
    </div>

    <!-- Electronic Overprint Signage (Print Mode Only) -->
    <div class="print-only print-footer" style="display: none; margin-top: 50px;">
        <div style="display: flex; justify-content: space-between;">
            <div style="text-align: center; width: 250px;">
                <div style="border-bottom: 1px solid #000; height: 50px;"></div>
                <div style="font-size: 10pt; margin-top: 5px;">Report Composer / Author</div>
                <div style="font-size: 10pt; font-weight: bold;">{{ auth()->user()->name }}</div>
            </div>
            <div style="text-align: center; width: 250px;">
                <div style="border-bottom: 1px solid #000; height: 50px;"></div>
                <div style="font-size: 10pt; margin-top: 5px;">Head of Logistics</div>
            </div>
        </div>
        <div style="text-align: center; margin-top: 30px; font-size: 8pt; color: #555;">
            Generated from NACOC Secure Framework at {{ date('H:i T, d M Y') }}
        </div>
    </div>

</div>

<style>
    /* Unique Localized CSS for Reports */
    .period-toggle-group {
        display: flex;
        background: var(--bg-main);
        border-radius: 14px;
        padding: 4px;
        border: 1px solid var(--border-color);
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
    }

    .period-btn {
        padding: 0.65rem 1.75rem;
        border-radius: 10px;
        font-weight: 800;
        font-size: 0.9rem;
        color: var(--text-muted);
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .period-btn.active {
        background: var(--bg-card);
        color: var(--primary);
        box-shadow: 0 4px 10px rgba(0,0,0,0.06);
        border: 1px solid var(--border-color);
    }

    .period-btn:not(.active):hover {
        color: var(--text-main);
        background: rgba(99, 102, 241, 0.05);
    }

    .stat-card {
        padding: 2rem;
        border-radius: 24px;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        transition: 0.3s;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.05);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-icon i {
        width: 28px;
        height: 28px;
    }

    .stat-label {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--text-muted);
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .stat-value {
        font-size: 2.2rem;
        font-weight: 950;
        color: var(--text-main);
        line-height: 1;
    }

    .stat-unit {
        font-size: 1.1rem;
        color: var(--text-muted);
        font-weight: 700;
    }

    .stat-subtitle {
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 600;
        margin-top: 8px;
    }

    .modern-textarea {
        width: 100%;
        min-height: 200px;
        padding: 1.5rem;
        border-radius: 18px;
        border: 2px solid var(--border-color);
        background: var(--bg-main);
        color: var(--text-main);
        font-family: 'Inter', sans-serif;
        font-size: 1rem;
        line-height: 1.7;
        resize: vertical;
        outline: none;
        transition: all 0.3s;
    }

    .modern-textarea:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 5px rgba(99, 102, 241, 0.1);
        background: var(--bg-card);
    }

    .activity-row-modern {
        display: flex;
        justify-content: space-between;
        padding: 1.25rem 0;
        border-bottom: 1px solid var(--border-color);
    }

    .activity-row-modern:last-child {
        border-bottom: none;
    }

    .formal-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: transparent;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
    }
    
    .formal-table th {
        padding: 16px 20px;
        background: rgba(99, 102, 241, 0.06);
        color: var(--text-main);
        font-weight: 800;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--border-color);
        text-align: left;
    }
    
    .formal-table td {
        padding: 16px 20px;
        border-bottom: 1px dashed var(--border-color);
        color: var(--text-main);
        font-size: 0.95rem;
        transition: background 0.3s ease;
    }

    .formal-table tbody tr:last-child td {
        border-bottom: none;
    }

    .formal-table tbody tr:hover td {
        background: rgba(99, 102, 241, 0.04);
    }

    /* PREMIUM MOBILE OPTIMIZATIONS */
    @media (max-width: 1024px) {
        .page-header {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 2rem !important;
            padding: 2rem !important;
        }
        .page-header h2 { font-size: 2rem !important; }
        .period-toggle-group { width: 100% !important; justify-content: space-between; }
        .period-btn { flex: 1; text-align: center; padding: 0.65rem 0.5rem !important; font-size: 0.8rem !important; }
    }

    @media (max-width: 768px) {
        .report-container { padding: 0.5rem !important; }
        .page-header { padding: 1.5rem !important; margin-bottom: 1.5rem !important; }
        
        .print-actions-bar {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 1rem !important;
        }
        .print-actions-bar button { width: 100% !important; justify-content: center; }

        .narrative-header {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 1rem !important;
        }
        .narrative-header div:last-child {
            width: 100%;
            display: grid !important;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
        }
        .narrative-header button { width: 100% !important; justify-content: center; padding: 0.6rem !important; font-size: 0.75rem !important; }
        
        .stat-card { padding: 1.5rem !important; flex-direction: column; text-align: center; }
        .stat-icon { margin: 0 auto 1rem; }
        .stat-value { font-size: 1.8rem !important; }

        /* FORMAL TABLE CARD VIEW */
        .formal-table { border: none !important; border-radius: 0 !important; box-shadow: none !important; }
        .formal-table thead { display: none !important; }
        .formal-table tbody { display: block !important; }
        .formal-table tr {
            display: block !important;
            background: var(--bg-card) !important;
            margin-bottom: 1.5rem !important;
            padding: 1.5rem !important;
            border-radius: 24px !important;
            border: 1px solid var(--border-color) !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04) !important;
            position: relative;
            overflow: hidden;
        }
        .formal-table tr::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--primary);
        }
        .formal-table td {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            padding: 0.75rem 0 !important;
            border-bottom: 1px solid rgba(0,0,0,0.03) !important;
            text-align: right !important;
            width: 100% !important;
            font-size: 0.9rem !important;
        }
        .formal-table td:last-child { border-bottom: none !important; }
        .formal-table td::before {
            content: attr(data-label);
            font-weight: 800;
            color: var(--text-muted);
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-right: 1rem;
            text-align: left !important;
        }
    }

    /* Print Architecture */
    @media print {
        @page { size: portrait; margin: 15mm; }
        
        body { 
            background: white !important;
            zoom: 1 !important;
            color: #1e293b !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .sidebar, .top-nav, .period-toggle-group, button, .zoom-controls { 
            display: none !important; 
        }

        .main-wrapper { 
            margin: 0 !important; 
            padding: 0 !important; 
            box-shadow: none !important; 
            background: transparent !important;
        }

        .report-container {
            width: 100% !important;
            max-width: none !important;
        }

        .print-header { display: block !important; }
        .page-header { display: none !important; } 
        .modern-textarea { display: none !important; }
        .hide-in-print { display: none !important; }

        #printNarrativeView {
            display: block !important;
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.6;
            padding: 0;
            margin-top: 5px;
            margin-bottom: 35px;
            white-space: pre-line;
            border: none !important;
            text-align: justify;
            color: #000 !important;
        }

        .print-date-label { 
            color: #1e3a8a !important; 
            font-family: 'Times New Roman', serif !important; 
            font-size: 16pt !important; 
            margin-bottom: 15px !important; 
        }

        /* Redesign Stats into a Formal Table-Like Row */
        .stats-container {
            display: flex !important;
            border-top: 3px solid #1e3a8a !important;
            border-bottom: 3px solid #1e3a8a !important;
            padding: 15px 0 !important;
            gap: 0 !important;
            margin-bottom: 30px !important;
            background-color: #f8fafc !important;
        }

        .stat-card {
            flex: 1;
            padding: 0 15px !important;
            border: none !important;
            border-right: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
            background: transparent !important;
            border-radius: 0 !important;
            text-align: center;
        }
        .stat-card:last-child { border-right: none !important; }
        .stat-card .stat-icon { display: none !important; }
        .stat-label { color: #475569 !important; font-family: 'Arial', sans-serif; font-size: 10pt !important; margin-bottom: 5px; font-weight: bold; }
        .stat-value { color: #1e3a8a !important; font-family: 'Times New Roman', serif; font-size: 20pt !important; font-weight: bold; }
        .stat-subtitle { display: none !important; } /* Hide the subtitle in print to keep it clean */

        /* Redesign Grids into Ledgers */
        .detail-grids {
            display: block !important;
        }

        .detail-grids > .glass-card {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin-bottom: 35px !important;
            border-radius: 0 !important;
            background: transparent !important;
        }

        .detail-grids h4 {
            color: #1e3a8a !important;
            border-bottom: 2px solid #3b82f6 !important;
            padding-bottom: 5px !important;
            font-family: 'Times New Roman', serif !important;
            font-size: 14pt !important;
            font-weight: bold;
            margin-bottom: 10px !important;
            border-top: none !important;
        }
        .detail-grids h4 i { display: inline-block !important; margin-right: 5px; width: 18px; color: #ef4444 !important; }

        .formal-table { width: 100% !important; border-collapse: collapse !important; border: none !important; border-top: 3px solid #1e3a8a !important; border-bottom: 3px solid #1e3a8a !important; margin-top: 5px !important; }
        .formal-table th { border: none !important; border-bottom: 2px solid #94a3b8 !important; background: transparent !important; color: #1e293b !important; font-family: 'Arial', sans-serif !important; font-size: 10pt !important; padding: 12px 8px !important; font-weight: 900 !important; text-align: left; text-transform: uppercase; }
        .formal-table td { border: none !important; border-bottom: 1px solid #e2e8f0 !important; color: #334155 !important; font-family: 'Times New Roman', serif !important; font-size: 11pt !important; padding: 10px 8px !important; }
        .custom-scroll { max-height: none !important; overflow: visible !important; }
        .print-table-wrapper { border: none !important; padding: 0 !important; margin-bottom: 30px !important; border-radius: 0 !important; }

        .print-footer { display: block !important; page-break-inside: avoid; margin-top: 60px !important; }
    }
</style>

<script>
    function triggerPrintMode() {
        const narrativeContents = document.getElementById('reportNarrative').value;
        const printView = document.getElementById('printNarrativeView');

        if (narrativeContents.trim() === '') {
            printView.innerHTML = "<em>No auditor's narrative supplied for this time period.</em>";
        } else {
            // Escape HTML and preserve line breaks visually
            const safeContent = narrativeContents.replace(/</g, "&lt;").replace(/>/g, "&gt;");
            printView.innerHTML = `<strong>Auditor's Remarks:</strong><br>` + safeContent;
        }

        // Trigger native print flow
        window.print();
    }

    function autoGenerateReport() {
        const periodLabel = "{{ $period === 'daily' ? 'today' : ($period === 'monthly' ? 'this month' : 'this year') }}";
        const totalRec = "{{ number_format((float)$totalReceivedQty) }}";
        const totalIss = "{{ number_format((float)$totalIssuedQty) }}";
        const recBatches = "{{ $totalReceivedBatches }}";
        const issBatches = "{{ $totalIssuedBatches }}";
        const netMovement = "{{ number_format(max(0, (float)$totalReceivedQty - (float)$totalIssuedQty)) }}";
        const dateStamp = "{{ \Carbon\Carbon::now()->format('F j, Y') }}";

        const template = `EXECUTIVE LOGISTICS SUMMARY - ${dateStamp}

During this reporting period (${periodLabel}), the logistics and inventory framework processed significant asset movements crucial to our operational readiness.

RECEIPTS & ACQUISITIONS:
We successfully recorded the inbound reception of ${totalRec} units distributed across ${recBatches} discrete batches. These acquisitions have been formally logged into the secure ledger, ensuring our reserves remain adequately stocked.

DISBURSEMENTS & ALLOCATIONS:
In support of ongoing operations and departmental requirements, the logistics division issued a total of ${totalIss} units spanning ${issBatches} distinct disbursement events. All allocations were verified against authorized requisitions to maintain strict supply chain integrity.

OVERALL THEORETICAL STOCK STATUS:
Factoring in the aggregation of ${totalRec} incoming units against the ${totalIss} allocated units, the facility noted a net functional surplus of ${netMovement} units for ${periodLabel}. This metric solidifies our adherence to conservative inventory retention protocols.

CONCLUSION:
No gross anomalies or unaccounted systemic variances were detected during this audit window. The inventory ecosystem remains balanced, verifiable, and prepared for subsequent logistical cycles.

- Automatically generated by NACOC Intelligence System`;

        const textarea = document.getElementById('reportNarrative');
        textarea.value = template;
        
        textarea.style.transition = 'all 0.3s ease';
        textarea.style.borderColor = '#10b981';
        
        setTimeout(() => {
            textarea.style.borderColor = 'var(--border-color)';
        }, 1500);
    }

    function clearNarrative() {
        document.getElementById('reportNarrative').value = '';
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>
@endsection
