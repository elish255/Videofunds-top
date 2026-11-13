<?php
// Shared site footer and floating support links for all public pages.
?>
<style>
html,body{min-height:100%}
body{min-height:100vh;display:flex;flex-direction:column}
.vf-site-footer{background:#68162e;color:#fff;width:100%;margin-top:auto!important;padding:22px 14px;text-align:center;clear:both;box-sizing:border-box;flex-shrink:0}
.vf-site-footer .vf-footer-inner{max-width:1140px;margin:0 auto;font:500 14px/1.6 Arial,sans-serif;letter-spacing:.02em}
.vf-floating-contact{position:fixed;z-index:99999;right:18px;bottom:22px;display:flex;align-items:center;justify-content:center;gap:9px;min-height:54px;padding:0 17px;border:0;border-radius:999px;color:#fff!important;text-decoration:none!important;box-shadow:0 5px 18px rgba(0,0,0,.25);font:700 14px/1 Arial,sans-serif;cursor:pointer;transition:transform .2s ease,box-shadow .2s ease}
.vf-floating-contact:hover,.vf-floating-contact:focus-visible{transform:translateY(-3px);box-shadow:0 8px 22px rgba(0,0,0,.32);color:#fff!important;outline:3px solid rgba(255,255,255,.9);outline-offset:3px}
.vf-floating-whatsapp{background:#159447;animation:vf-support-bounce 2s ease-in-out infinite}
.vf-floating-email{right:18px;bottom:88px;background:#68162e}
.vf-floating-contact svg{flex:0 0 auto}
@keyframes vf-support-bounce{0%,80%,100%{transform:translateY(0)}88%{transform:translateY(-9px)}94%{transform:translateY(0)}}
.vf-support-dialog{width:min(420px,calc(100% - 32px));padding:0;border:0;border-radius:18px;color:#202124;box-shadow:0 24px 70px rgba(0,0,0,.32)}
.vf-support-dialog::backdrop{background:rgba(20,15,18,.62);backdrop-filter:blur(3px)}
.vf-support-dialog-inner{padding:26px}
.vf-support-dialog h2{margin:0 0 8px;color:#68162e;font:700 22px/1.25 Arial,sans-serif}
.vf-support-dialog p{margin:0 0 20px;color:#687078;font:400 14px/1.5 Arial,sans-serif}
.vf-support-actions{display:grid;gap:11px}
.vf-support-action{display:flex;align-items:center;justify-content:center;gap:10px;min-height:48px;padding:10px 16px;border-radius:10px;color:#fff!important;text-decoration:none!important;font:700 15px/1.2 Arial,sans-serif;transition:filter .18s ease,transform .18s ease}
.vf-support-action:hover{filter:brightness(.93);transform:translateY(-1px)}
.vf-support-action-service{background:#159447}.vf-support-action-channel{background:#68162e}
.vf-support-close{display:block;margin:16px auto 0;padding:8px 14px;border:0;border-radius:8px;background:#f1f3f5;color:#31363a;font-weight:600;cursor:pointer}
.vf-support-close:focus-visible,.vf-support-action:focus-visible{outline:3px solid #1e6bd6;outline-offset:3px}
@media(max-width:576px){.vf-floating-contact{right:12px;bottom:16px;min-height:48px;padding:0 14px;font-size:13px}.vf-floating-email{right:12px;bottom:76px}.vf-support-dialog-inner{padding:22px}}
@media(prefers-reduced-motion:reduce){.vf-floating-whatsapp{animation:none}.vf-floating-contact,.vf-support-action{transition:none}}
</style>
<footer class="vf-site-footer"><div class="vf-footer-inner">Videofunds top</div></footer>
<button class="vf-floating-contact vf-floating-whatsapp" id="vfSupportOpen" type="button" aria-haspopup="dialog" aria-controls="vfSupportDialog">
  <svg viewBox="0 0 24 24" width="25" height="25" fill="currentColor" aria-hidden="true"><path d="M20.52 3.48A11.86 11.86 0 0 0 12.08 0C5.5 0 .15 5.34.15 11.92c0 2.1.55 4.15 1.6 5.96L.05 24l6.28-1.65a11.9 11.9 0 0 0 5.74 1.46h.01c6.57 0 11.92-5.35 11.92-11.92 0-3.19-1.24-6.19-3.48-8.41ZM12.08 21.8h-.01a9.88 9.88 0 0 1-5.03-1.37l-.36-.21-3.73.98.99-3.64-.24-.37a9.86 9.86 0 0 1-1.52-5.27c0-5.46 4.45-9.9 9.91-9.9a9.84 9.84 0 0 1 7.01 2.9 9.84 9.84 0 0 1 2.9 7.01c0 5.46-4.45 9.9-9.92 9.9Z"/></svg>
  <span>Customer Services</span>
</button>
<a class="vf-floating-contact vf-floating-email" href="mailto:officialvideofunds@gmail.com?subject=VideoFunds%20Support" aria-label="Email VideoFunds support">
  <svg viewBox="0 0 24 24" width="25" height="25" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg><span class="vf-contact-label">Email Support</span>
</a>
<dialog class="vf-support-dialog" id="vfSupportDialog" aria-labelledby="vfSupportTitle">
  <div class="vf-support-dialog-inner">
    <h2 id="vfSupportTitle">Customer Services</h2>
    <p>Chagua njia unayopendelea kuwasiliana nasi.</p>
    <div class="vf-support-actions">
      <a class="vf-support-action vf-support-action-service" href="https://wa.me/255632394393" target="_blank" rel="noopener noreferrer">Customer service · 0632394393</a>
      <a class="vf-support-action vf-support-action-channel" href="https://whatsapp.com/channel/0029VbE0Zw95a23zlb8rKk0R" target="_blank" rel="noopener noreferrer">WhatsApp Channel</a>
    </div>
    <button class="vf-support-close" type="button" id="vfSupportClose">Funga</button>
  </div>
</dialog>
<script>
(function(){
  var dialog=document.getElementById('vfSupportDialog');
  var openButton=document.getElementById('vfSupportOpen');
  var closeButton=document.getElementById('vfSupportClose');
  var navSupport=document.getElementById('vfNavSupport');
  if(!dialog||!openButton||!closeButton)return;
  openButton.addEventListener('click',function(){dialog.showModal();});
  if(navSupport)navSupport.addEventListener('click',function(event){event.preventDefault();dialog.showModal();});
  closeButton.addEventListener('click',function(){dialog.close();});
  dialog.addEventListener('click',function(event){if(event.target===dialog)dialog.close();});
})();
</script>
