{{-- Developer Credit Tag - Bottom Right Corner & Visible ONLY on direct hover of the text location --}}
<footer class="app-system-footer" 
        style="margin-top: 3rem; padding: 1rem 1.5rem; border-top: 1px solid var(--border-color, rgba(226, 232, 240, 0.4)); display: flex; justify-content: flex-end; align-items: center; background: transparent; min-height: 45px; pointer-events: none;">
    <div class="dev-attribution-tag" 
         style="opacity: 0; visibility: hidden; transition: opacity 0.35s ease, visibility 0.35s ease, transform 0.35s ease; font-size: 0.75rem; color: var(--text-muted, #64748b); font-weight: 600; user-select: none; display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 10px; background: var(--bg-card, rgba(255,255,255,0.85)); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border: 1px solid var(--border-color, #e2e8f0); pointer-events: auto; cursor: default;">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.7;"><path d="m18 16 4-4-4-4"/><path d="m6 8-4 4 4 4"/><path d="m14.5 4-5 16"/></svg>
        <span>System developed by <strong style="color: var(--primary, #059669); font-weight: 800;">Adomako Emmanuel</strong></span>
    </div>
</footer>

<style>
/* Reveal developer attribution ONLY when hovering directly on the exact text badge location */
.dev-attribution-tag:hover {
    opacity: 1 !important;
    visibility: visible !important;
    transform: translateY(-2px);
}
</style>
