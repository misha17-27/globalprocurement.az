<?php
/*
 * Təhlükəsizlik: HTTP başlıqları, xətaların gizlədilməsi, real IP (Cloudflare arxasında),
 * formalar üçün imzalı vaxt möhürü və Cloudflare Turnstile.
 * bootstrap.php tərəfindən daxil edilir (sayt və admin panel).
 */

/* Xətalar ziyarətçiyə göstərilmir — storage/php-errors.log-a yazılır (brauzerdən bağlıdır).
   Lokal `php -S` serverində göstərilir. */
if (PHP_SAPI !== 'cli-server' && PHP_SAPI !== 'cli') {
    @ini_set('display_errors', '0');
    @ini_set('log_errors', '1');
    @ini_set('error_log', __DIR__ . '/../storage/php-errors.log');
}
@ini_set('expose_php', '0');
@ini_set('session.use_strict_mode', '1');
@ini_set('session.use_only_cookies', '1');

/**
 * Təhlükəsizlik başlıqları. .htaccess-dəkindən asılı olmasın deyə PHP-dən də göndərilir
 * (LiteSpeed / mod_headers olmadıqda belə işləyir).
 */
function security_headers(bool $admin = false): void {
    if (headers_sent()) return;
    header_remove('X-Powered-By');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), camera=(), microphone=(), payment=(), usb=()');
    header('Cross-Origin-Opener-Policy: same-origin');
    /* CSP: skriptlər yalnız öz faylımızdan + Turnstile + Google Analytics.
       'unsafe-eval' — Turnstile-in yoxlama mühərriki üçün lazımdır. Inline <script> YOXDUR. */
    $csp = "default-src 'self'; base-uri 'self'; object-src 'none'; frame-ancestors 'self'; form-action 'self'; "
         . "script-src 'self' 'unsafe-eval' https://challenges.cloudflare.com https://www.googletagmanager.com; "
         . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
         . "font-src 'self' https://fonts.gstatic.com data:; "
         . "img-src 'self' data: https:; "
         . "frame-src https://challenges.cloudflare.com https://maps.google.com https://www.google.com; "
         . "connect-src 'self' https://challenges.cloudflare.com https://www.google-analytics.com https://*.google-analytics.com https://*.analytics.google.com https://www.googletagmanager.com";
    header('Content-Security-Policy: ' . $csp);
    if (is_https_req()) header('Strict-Transport-Security: max-age=31536000');
    if ($admin) {
        header('X-Robots-Tag: noindex, nofollow');
        header('Cache-Control: no-store, no-cache, must-revalidate');
    }
}

function is_https_req(): bool {
    $h = (string)($_SERVER['HTTPS'] ?? '');
    return ($h !== '' && strtolower($h) !== 'off')
        || strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https'
        || strpos((string)($_SERVER['HTTP_CF_VISITOR'] ?? ''), 'https') !== false
        || (int)($_SERVER['SERVER_PORT'] ?? 0) === 443;
}

/* ---------------- real IP ---------------- */
/** Cloudflare-in IP diapazonları (https://www.cloudflare.com/ips/) */
const CF_RANGES = [
    '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22', '141.101.64.0/18', '108.162.192.0/18',
    '190.93.240.0/20', '188.114.96.0/20', '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
    '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
    '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32', '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
];

function ip_in_range(string $ip, string $cidr): bool {
    [$net, $bits] = explode('/', $cidr);
    $a = @inet_pton($ip); $b = @inet_pton($net);
    if ($a === false || $b === false || strlen($a) !== strlen($b)) return false;
    $bits = (int)$bits; $bytes = intdiv($bits, 8); $rest = $bits % 8;
    if (strncmp($a, $b, $bytes) !== 0) return false;
    if ($rest === 0) return true;
    $mask = chr((0xFF << (8 - $rest)) & 0xFF);
    return (($a[$bytes] & $mask) === ($b[$bytes] & $mask));
}

/** Ziyarətçinin IP-si. CF-Connecting-IP yalnız sorğu həqiqətən Cloudflare-dən gəlibsə qəbul olunur. */
function client_ip(): string {
    $remote = (string)($_SERVER['REMOTE_ADDR'] ?? '');
    $cf = trim((string)($_SERVER['HTTP_CF_CONNECTING_IP'] ?? ''));
    if ($cf !== '' && filter_var($cf, FILTER_VALIDATE_IP)) {
        foreach (CF_RANGES as $r) if (ip_in_range($remote, $r)) return $cf;
    }
    return substr($remote, 0, 64);
}

/* ---------------- gizli açar ---------------- */
/** Saytın gizli açarı: config.php-dəki 'app_secret', yoxdursa install_token + DB parolundan törədilir */
function app_secret(): string {
    $s = (string)site_cfg('app_secret', '');
    if ($s === '') $s = hash('sha256', 'gp|' . site_cfg('install_token', '') . '|' . site_cfg('db_pass', '') . '|' . __DIR__);
    return $s;
}

/* ---------------- forma möhürü (vaxt tələsi) ---------------- */
/** Formaya əlavə olunan imzalı vaxt: "zaman.imza" */
function form_stamp(): string {
    $t = (string)time();
    return $t . '.' . substr(hash_hmac('sha256', 'form|' . $t, app_secret()), 0, 32);
}
/**
 * Möhür düzgündürmü: imza uyğun, forma ən azı $min saniyə əvvəl açılıb (bot dərhal göndərir)
 * və $max saniyədən köhnə deyil.
 */
function form_stamp_ok(string $stamp, int $min = 3, int $max = 86400): bool {
    if (!preg_match('/^(\d{9,11})\.([a-f0-9]{32})$/', $stamp, $m)) return false;
    if (!hash_equals(substr(hash_hmac('sha256', 'form|' . $m[1], app_secret()), 0, 32), $m[2])) return false;
    $age = time() - (int)$m[1];
    return $age >= $min && $age <= $max;
}

/* ---------------- Cloudflare Turnstile (sayt formaları) ---------------- */
function ts_site_key(): string   { return function_exists('setting') ? setting('turnstile_site') : ''; }
function ts_secret_key(): string { return function_exists('setting') ? setting('turnstile_secret') : ''; }
function ts_forms_on(): bool     { return ts_site_key() !== '' && ts_secret_key() !== '' && setting('turnstile_forms', '1') === '1'; }

/** Tokeni Cloudflare-də yoxlayır */
function ts_check(string $token, string $secret, string $ip = ''): bool {
    if ($secret === '') return true;
    if ($token === '' || strlen($token) > 4096) return false;
    $post = http_build_query(['secret' => $secret, 'response' => $token, 'remoteip' => $ip]);
    $url  = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    $raw  = false;
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $post, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10]);
        $raw = curl_exec($ch);
        curl_close($ch);
    }
    if ($raw === false) {
        $raw = @file_get_contents($url, false, stream_context_create(['http' => [
            'method' => 'POST', 'timeout' => 10, 'header' => "Content-Type: application/x-www-form-urlencoded\r\n", 'content' => $post]]));
    }
    $d = is_string($raw) ? json_decode($raw, true) : null;
    return !empty($d['success']);
}
