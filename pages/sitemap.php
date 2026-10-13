<?php
/* /sitemap.xml — bütün dillər üzrə səhifələr, kateqoriyalar və məhsullar */
header('Content-Type: application/xml; charset=utf-8');
$o = site_origin();
$paths = ['' => '1.0', 'shop' => '0.9', 'haqqimizda' => '0.6', 'elaqe' => '0.6'];
$today = date('Y-m-d');
$lastmod = [];

if (db()) {
    foreach (categories() as $c) $paths['product-category/' . $c['slug']] = '0.8';
    try { foreach (db()->query("SELECT slug FROM pages WHERE visible = 1 AND slug <> 'haqqimizda'") as $pg) $paths[$pg['slug']] = '0.6'; } catch (Throwable $e) {}
    foreach (db()->query('SELECT slug, updated_at FROM products WHERE visible = 1 ORDER BY sort, id') as $p) {
        $paths['product/' . $p['slug']] = '0.7';
        $lastmod['product/' . $p['slug']] = substr((string)$p['updated_at'], 0, 10) ?: $today;
    }
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
foreach ($paths as $p => $prio) {
    foreach (LANGS as $l => $_) {
        echo "  <url>\n    <loc>" . e($o . url($p, $l)) . "</loc>\n";
        foreach (LANGS as $l2 => $_2) echo '    <xhtml:link rel="alternate" hreflang="' . $l2 . '" href="' . e($o . url($p, $l2)) . "\"/>\n";
        echo '    <lastmod>' . ($lastmod[$p] ?? $today) . "</lastmod>\n    <priority>" . ($l === DEFAULT_LANG ? $prio : number_format((float)$prio - 0.1, 1)) . "</priority>\n  </url>\n";
    }
}
echo "</urlset>\n";
