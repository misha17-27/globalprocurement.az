<?php
/*
 * Front controller — bütün ünvanlar buradan keçir (.htaccess / router.php).
 *
 * Ünvan sxemi WordPress-dəki kimidir (Google-dakı mövqelər itməsin):
 *   /                          ana səhifə
 *   /shop/                     kataloq            (?q= axtarış, ?brand= brend)
 *   /product-category/<slug>/  kateqoriya
 *   /product/<slug>/           məhsul
 *   /haqqimizda/               haqqımızda
 *   /elaqe/                    əlaqə
 *   /<slug>/                   mətn səhifəsi (admin → «Страницы»), məs. /welder-qaynag-avadanliqlari/
 *   /ru/...                    rus versiyası (eyni yollar)
 *   /api/request               sorğu göndərmə (POST, JSON)
 *   /sitemap.xml
 */
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
security_headers();

/* Server qadağan olunmuş fayla (config.php, includes/, storage/, .htaccess...) müraciəti rədd edib
   bu skripti ErrorDocument kimi çağıranda (REDIRECT_STATUS=403) sayt şablonu göstərilmir — sadə 403. */
if (in_array((int)($_SERVER['REDIRECT_STATUS'] ?? 200), [401, 403], true)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit('403 Forbidden');
}

/* --- yolu çıxarırıq --- */
$uri  = (string)parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$uri  = rawurldecode($uri);
/* Yolda "\" və idarəedici simvollar qəbul olunmur: brauzer "/\host"-u "//host" kimi oxuyur,
   bu da bizim 301-lərdən saxta saytlara açıq yönləndirmə (open redirect) yaradardı. */
if (preg_match('~[\\\\\x00-\x1F\x7F]~', $uri)) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    exit('400 Bad Request');
}
$uri  = (string)preg_replace('~/{2,}~', '/', $uri);
$base = base_url();
if ($base !== '' && stripos($uri, $base) === 0) $uri = substr($uri, strlen($base));
$path = trim($uri, '/');

/* --- dil prefiksi --- */
$seg = $path === '' ? [] : explode('/', $path);
if ($seg && isset(LANGS[$seg[0]]) && $seg[0] !== DEFAULT_LANG) { set_lang(array_shift($seg)); }
$route = implode("/", $seg);
$GLOBALS["ROUTE"] = $route;

/* --- texniki ünvanlar --- */
if ($route === 'sitemap.xml' || $route === 'sitemap_index.xml') { require __DIR__ . '/pages/sitemap.php'; exit; }
/* Yoast-ın köhnə alt-sitemap-ları (product-sitemap.xml, page-sitemap.xml...) → yeni sitemap */
if (preg_match('~^[a-z_-]+-sitemap\d*\.xml$~', $route)) { header('Location: ' . base_url() . '/sitemap.xml', true, 301); exit; }
if ($route === 'api/request') { require __DIR__ . '/includes/request.php'; exit; }

/* --- köhnə WordPress ünvanları → 301 --- */
$legacy = [
    'contact-us' => 'elaqe', 'about-us' => 'haqqimizda', 'about-us-3' => 'haqqimizda', 'ana-sehife' => 'sirket-haqqinda', 'home-video' => '',
    'cart' => 'shop', 'checkout' => 'shop', 'my-account' => 'shop', 'wishlist' => 'shop', 'compare' => 'shop',
    'osb-lovh-l-rin-topdan-v-p-rak-nd-satisi' => 'product-category/kronospan-osb-lovh-l-ri',
    'mehsullar' => 'shop', 'catalog' => 'shop', 'kataloq' => 'shop',
];
if (isset($legacy[$route])) { header('Location: ' . url($legacy[$route]), true, 301); exit; }

/* Test qovluğu canlıya keçəndən sonra */
if ($route === 'yeni' || strpos($route, 'yeni/') === 0) {
    $rest = ltrim((string)substr($route, 4), '/');
    $to   = url('') . $rest;                                           // faylda (…/site.css) sonda slash olmur
    if ($rest !== '' && !preg_match('~\.[a-z0-9]{2,5}$~i', $rest)) $to .= '/';
    $qs   = (string)($_SERVER['QUERY_STRING'] ?? '');
    header('Location: ' . $to . ($qs !== '' ? '?' . $qs : ''), true, 301); exit;
}

/* 410 Gone: WordPress-in texniki ünvanları, spam yazıların arxivləri (kateqoriya/etiket/müəllif/lent)
   və sındırılmış sayta yeridilmiş 3118 spam yazı — Google onları indeksdən daha tez silir */
$gone = function (): void {
    http_response_code(410);
    header('X-Robots-Tag: noindex');
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><meta charset="utf-8"><meta name="robots" content="noindex"><title>410</title>'
       . '<p style="font-family:sans-serif;padding:40px">410 — bu səhifə silinib. <a href="' . e(url('')) . '">globalprocurement.az</a></p>';
    exit;
};
if (preg_match('~^(wp-admin|wp-includes|wp-json|wp-login\.php|xmlrpc\.php|wp-content/(themes|plugins|cache)|feed|comments/feed|category|tag|author|\d{4}/\d{2})(/|$)~', $route)) $gone();
if (preg_match('~^page/\d+$~', $route)) { header('Location: ' . url(''), true, 301); exit; }
if (preg_match('~^[a-z0-9%-]+$~', $route) && !in_array($route, ['shop', 'haqqimizda', 'elaqe'], true)) {
    $f = __DIR__ . '/includes/gone.txt';
    if (is_file($f) && in_array($route, file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES), true)) $gone();
}
/* WordPress-in ?p=123, ?s=... kimi köhnə sorğu ünvanları ana səhifənin dublikatı olmasın */
if ($route === '' && preg_match('~(^|&)(p|page_id|cat|tag|m|attachment_id|author|feed|replytocom)=~', (string)($_SERVER['QUERY_STRING'] ?? ''))) $gone();
if (preg_match('~^shop/page/\d+$~', $route)) { header('Location: ' . url('shop'), true, 301); exit; }

/* --- kanonik: sonda slash --- */
if ($path !== '' && substr($uri, -1) !== '/' && !preg_match('~\.[a-z0-9]{2,5}$~i', $uri)) {
    $qs = (string)($_SERVER['QUERY_STRING'] ?? '');
    header('Location: ' . $base . '/' . $path . '/' . ($qs !== '' ? '?' . $qs : ''), true, 301); exit;
}

/* --- marşrutlar --- */
$params = [];
if ($route === '')                                   $page = 'home';
elseif ($route === 'shop')                           $page = 'shop';
elseif (preg_match('~^product-category/(?:[a-z0-9-]+/)*([a-z0-9-]+)$~', $route, $m)) { $page = 'shop'; $params['cat'] = $m[1]; }
elseif (preg_match('~^product/([a-z0-9-]+)$~', $route, $m))  { $page = 'product'; $params['slug'] = $m[1]; }
elseif ($route === 'haqqimizda')                     $page = 'about';
elseif ($route === 'elaqe')                          $page = 'contact';
elseif (preg_match('~^[a-z0-9-]+$~', $route) && page_by_slug($route)) { $page = 'page'; $params['slug'] = $route; }
else                                                 $page = '404';

if (!db() && $page !== '404') {
    http_response_code(503);
    echo '<meta charset="utf-8"><p style="font-family:sans-serif;padding:40px">Sayt quraşdırılır. Admin: <a href="' . e($base) . '/admin/">/admin/</a></p>';
    exit;
}

require __DIR__ . '/pages/' . $page . '.php';
