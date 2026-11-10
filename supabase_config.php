<?php
/** Supabase configuration. Set these values in Vercel Environment Variables. */
function sb_env(string $name, string $default = ''): string {
    $value = getenv($name);
    return ($value === false || $value === '') ? $default : $value;
}
function sb_url(): string { return rtrim(sb_env('SUPABASE_URL'), '/'); }
function sb_anon_key(): string { return sb_env('SUPABASE_ANON_KEY'); }
function sb_service_key(): string { return sb_env('SUPABASE_SERVICE_ROLE_KEY'); }
function sb_require_config(): void {
    foreach (['SUPABASE_URL'=>sb_url(), 'SUPABASE_ANON_KEY'=>sb_anon_key(), 'SUPABASE_SERVICE_ROLE_KEY'=>sb_service_key()] as $key=>$value) {
        if ($value === '') { http_response_code(500); exit('Server configuration missing: '.htmlspecialchars($key)); }
    }
}
