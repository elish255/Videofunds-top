<?php
require_once __DIR__.'/supabase_config.php';
function sb_request(string $method, string $path, ?array $payload=null, bool $service=true, ?string $bearer=null, array $extraHeaders=[]): array {
    sb_require_config();
    $key = $service ? sb_service_key() : sb_anon_key();
    $headers = ['apikey: '.$key, 'Authorization: Bearer '.($bearer ?: $key), 'Accept: application/json'];
    if ($payload !== null) $headers[]='Content-Type: application/json';
    $headers=array_merge($headers,$extraHeaders);
    $ch=curl_init(sb_url().$path);
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_HTTPHEADER=>$headers,CURLOPT_TIMEOUT=>25,CURLOPT_CONNECTTIMEOUT=>10]);
    if($payload!==null) curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($payload,JSON_UNESCAPED_SLASHES));
    $raw=curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);$err=curl_error($ch);curl_close($ch);
    $data=json_decode((string)$raw,true);
    if($raw===false) throw new RuntimeException('Supabase connection failed: '.$err);
    if($code>=400) throw new RuntimeException((string)($data['msg']??$data['message']??$data['error_description']??$data['error']??'Supabase request failed ('.$code.')'));
    return ['status'=>$code,'data'=>$data??[],'raw'=>$raw];
}
function sb_auth(string $path,array $payload): array {
    // sb_request() wraps every response as ['status'=>..., 'data'=>..., 'raw'=>...].
    // Auth callers need the actual Supabase Auth payload (access_token, user, etc.).
    $response = sb_request('POST', '/auth/v1/'.$path, $payload, false, null);
    return is_array($response['data'] ?? null) ? $response['data'] : [];
}
function sb_set_auth_cookies(array $auth): void {
    $secure=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')||($_SERVER['HTTP_X_FORWARDED_PROTO']??'')==='https';
    $opts=['expires'=>time()+(int)($auth['expires_in']??3600),'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax'];
    setcookie('vf_access_token',(string)$auth['access_token'],$opts);
    $opts['expires']=time()+60*60*24*30;setcookie('vf_refresh_token',(string)($auth['refresh_token']??''),$opts);
}
function sb_clear_auth_cookies(): void { foreach(['vf_access_token','vf_refresh_token'] as $n)setcookie($n,'',['expires'=>1,'path'=>'/','httponly'=>true,'samesite'=>'Lax']); }
function sb_current_user(): ?array {
    static $user=false;if($user!==false)return $user;
    $token=$_COOKIE['vf_access_token']??'';if($token==='')return $user=null;
    try{$r=sb_request('GET','/auth/v1/user',null,false,$token);$user=$r['data'];return $user;}
    catch(Throwable $e){$refresh=$_COOKIE['vf_refresh_token']??'';if($refresh!==''){try{$a=sb_auth('token?grant_type=refresh_token',['refresh_token'=>$refresh]);sb_set_auth_cookies($a);$user=$a['user']??null;return $user;}catch(Throwable $ignored){}}
        sb_clear_auth_cookies();return $user=null;}
}
function sb_require_user(): array {$u=sb_current_user();if(!$u){header('Location: /index.php');exit;}return $u;}
function sb_profile(string $uid): array {$r=sb_request('GET','/rest/v1/vf_profiles?select=*&id=eq.'.rawurlencode($uid).'&limit=1');return $r['data'][0]??[];}

