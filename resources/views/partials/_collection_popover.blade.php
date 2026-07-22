@auth
{{-- Approved Requisition Collection Pop-over Container --}}
<div id="approvedCollectionPopoverOverlay" style="display: none; position: fixed; inset: 0; z-index: 999999; align-items: center; justify-content: center; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); padding: 1.5rem;">
    <div style="background: var(--bg-card, #ffffff); border-radius: 28px; width: 100%; max-width: 520px; padding: 2.25rem; border: 1px solid var(--border-color, rgba(0,0,0,0.1)); box-shadow: 0 30px 90px rgba(0, 0, 0, 0.35); text-align: center; position: relative; animation: popoverSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1);">
        
        {{-- Icon header --}}
        <div style="width: 72px; height: 72px; background: rgba(16, 185, 129, 0.12); color: #10b981; border: 2px solid rgba(16, 185, 129, 0.25); border-radius: 22px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem; box-shadow: 0 10px 25px rgba(16, 185, 129, 0.2);">
            <i data-lucide="package-check" style="width: 36px; height: 36px;"></i>
        </div>

        <div style="font-size: 0.72rem; font-weight: 850; color: #10b981; text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 4px;">Requisition Approved</div>
        <h2 style="font-size: 1.5rem; font-weight: 900; color: var(--text-main, #0f172a); margin: 0 0 0.75rem; letter-spacing: -0.02em;">Awaiting Collection</h2>
        
        <p style="font-size: 0.95rem; color: var(--text-main, #1e293b); font-weight: 600; line-height: 1.55; margin: 0 0 1.5rem; background: rgba(16, 185, 129, 0.06); padding: 1rem 1.25rem; border-radius: 14px; border: 1px solid rgba(16, 185, 129, 0.2); text-align: center;">
            Your requisition request(s) has been <strong style="color: #047857;">approved</strong> and is <strong style="color: #047857;">awaiting collection</strong> from the store officer.
        </p>

        {{-- Approved Requisitions Summary Cards --}}
        <div id="popoverRequisitionsList" style="max-height: 220px; overflow-y: auto; text-align: left; margin-bottom: 1.75rem; display: flex; flex-direction: column; gap: 0.75rem; padding-right: 4px;">
            {{-- Injected dynamically --}}
        </div>

        {{-- Action Button --}}
        <button id="popoverOkayBtn" onclick="acknowledgeCollectionPopover()" style="width: 100%; padding: 0.9rem 2rem; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: white; border: none; border-radius: 14px; font-weight: 800; font-size: 0.95rem; cursor: pointer; box-shadow: 0 8px 24px rgba(22, 163, 74, 0.3); transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center; gap: 8px;" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 12px 28px rgba(22, 163, 74, 0.4)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 8px 24px rgba(22, 163, 74, 0.3)';">
            <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>
            <span>Okay</span>
        </button>
    </div>
</div>

<style>
    @keyframes popoverSlideIn {
        from { opacity: 0; transform: scale(0.92) translateY(12px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
</style>

<script>
(function() {
    let popoverKeysToAcknowledge = [];

    async function checkApprovedCollectionPopover() {
        try {
            const res = await fetch("{{ route('api.collection-popover-data', [], false) }}?_t=" + Date.now(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            if (!res.ok) return;
            const data = await res.json();

            if (data.has_popover && data.requisitions && data.requisitions.length > 0) {
                popoverKeysToAcknowledge = data.requisitions.map(r => r.key);
                
                const listContainer = document.getElementById('popoverRequisitionsList');
                if (listContainer) {
                    listContainer.innerHTML = data.requisitions.map(req => `
                        <div style="background: var(--bg-main, #f8fafc); border: 1px solid var(--border-color, #e2e8f0); border-radius: 14px; padding: 0.85rem 1rem; display: flex; flex-direction: column; gap: 4px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 0.85rem; font-weight: 900; color: var(--text-main, #0f172a);">${req.ref}</span>
                                <span style="font-size: 0.68rem; font-weight: 850; padding: 2px 8px; border-radius: 99px; background: ${req.status_color}18; color: ${req.status_color}; border: 1px solid ${req.status_color}30;">
                                    ${req.status_label}
                                </span>
                            </div>
                            <div style="font-size: 0.76rem; color: var(--text-muted, #64748b); font-weight: 600;">
                                Requester: <strong>${req.requester}</strong> (${req.department})
                            </div>
                            ${req.purpose ? `<div style="font-size: 0.74rem; color: var(--text-muted, #64748b); font-style: italic; margin-top: 2px;">"${req.purpose}"</div>` : ''}
                        </div>
                    `).join('');
                }

                const overlay = document.getElementById('approvedCollectionPopoverOverlay');
                if (overlay) {
                    overlay.style.display = 'flex';
                    if (window.lucide) lucide.createIcons();
                }
            }
        } catch (e) {
            console.error('Error loading collection popover data:', e);
        }
    }

    window.acknowledgeCollectionPopover = async function() {
        const overlay = document.getElementById('approvedCollectionPopoverOverlay');
        const btn = document.getElementById('popoverOkayBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = 'Acknowledging...';
        }

        try {
            await fetch("{{ route('api.acknowledge-collection-popover', [], false) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ keys: popoverKeysToAcknowledge })
            });
        } catch (e) {
            console.error('Error acknowledging collection popover:', e);
        }

        if (overlay) {
            overlay.style.display = 'none';
        }
    };

    document.addEventListener('DOMContentLoaded', checkApprovedCollectionPopover);
})();
</script>
@endauth
