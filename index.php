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

/* --- yolu çıxarırıq --- */
$uri  = (string)parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$uri  = rawurldecode($uri);
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
if ($route === 'api/request') { require __DIR__ . '/includes/request.php'; exit; }

/* --- köhnə WordPress ünvanları → 301 --- */
$legacy = [
    'contact-us' => 'elaqe', 'about-us' => 'haqqimizda', 'about-us-3' => 'haqqimizda', 'ana-sehife' => 'sirket-haqqinda', 'home-video' => '',
    'cart' => 'shop', 'checkout' => 'shop', 'my-account' => 'shop', 'wishlist' => 'shop', 'compare' => 'shop',
    'osb-lovh-l-rin-topdan-v-p-rak-nd-satisi' => 'product-category/kronospan-osb-lovh-l-ri',
    'mehsullar' => 'shop', 'catalog' => 'shop', 'kataloq' => 'shop',
];
if (isset($legacy[$route])) { header('Location: ' . url($legacy[$route]), true, 301); exit; }
if (preg_match('~^(wp-admin|wp-login\.php|wp-content/(themes|plugins)|feed|category|tag|author|page/\d+)~', $route)) {
    header('Location: ' . url(''), true, 301); exit;
}
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