function sb_ensure_profile(array $user, array $fallback=[]): array {
    $uid = (string)($user['id'] ?? '');
    if ($uid === '') {
        throw new RuntimeException('Authenticated user ID is missing.');
    }

    $existing = sb_profile($uid);
    if ($existing) {
        return $existing;
    }

    $email = (string)($user['email'] ?? ($fallback['email'] ?? ''));
    $name = trim((string)($user['user_metadata']['full_name'] ?? ($fallback['full_name'] ?? '')));
    $profile = [
        'id' => $uid,
        'full_name' => $name,
        'email' => $email,
        'balance' => 5.00,
        'referral_code' => strtoupper(substr(bin2hex(random_bytes(5)), 0, 8)),
        'referred_by' => $fallback['referred_by'] ?? null,
        'country_code' => $fallback['country_code'] ?? 'TZ',
        'currency_code' => $fallback['currency_code'] ?? 'USD',
        'exchange_rate' => (float)($fallback['exchange_rate'] ?? 1),
        'daily_watch_count' => 0,
        'last_watch_date' => date('Y-m-d')
    ];

    sb_request('POST', '/rest/v1/vf_profiles', $profile, true, null,
        ['Prefer: resolution=ignore-duplicates,return=minimal']);

    $created = sb_profile($uid);
    if (!$created) {
        throw new RuntimeException('Could not create/read VideoFunds profile. Check vf_profiles table and service-role key.');
    }
    return $created;
}

function sb_user_id(): string {$u=sb_require_user();return (string)$u['id'];}
function sb_rpc(string $name,array $args): array {$r=sb_request('POST','/rest/v1/rpc/'.$name,$args,true,null,['Prefer: return=representation']);return $r['data'];}


/**
 * Detect visitor country using Vercel's trusted country header.
 * Currency is then mapped from ISO country code. Exchange rates are USD base.
 */
function vf_country_currency_map(): array {
    return [
        'TZ'=>'TZS','KE'=>'KES','UG'=>'UGX','NG'=>'NGN','GH'=>'GHS','ZA'=>'ZAR',
        'ZW'=>'USD','BW'=>'BWP','ZM'=>'ZMW','MW'=>'MWK','RW'=>'RWF','BI'=>'BIF',
        'ET'=>'ETB','EG'=>'EGP','MA'=>'MAD','DZ'=>'DZD','TN'=>'TND','SN'=>'XOF',
        'CI'=>'XOF','CM'=>'XAF','CD'=>'CDF','US'=>'USD','CA'=>'CAD','GB'=>'GBP',
        'IE'=>'EUR','FR'=>'EUR','DE'=>'EUR','IT'=>'EUR','ES'=>'EUR','PT'=>'EUR',
        'NL'=>'EUR','BE'=>'EUR','AT'=>'EUR','FI'=>'EUR','GR'=>'EUR','CY'=>'EUR',
        'EE'=>'EUR','LV'=>'EUR','LT'=>'EUR','LU'=>'EUR','MT'=>'EUR','SK'=>'EUR',
        'SI'=>'EUR','IN'=>'INR','PK'=>'PKR','BD'=>'BDT','PH'=>'PHP','ID'=>'IDR',
        'MY'=>'MYR','SG'=>'SGD','AU'=>'AUD','NZ'=>'NZD','JP'=>'JPY','CN'=>'CNY',
        'BR'=>'BRL','MX'=>'MXN','CH'=>'CHF','NO'=>'NOK','SE'=>'SEK','DK'=>'DKK',
        'TR'=>'TRY','SA'=>'SAR','AE'=>'AED','QA'=>'QAR','KW'=>'KWD','OM'=>'OMR',
        'BH'=>'BHD','IL'=>'ILS','TH'=>'THB','VN'=>'VND','KR'=>'KRW'
    ];
}

function vf_detect_country_currency(): array {
    $country = strtoupper(trim((string)($_SERVER['HTTP_X_VERCEL_IP_COUNTRY'] ?? $_SERVER['HTTP_CF_IPCOUNTRY'] ?? '')));
    $map = vf_country_currency_map();

    // Fallback for non-Vercel hosting: use the visitor IP, not the server IP.
    if ($country === '' || $country === 'XX' || $country === 'T1') {
        $forwarded = (string)($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');
        $ip = trim(explode(',', $forwarded)[0] ?? '');
        if ($ip === '') $ip = (string)($_SERVER['HTTP_X_REAL_IP'] ?? '');
        if ($ip !== '' && filter_var($ip, FILTER_VALIDATE_IP)) {
            $ch = curl_init('https://ipapi.co/'.rawurlencode($ip).'/json/');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_HTTPHEADER => ['Accept: application/json']
            ]);
            $raw = curl_exec($ch);
            curl_close($ch);
            $geo = json_decode((string)$raw, true);
            if (is_array($geo)) {
                $country = strtoupper((string)($geo['country_code'] ?? ''));
                if (!empty($geo['currency'])) $map[$country] = strtoupper((string)$geo['currency']);
            }
        }
    }

    $currency = $map[$country] ?? 'USD';
    return ['country_code' => ($country !== '' ? $country : 'US'), 'currency_code' => $currency];
}

