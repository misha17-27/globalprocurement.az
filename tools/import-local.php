<?php
/*
 * Lokal idxal — WordPress SQL dump-dan hazırlanmış JSON-dan (sayta giriş lazım deyil).
 *
 *   1) python -I tools/wpdump.py <dump.sql> <site.zip> <qovluq>
 *      → categories.json, products.json, seo.json + wp-content/uploads/...
 *   2) php tools/import-local.php <qovluq> [--keep]
 *
 * --keep: yalnız boş sahələr doldurulur (paneldə edilən dəyişikliklər qalır).
 * Paneldə idxal bölməsi yoxdur — köçürmə bitib, təkrar idxal paneldəki düzəlişləri silə bilərdi.
 */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }

$dir = $argv[1] ?? '';
if ($dir === '' || !is_file("$dir/products.json")) { fwrite(STDERR, "İstifadə: php tools/import-local.php <qovluq> [--keep]\n"); exit(1); }

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
require __DIR__ . '/../admin/lib.php';
require __DIR__ . '/../admin/seed.php';
require __DIR__ . '/../admin/import.php';
require __DIR__ . '/../admin/content-pack.php';

$db = pdo();
cms_create_tables($db);
$seed = cms_seed($db);
echo 'Seed: ' . json_encode($seed) . "\n";

$imp = new WpImport($db, 'https://globalprocurement.az', !in_array('--keep', $argv, true), $dir);
$imp->run();
echo implode("\n", $imp->log) . "\n";
echo 'Paket (SEO + RU): ' . json_encode(cms_apply_pack($db)) . "\n";
