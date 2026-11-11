<?php
// Shared site footer and floating support links for all public pages.
?>
<style>
.vf-site-footer{background:#212529;color:#fff;width:100%;margin-top:36px;padding:22px 14px;text-align:center;clear:both}
.vf-site-footer .vf-footer-inner{max-width:1140px;margin:0 auto;font-size:14px;line-height:1.6}
.vf-floating-contact{position:fixed;z-index:99999;right:12px;top:56%;bottom:auto;display:flex;align-items:center;justify-content:center;width:46px;height:46px;border-radius:50%;color:#fff!important;text-decoration:none!important;box-shadow:0 4px 14px rgba(0,0,0,.28);transition:transform .18s ease,box-shadow .18s ease;font-size:25px}
.vf-floating-contact:hover,.vf-floating-contact:focus{transform:translateY(-4px);box-shadow:0 7px 18px rgba(0,0,0,.34);color:#fff!important;outline:2px solid rgba(255,255,255,.85);outline-offset:2px}
.vf-floating-whatsapp{right:12px;background:#20b95a;top:calc(56% - 27px)}
.vf-floating-email{right:12px;background:#68162e;top:calc(56% + 27px)}
.vf-floating-contact .vf-contact-label{position:absolute;left:50%;bottom:calc(100% + 8px);transform:translateX(-50%);background:#202124;color:#fff;border-radius:5px;padding:5px 9px;font:600 12px/1.2 Arial,sans-serif;white-space:nowrap;opacity:0;pointer-events:none;transition:opacity .18s ease}
.vf-floating-contact:hover .vf-contact-label,.vf-floating-contact:focus .vf-contact-label{opacity:1}
@media(max-width:576px){.vf-floating-contact{width:42px;height:42px;font-size:20px;right:10px}.vf-floating-whatsapp{right:10px;top:calc(56% - 25px)}.vf-floating-email{right:10px;top:calc(56% + 25px)}}
body{padding-bottom:4px}
</style>
<footer class="vf-site-footer">
  <div class="vf-footer-inner">Copyright &copy; videofunds.homes 2025-2045</div>
</footer>
<a class="vf-floating-contact vf-floating-whatsapp" href="https://whatsapp.com/channel/0029VbE0Zw95a23zlb8rKk0R" target="_blank" rel="noopener noreferrer" aria-label="Join VideoFunds WhatsApp Channel">
  <svg viewBox="0 0 24 24" width="27" height="27" fill="currentColor" aria-hidden="true"><path d="M20.52 3.48A11.86 11.86 0 0 0 12.08 0C5.5 0 .15 5.34.15 11.92c0 2.1.55 4.15 1.6 5.96L.05 24l6.28-1.65a11.9 11.9 0 0 0 5.74 1.46h.01c6.57 0 11.92-5.35 11.92-11.92 0-3.19-1.24-6.19-3.48-8.41ZM12.08 21.8h-.01a9.88 9.88 0 0 1-5.03-1.37l-.36-.21-3.73.98.99-3.64-.24-.37a9.86 9.86 0 0 1-1.52-5.27c0-5.46 4.45-9.9 9.91-9.9a9.84 9.84 0 0 1 7.01 2.9 9.84 9.84 0 0 1 2.9 7.01c0 5.46-4.45 9.9-9.92 9.9Zm5.44-7.42c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.47-.89-.79-1.49-1.76-1.66-2.06-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.62-.92-2.22-.24-.58-.48-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.2 5.08 4.49.71.31 1.27.5 1.7.64.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2.01-1.42.25-.7.25-1.3.17-1.42-.07-.13-.27-.2-.57-.35Z"/></svg><span class="vf-contact-label">WhatsApp Channel</span>
</a>
<a class="vf-floating-contact vf-floating-email" href="mailto:officialvideofunds@gmail.com?subject=VideoFunds%20Support" aria-label="Email VideoFunds support">
  <svg viewBox="0 0 24 24" width="25" height="25" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg><span class="vf-contact-label">Email Support</span>
</a>