function vf_usd_exchange_rate(string $currency): float {
    $currency = strtoupper(trim($currency));
    if ($currency === '' || $currency === 'USD') return 1.0;

    // Live USD-based rates. If the free endpoint is temporarily unavailable,
    // use conservative fallback values rather than displaying a rate of 1.
    $ch = curl_init('https://open.er-api.com/v6/latest/USD');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 7,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_HTTPHEADER => ['Accept: application/json']
    ]);
    $raw = curl_exec($ch);
    curl_close($ch);
    $fx = json_decode((string)$raw, true);
    $rate = (float)($fx['rates'][$currency] ?? 0);
    if (($fx['result'] ?? '') === 'success' && $rate > 0) return $rate;

    $fallback = [
        'TZS'=>2600, 'KES'=>130, 'UGX'=>3700, 'NGN'=>1500, 'GHS'=>15,
        'ZAR'=>18.5, 'BWP'=>13.5, 'ZMW'=>27, 'MWK'=>1700, 'RWF'=>1300,
        'BIF'=>2900, 'ETB'=>130, 'EGP'=>50, 'MAD'=>10, 'DZD'=>135,
        'TND'=>3.1, 'XOF'=>600, 'XAF'=>600, 'CDF'=>2800, 'EUR'=>0.92,
        'GBP'=>0.79, 'CAD'=>1.4, 'INR'=>83, 'PKR'=>280, 'BDT'=>120,
        'PHP'=>57, 'IDR'=>16000, 'MYR'=>4.5, 'SGD'=>1.35, 'AUD'=>1.5,
        'NZD'=>1.65, 'JPY'=>150, 'CNY'=>7.2, 'BRL'=>5.5, 'MXN'=>17,
        'CHF'=>0.88, 'NOK'=>10.5, 'SEK'=>10.5, 'DKK'=>6.9, 'TRY'=>35,
        'SAR'=>3.75, 'AED'=>3.6725, 'QAR'=>3.64, 'KWD'=>0.31, 'OMR'=>0.385,
        'BHD'=>0.376, 'ILS'=>3.7, 'THB'=>36, 'VND'=>25000, 'KRW'=>1350
    ];
    return (float)($fallback[$currency] ?? 1.0);
}

function vf_sync_profile_currency(string $uid, array $profile): array {
    $geo = vf_detect_country_currency();
    $country = $geo['country_code'];
    $currency = $geo['currency_code'];
    $rate = vf_usd_exchange_rate($currency);

    $oldCountry = strtoupper((string)($profile['country_code'] ?? ''));
    $oldCurrency = strtoupper((string)($profile['currency_code'] ?? ''));
    $oldRate = (float)($profile['exchange_rate'] ?? 0);

    // Update existing users too, especially old profiles saved with USD/rate=1.
    if ($oldCountry !== $country || $oldCurrency !== $currency ||
        $oldRate <= 0 || abs($oldRate - $rate) > max(0.0001, $rate * 0.001)) {
        sb_request('PATCH', '/rest/v1/vf_profiles?id=eq.'.rawurlencode($uid), [
            'country_code' => $country,
            'currency_code' => $currency,
            'exchange_rate' => $rate,
            'updated_at' => date('c')
        ], true, null, ['Prefer: return=minimal']);
        $profile['country_code'] = $country;
        $profile['currency_code'] = $currency;
        $profile['exchange_rate'] = $rate;
    }
    return $profile;
}
