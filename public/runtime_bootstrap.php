<?php
// Configure durable PHP sessions on Vercel using a Redis-compatible service.
$redisUrl = getenv('REDIS_URL');
ini_set('session.gc_maxlifetime', '28800');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if (getenv('VERCEL')) ini_set('session.cookie_secure', '1');

if ($redisUrl && !ini_get('session.save_handler') || ($redisUrl && ini_get('session.save_handler') !== 'redis')) {
    $parts = parse_url($redisUrl);
    if ($parts && !empty($parts['host'])) {
        $scheme = (($parts['scheme'] ?? 'redis') === 'rediss') ? 'tls' : 'tcp';
        $path = $parts['path'] ?? '/0';
        $query = ['database' => ltrim($path, '/') ?: '0'];
        if (isset($parts['pass'])) $query['auth'] = rawurldecode($parts['pass']);
        if (isset($parts['user']) && $parts['user'] !== '') $query['user'] = rawurldecode($parts['user']);
        $savePath = $scheme.'://'.$parts['host'].':'.($parts['port'] ?? 6379).'?'.http_build_query($query);
        ini_set('session.save_handler', 'redis');
        ini_set('session.save_path', $savePath);
    }
}
// Add WhatsApp channel shortcut to the public site without editing every page template.
if (PHP_SAPI !== 'cli' && !str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin')) {
    ob_start(static function(string $html): string {
        if (stripos($html, '</body>') === false || stripos($html, 'vf-whatsapp-float') !== false) return $html;
        $notify = '';
        if (str_contains($_SERVER['REQUEST_URI'] ?? '', 'dashboard.php')) {
            $notify = '<div id="vf-notices" style="position:fixed;top:82px;right:14px;z-index:99990;max-width:min(360px,92vw);font:14px Arial"></div><script>(async()=>{try{const r=await fetch("/user-notifications.php",{credentials:"same-origin"});const d=await r.json();if(!d.ok)return;const seen=JSON.parse(localStorage.getItem("vf_seen_notices")||"[]");const fresh=d.items.filter(n=>!seen.includes(n.id));if(!fresh.length)return;const box=document.getElementById("vf-notices");fresh.slice(0,4).forEach(n=>{const el=document.createElement("div");el.style.cssText="background:#10243d;color:white;padding:13px 15px;margin:0 0 9px;border-radius:11px;box-shadow:0 4px 18px #0003";const t=document.createElement("b");t.textContent=n.title;const p=document.createElement("div");p.style.cssText="margin-top:6px;line-height:1.4";p.textContent=n.body;el.append(t,p);box.append(el);});localStorage.setItem("vf_seen_notices",JSON.stringify([...seen,...fresh.map(n=>n.id)].slice(-100)));}catch(e){}})();</script>';
        }
        $widget = $notify.'<a id="vf-whatsapp-float" href="https://whatsapp.com/channel/0029VbE0Zw95a23zlb8rKk0R" target="_blank" rel="noopener noreferrer" aria-label="Join VideoFunds WhatsApp channel"><span style="font-size:25px">☏</span><span>WhatsApp Channel</span></a><style>#vf-whatsapp-float{position:fixed;right:17px;bottom:18px;z-index:99999;display:flex;align-items:center;gap:8px;background:#159b59;color:#fff;padding:12px 16px;border-radius:50px;text-decoration:none;font:600 14px Arial;box-shadow:0 5px 18px #0003}#vf-whatsapp-float:hover{background:#0c8048;color:#fff}@media(max-width:480px){#vf-whatsapp-float{padding:13px;border-radius:50%;width:52px;height:52px;justify-content:center}#vf-whatsapp-float span:last-child{display:none}}</style>';
        return str_ireplace('</body>', $widget.'</body>', $html);
    });
}
