{{-- Blank Footer Region (Secret QR Code on Click/Double-Click) --}}
<footer class="app-system-footer" 
        onclick="showDeveloperQrModal()" 
        ondblclick="showDeveloperQrModal()" 
        style="margin-top: 3rem; padding: 1.5rem 0; min-height: 40px; border-top: 1px solid var(--border-color, rgba(226, 232, 240, 0.5)); cursor: default; user-select: none;">
</footer>

<!-- Minimalist QR Code Modal (Only QR Code image displayed on click/double-click) -->
<div id="developerQrModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); z-index: 999999; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s ease;">
    <div style="background: white; padding: 1.25rem; border-radius: 24px; text-align: center; box-shadow: 0 25px 70px rgba(0,0,0,0.3); position: relative; animation: devQrPop 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275); display: inline-block;">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=System%20developed%20by%20Adomako%20Emmanuel" 
             alt="Developer QR Code" 
             onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'200\' height=\'200\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23059669\' stroke-width=\'1.5\'><rect width=\'5\' height=\'5\' x=\'3\' y=\'3\' rx=\'1\'/><rect width=\'5\' height=\'5\' x=\'16\' y=\'3\' rx=\'1\'/><rect width=\'5\' height=\'5\' x=\'3\' y=\'16\' rx=\'1\'/><path d=\'M21 16h-3a2 2 0 0 0-2 2v3\'/><path d=\'M12 7v3a2 2 0 0 1-2 2H7\'/></svg>'"
             style="width: 200px; height: 200px; display: block; border-radius: 16px;">
    </div>
</div>

<style>
@keyframes devQrPop {
    0% { transform: scale(0.85); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}
</style>

<script>
function showDeveloperQrModal() {
    const modal = document.getElementById('developerQrModal');
    if (!modal) return;
    modal.style.display = 'flex';
    requestAnimationFrame(() => {
        modal.style.opacity = '1';
    });
}

function hideDeveloperQrModal() {
    const modal = document.getElementById('developerQrModal');
    if (!modal) return;
    modal.style.opacity = '0';
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
}

document.addEventListener('click', function(e) {
    const modal = document.getElementById('developerQrModal');
    if (modal && e.target === modal) {
        hideDeveloperQrModal();
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideDeveloperQrModal();
    }
});
</script>
