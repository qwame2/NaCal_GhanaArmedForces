@if($sras->isEmpty())
    @if(request()->anyFilled(['search', 'status', 'delivery_type']))
        <div class="glass-card" style="border-radius: 24px; padding: 4rem 2rem; text-align: center;">
            <i data-lucide="search" style="width: 64px; height: 64px; color: var(--text-muted); margin: 0 auto 1rem; display: block; opacity: 0.4;"></i>
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin: 0 0 0.5rem;">No Matching SRAs Found</h3>
            <p style="color: var(--text-muted); margin: 0 0 1.5rem;">Your query returned no results. Try adjusting your search term or filter options.</p>
        </div>
    @else
        <div class="glass-card" style="border-radius: 24px; padding: 4rem 2rem; text-align: center;">
            <i data-lucide="file-plus" style="width: 64px; height: 64px; color: var(--text-muted); margin: 0 auto 1rem; display: block; opacity: 0.4;"></i>
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin: 0 0 0.5rem;">No SRAs Submitted Yet</h3>
            <p style="color: var(--text-muted); margin: 0 0 1.5rem;">You haven't submitted any Stores Received Advice forms.</p>
            <a href="{{ route('service-sra.create') }}" class="btn-primary" style="padding: 0.85rem 2rem; border: none; border-radius: 14px; cursor: pointer; background: var(--primary); color: white; font-weight: 800; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                <i data-lucide="plus-circle" style="width: 16px;"></i> Submit First SRA
            </a>
        </div>
    @endif
