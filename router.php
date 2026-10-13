<?php
/*
 * PHP daxili serveri üçün marşrutlayıcı (yalnız lokal iş):
 *   php -S localhost:8020 -t D:\globalprocurement.az\global-php D:\globalprocurement.az\global-php\router.php
 * Hostinqdə .htaccess istifadə olunur.
 */
$path = (string)parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$file = __DIR__ . $path;

/* Bağlı qovluq və fayllar (serverdə eyni qadağa .htaccess ilə) */
if (preg_match('~^/(includes|pages|storage|tools|designs)(/|$)~', $path)
    || preg_match('~(^|/)\.~', $path)
    || preg_match('~^/admin/(config|config\.sample|db|schema|seed|lib|turnstile|import|content-pack)\.php$~', $path)
    || preg_match('~\.(log|sqlite|sql|json|md|zip|bak)$~i', $path)) { http_response_code(403); exit('Forbidden'); }

/* Mövcud statik fayllar və admin skriptləri birbaşa */
if ($path !== '/' && is_file($file)) return false;
if (preg_match('~^/admin/?$~', $path)) { chdir(__DIR__ . '/admin'); require __DIR__ . '/admin/index.php'; return true; }

require __DIR__ . '/index.php';