@else
    <div class="glass-card" style="border-radius: 24px; overflow: hidden; padding: 0;">
        <div style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1rem; font-weight: 800; color: var(--text-main);">
                All Submissions ({{ $sras->total() }})
            </h3>
        </div>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: var(--bg-main);">
                        <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">SRA No.</th>
                        <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">Supplier</th>
                        <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">Delivery</th>
                        <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">Date</th>
                        <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: left;">Status</th>
                        <th style="padding: 0.85rem 1.5rem; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sras as $sra)
                    @php $badge = $sra->status_badge; @endphp
                    <tr class="sra-table-row" data-sra-id="{{ $sra->id }}" data-status="{{ $sra->status }}" data-admin-status="{{ $sra->admin_status }}">
                        <td style="padding: 1rem 1.5rem; font-weight: 800; color: var(--primary); font-size: 0.85rem;">
                            {{ $sra->sra_number }}
                        </td>
                        <td style="padding: 1rem 1.5rem;">
                            <div style="font-weight: 700; color: var(--text-main); font-size: 0.88rem;">{{ $sra->supplier_name }}</div>
                            @if($sra->vehicle_number)
                                <div style="font-size: 0.72rem; color: var(--text-muted);">Vehicle: {{ $sra->vehicle_number }}</div>
                            @endif
                        </td>
                        <td style="padding: 1rem 1.5rem;">
                            <span class="sra-status-badge" style="background: {{ $sra->delivery_type === 'full' ? 'rgba(5,150,105,0.1)' : 'rgba(5,150,105,0.1)' }}; color: {{ $sra->delivery_type === 'full' ? '#059669' : '#059669' }};">
                                {{ ucfirst($sra->delivery_type) }}
                            </span>
                        </td>
                        <td style="padding: 1rem 1.5rem; font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">
                            {{ $sra->date_of_delivery->format('d M Y') }}
                        </td>
                        <td style="padding: 1rem 1.5rem;">
                            <span class="sra-status-badge" style="background: {{ $badge['bg'] }}; color: {{ $badge['color'] }};">
                                ● {{ $badge['label'] }}
                            </span>
                        </td>
                        <td style="padding: 1rem 1.5rem; text-align: center;">
                            @if($sra->status === 'approved')
                                <a href="{{ route('service-sra.receipt', $sra->id) }}" target="_blank"
                                   style="display: inline-flex; align-items: center; gap: 6px; padding: 0.55rem 1.15rem; background: rgba(5,150,105,0.1); color: #059669; border: 1px solid rgba(5,150,105,0.25); border-radius: 10px; font-size: 0.78rem; font-weight: 800; text-decoration: none; transition: all 0.2s;"
                                   onmouseover="this.style.background='rgba(5,150,105,0.2)'" onmouseout="this.style.background='rgba(5,150,105,0.1)'">
                                    <i data-lucide="download" style="width: 14px;"></i> Download Receipt
                                </a>
                            @else
                                <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">
                                    @if($sra->status === 'declined')
                                        <i data-lucide="x-circle" style="width: 13px; color: #ef4444;"></i>
                                        Declined{{ $sra->admin_status === 'declined' ? ' by Admin' : ' by Stores' }}
                                    @else
                                        <i data-lucide="clock" style="width: 13px; color: var(--primary);"></i>
                                        Pending Approval
                                    @endif
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($sras->hasPages())
    <div id="sra-pagination" style="padding:1.25rem 1.5rem;border:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;background:var(--bg-card);border-radius:16px;box-shadow:0 4px 20px rgba(15,23,42,0.04);margin-top:1rem;">
        <div style="font-size:0.82rem;font-weight:700;color:var(--text-muted);">
            Showing
            <span style="color:var(--text-main);font-weight:900;">{{ $sras->firstItem() ?? 0 }}</span>–<span style="color:var(--text-main);font-weight:900;">{{ $sras->lastItem() ?? 0 }}</span>
            of <span style="color:var(--text-main);font-weight:900;">{{ $sras->total() }}</span> entries
        </div>
        <div style="display:flex;align-items:center;gap:0.4rem;">
            @if($sras->onFirstPage())
                <span style="padding:0.45rem 0.9rem;border-radius:8px;background:var(--bg-main);color:var(--text-muted);font-size:0.78rem;font-weight:800;border:1px solid var(--border-color);opacity:.5;cursor:not-allowed;">‹ Prev</span>
            @else
                <a href="#" data-page="{{ $sras->currentPage() - 1 }}" class="ajax-sra-page-btn" style="padding:0.45rem 0.9rem;border-radius:8px;background:var(--bg-card);color:var(--text-main);font-size:0.78rem;font-weight:800;border:1px solid var(--border-color);text-decoration:none;transition:0.2s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)';this.style.color='var(--text-main)'">‹ Prev</a>
            @endif
            @foreach($sras->getUrlRange(max(1,$sras->currentPage()-2),min($sras->lastPage(),$sras->currentPage()+2)) as $page => $url)
                @if($page == $sras->currentPage())
                    <span style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:8px;background:var(--primary);color:white;font-size:0.82rem;font-weight:900;">{{ $page }}</span>
                @else
                    <a href="#" data-page="{{ $page }}" class="ajax-sra-page-btn" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:8px;background:var(--bg-card);color:var(--text-main);font-size:0.82rem;font-weight:800;border:1px solid var(--border-color);text-decoration:none;transition:0.2s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)';this.style.color='var(--text-main)'">{{ $page }}</a>
                @endif
            @endforeach
            @if($sras->hasMorePages())
                <a href="#" data-page="{{ $sras->currentPage() + 1 }}" class="ajax-sra-page-btn" style="padding:0.45rem 0.9rem;border-radius:8px;background:var(--bg-card);color:var(--text-main);font-size:0.78rem;font-weight:800;border:1px solid var(--border-color);text-decoration:none;transition:0.2s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)';this.style.color='var(--text-main)'">Next ›</a>
            @else
                <span style="padding:0.45rem 0.9rem;border-radius:8px;background:var(--bg-main);color:var(--text-muted);font-size:0.78rem;font-weight:800;border:1px solid var(--border-color);opacity:.5;cursor:not-allowed;">Next ›</span>
            @endif
        </div>
    </div>
    @endif
@endif
