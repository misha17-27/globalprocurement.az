<?php
/*
 * WordPress / WooCommerce-dən idxal.
 * Serverdə işləyir: köhnə saytın REST API-sindən kateqoriyaları, məhsulları,
 * şəkilləri və Yoast SEO məlumatını götürür. Şəkillər assets/img/products/ altına
 * endirilir (eyni hostinqdə wp-content/uploads varsa, oradan kopyalanır).
 * Slug-lar saxlanılır — köhnə ünvanlar eynilə işləyir.
 */
declare(strict_types=1);

final class WpImport
{
    public array $log = [];
    public array $stat = ['cats' => 0, 'products' => 0, 'pages' => 0, 'images' => 0, 'errors' => 0];
    private string $base;
    private PDO $db;
    private bool $overwrite;
    private array $imgCache = [];
    /** Lokal rejim: SQL dump-dan hazırlanmış JSON + wp-content/uploads qovluğu (tools/import-local.php) */
    private string $localDir = '';

    public function __construct(PDO $db, string $base, bool $overwrite = true, string $localDir = '')
    {
        $this->db = $db;
        $this->base = rtrim($base, '/');
        $this->overwrite = $overwrite;
        $this->localDir = rtrim(str_replace(chr(92), '/', $localDir), '/');
        @set_time_limit(600);
    }

    private function say(string $m): void { $this->log[] = date('H:i:s') . '  ' . $m; }
    private function fail(string $m): void { $this->stat['errors']++; $this->say('⚠ ' . $m); }

    /* ---------------- HTTP ---------------- */
    private function get(string $url, int $timeout = 40): ?string
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 4,
                CURLOPT_TIMEOUT => $timeout, CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (GP-Importer)', CURLOPT_ENCODING => '',
            ]);
            $body = curl_exec($ch);
            $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $err  = curl_error($ch);
            curl_close($ch);
            if ($body === false || $code >= 400) { $this->fail("HTTP $code $err — $url"); return null; }
            return (string)$body;
        }
        $ctx = stream_context_create(['http' => ['timeout' => $timeout, 'header' => "User-Agent: Mozilla/5.0 (GP-Importer)\r\n", 'ignore_errors' => true]]);
        $body = @file_get_contents($url, false, $ctx);
        if ($body === false) { $this->fail("HTTP xətası — $url"); return null; }
        return $body;
    }
    private function json(string $path): ?array
    {
        if ($this->localDir !== '') {
            $map = ['products/categories' => 'categories.json', 'wc/store/v1/products' => 'products.json', 'wp/v2/pages' => 'pages.json', 'wp/v2/product' => 'seo.json'];
            foreach ($map as $k => $f) {
                if (strpos($path, $k) !== false) {
                    if (strpos($path, 'page=') !== false && strpos($path, 'page=1') === false && !preg_match('/[?&]page=1(&|$)/', $path)) return [];
                    $d = json_decode((string)@file_get_contents($this->localDir . '/' . $f), true);
                    return is_array($d) ? $d : null;
                }
            }
            return null;
        }
        $raw = $this->get($this->base . $path);
        if ($raw === null) return null;
        $d = json_decode($raw, true);
        if (!is_array($d)) { $this->fail('JSON deyil: ' . $path . ' — ' . mb_substr(strip_tags($raw), 0, 120)); return null; }
        return $d;
    }
    /** Bütün səhifələri toplayır */
    private function all(string $path): array
    {
        $out = [];
        for ($page = 1; $page <= 20; $page++) {
            $d = $this->json($path . (strpos($path, '?') === false ? '?' : '&') . 'per_page=100&page=' . $page);
            if (!$d) break;
            $out = array_merge($out, $d);
            if (count($d) < 100) break;
        }
        return $out;
    }

    public function test(): bool
    {
        $d = $this->json('/wp-json/wc/store/v1/products?per_page=1');
        if ($d === null) return false;
        $this->say('Bağlantı uğurludur: ' . $this->base);
        return true;
    }

    /* ---------------- şəkillər ---------------- */
    /** URL → assets/img daxilində nisbi yol (və ya '' səhv olduqda) */
    public function image(string $url, string $name): string
    {
        $url = trim(html_entity_decode($url));
        if ($url === '') return '';
        if (isset($this->imgCache[$url])) return $this->imgCache[$url];

        $path = (string)parse_url($url, PHP_URL_PATH);
        $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (strpos($url, 'data:') === 0) return '';
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) { $this->fail("Şəkil növü dəstəklənmir: $url"); return ''; }

        $dir = ROOT_DIR . '/assets/img/products';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        $file = slugify($name) ?: 'img';
        $file = mb_substr($file, 0, 60) . '-' . substr(md5($url), 0, 6) . '.' . $ext;
        $abs  = "$dir/$file";
        $rel  = 'products/' . $file;

        if (is_file($abs) && filesize($abs) > 0) return $this->imgCache[$url] = $rel;

        /* Eyni hostinqdə köhnə WordPress faylları varsa — birbaşa kopyalayırıq */
        $data = null;
        if (preg_match('~/wp-content/uploads/.+$~', $path, $m)) {
            foreach ([$this->localDir, dirname(ROOT_DIR), dirname(ROOT_DIR, 2), (string)($_SERVER['DOCUMENT_ROOT'] ?? '')] as $root) {
                $local = rtrim(str_replace('\\', '/', $root), '/') . $m[0];
                if ($root !== '' && is_file($local)) { $data = @file_get_contents($local); break; }
            }
        }
        if (($data === null || $data === false) && $this->localDir === '') $data = $this->get($url, 60);
        if (!$data && $this->localDir !== '') { $this->fail("Şəkil tapılmadı: $url"); return ''; }
        if (!$data) return '';
        if (@getimagesizefromstring($data) === false) { $this->fail("Şəkil deyil: $url"); return ''; }
        file_put_contents($abs, $data);
        @chmod($abs, 0644);
        $this->stat['images']++;
        return $this->imgCache[$url] = $rel;
    }

    /** HTML daxilindəki WordPress şəkillərini lokal nüsxə ilə əvəz edir */
    private function localizeHtml(string $html, string $name): string
    {
        return (string)preg_replace_callback('~(<img[^>]+src=")([^"]+)(")~i', function ($m) use ($name) {
            $rel = $this->image($m[2], $name . '-content');
            return $m[1] . ($rel !== '' ? base_url() . '/assets/img/' . $rel : $m[2]) . $m[3];
        }, $html);
    }

    /**
     * WordPress post_content-i HTML-ə çevirir (wpautop-un sadə analoqu):
     * shortcode-lar silinir, boş sətir — abzas, "- " ilə başlayan sətirlər — siyahı.
     * Artıq HTML blokları olan mətnə toxunulmur.
     */
    public static function autop(string $s): string
    {
        $s = (string)preg_replace('~<!--.*?-->~s', '', $s);
        $s = (string)preg_replace('~\[/?[a-z_][a-z0-9_-]*(?:\s[^\]]*)?\]~i', '', $s);
        $s = trim(str_replace(["\r\n", "\r"], "\n", $s));
        if ($s === '' || preg_match('~<(p|div|ul|ol|table|h[1-6])[\s>]~i', $s)) return $s;
        $html = '';
        foreach (preg_split('/\n\s*\n/', $s) as $block) {
            $buf = []; $list = [];
            $flushP = function () use (&$buf, &$html) { if ($buf) { $html .= '<p>' . implode('<br>', $buf) . '</p>'; $buf = []; } };
            $flushL = function () use (&$list, &$html) { if ($list) { $html .= '<ul><li>' . implode('</li><li>', $list) . '</li></ul>'; $list = []; } };
            foreach (explode("\n", $block) as $ln) {
                $ln = trim($ln);
                if ($ln === '') continue;
                if (preg_match('/^[-–•✔✓]\s*(.+)$/u', $ln, $m)) { $flushP(); $list[] = $m[1]; }
                elseif (mb_strlen($ln) <= 60 && preg_match('/\p{Lu}{3}/u', $ln) && mb_strtoupper($ln) === $ln && !preg_match('/\d/', $ln)) { $flushP(); $flushL(); $html .= '<h3>' . $ln . '</h3>'; }
                else { $flushL(); $buf[] = $ln; }
            }
            $flushP(); $flushL();
        }
        return $html;
    }

    /* ---------------- spam təmizləmə ---------------- */
    /** Etibarlı xarici domenlər (sertifikatlar, istehsalçılar, sosial şəbəkələr) */
    private const LINK_ALLOW = ['globalprocurement.az', 'wa.me', 'api.whatsapp.com', 'google.com', 'drive.google.com', 'goo.gl',
        'instagram.com', 'facebook.com', 'youtube.com', 'youtu.be', 'polynor.ru', 'kronospan', 'tn.ru', 'technonicol', 'isomax'];
    private const SPAM = '~ghostwrit|hausarbeit|seminararbeit|bachelorarbeit|masterarbeit|arbeit-schreiben|schreiben-lassen|zitieren|casino|kasino|slot|bonus|essay|porn|viagra~i';

    /**
     * Sındırılmış WordPress-in əlavə etdiyi linkləri silir (linkin mətni ilə birlikdə —
     * spam cümlənin ortasına yazılıb, onsuz cümlə yenidən düzgün oxunur).
     * $allExternal=true: icazəli siyahıda olmayan BÜTÜN xarici linklər silinir (səhifələr üçün).
     */
    public static function stripSpam(string $html, bool $allExternal = false): string
    {
        if (trim($html) === '' || !class_exists('DOMDocument')) return $html;
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8"?><div id="__r">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        $root = $doc->getElementById('__r');
        if (!$root) return $html;
        foreach (iterator_to_array($root->getElementsByTagName('a')) as $a) {
            $host = strtolower((string)parse_url(trim($a->getAttribute('href')), PHP_URL_HOST));
            if ($host === '') continue;
            $ok = false;
            foreach (self::LINK_ALLOW as $w) if (strpos($host, $w) !== false) { $ok = true; break; }
            $spam = preg_match(self::SPAM, $host . ' ' . $a->textContent);
            if ($spam || ($allExternal && !$ok)) $a->parentNode->removeChild($a);
        }
        /* spam mətnli bloklar (linksiz qalmış abzaslar) */
        $xp = new DOMXPath($doc);
        foreach (iterator_to_array($xp->query('.//p|.//li|.//h2|.//h3|.//h4', $root)) as $n) {
            if ($n->parentNode && preg_match('~ghostwrit|hausarbeit|seminararbeit|schlaue wörter|wissenschaftliche arbeit~iu', $n->textContent)) {
                $n->parentNode->removeChild($n);
            }
        }
        $out = '';
        foreach ($root->childNodes as $c) $out .= $doc->saveHTML($c);
        return (string)preg_replace('/ {2,}/', ' ', $out);
    }

    /* ---------------- mətn səhifələri ---------------- */
    /** WordPress slug => yeni slug. Demo səhifələr (About us, Contact FAQ) və spam yazılar köçürülmür. */
    public const PAGES = ['haqqimizda' => 'haqqimizda', 'welder-qaynag-avadanliqlari' => 'welder-qaynag-avadanliqlari', 'ana-sehife' => 'sirket-haqqinda'];
    private const PAGE_TITLES = [
        'haqqimizda' => [null, 'О компании'],
        'welder-qaynag-avadanliqlari' => [null, 'WELDER — сварочное оборудование'],
        'sirket-haqqinda' => ['Şirkət haqqında', 'О компании подробнее'],
    ];

    public function pages(): void
    {
        $rows = $this->json('/wp-json/wp/v2/pages?per_page=100&_fields=id,slug,title,content,yoast_head_json') ?? [];
        $now = date('Y-m-d H:i');
        $order = array_keys(self::PAGES);
        foreach ($rows as $r) {
            $wpSlug = (string)($r['slug'] ?? '');
            if (!isset(self::PAGES[$wpSlug])) continue;
            $slug  = self::PAGES[$wpSlug];
            $title = html_entity_decode((string)(is_array($r['title'] ?? null) ? ($r['title']['rendered'] ?? '') : ($r['title'] ?? '')), ENT_QUOTES, 'UTF-8');
            $html  = (string)(is_array($r['content'] ?? null) ? ($r['content']['rendered'] ?? '') : ($r['content'] ?? ''));
            $y  = (array)($r['yoast_head_json'] ?? []);
            $mt = (string)($r['seo_title'] ?? ($y['title'] ?? ''));
            $md = (string)($r['seo_desc'] ?? ($y['description'] ?? ''));
            if (strpos($mt, '%%') !== false) $mt = '';
            if (strpos($md, '%%') !== false) $md = '';

            $html = self::stripSpam(self::autop($html), true);
            $html = sanitize_html($this->localizeHtml($html, $slug));
            $html = (string)preg_replace('~<h1[^>]*>.*?</h1>~is', '', $html);
            [$az, $ru] = self::splitLang($html);
            [$tAz, $tRu] = self::PAGE_TITLES[$slug] ?? [null, ''];
            if ($tAz) $title = $tAz;

            $st = $this->db->prepare('SELECT id FROM pages WHERE slug = ?'); $st->execute([$slug]);
            $pid = (int)($st->fetch()['id'] ?? 0);
            if ($pid) {
                $this->db->prepare('UPDATE pages SET wp_id = ?, updated_at = ? WHERE id = ?')->execute([(int)($r['id'] ?? 0), $now, $pid]);
            } else {
                $this->db->prepare("INSERT INTO pages (slug, image, sort, visible, in_menu, wp_id, updated_at) VALUES (?, '', ?, 1, 1, ?, ?)")
                    ->execute([$slug, (int)array_search($wpSlug, $order, true) + 1, (int)($r['id'] ?? 0), $now]);
                $pid = (int)$this->db->lastInsertId();
            }
            $this->upsertPageI18n($pid, 'az', ['title' => $title, 'body' => $az,
                'meta_title' => html_entity_decode($mt, ENT_QUOTES, 'UTF-8'), 'meta_desc' => html_entity_decode($md, ENT_QUOTES, 'UTF-8')]);
            if ($ru !== '' || $tRu !== '') $this->upsertPageI18n($pid, 'ru', ['title' => (string)$tRu, 'body' => $ru]);
            $this->stat['pages']++;
            $this->say("Səhifə: $title → /$slug/" . ($ru !== '' ? ' (AZ + RU)' : ''));
        }
    }
    private function upsertPageI18n(int $pid, string $lang, array $f): void
    {
        $st = $this->db->prepare('SELECT * FROM page_i18n WHERE page_id = ? AND lang = ?'); $st->execute([$pid, $lang]);
        $row = $st->fetch();
        if (!$row) {
            $f = array_merge(['title' => '', 'lead' => '', 'body' => '', 'meta_title' => '', 'meta_desc' => ''], $f);
            $this->db->prepare('INSERT INTO page_i18n (page_id, lang, title, lead, body, meta_title, meta_desc) VALUES (?,?,?,?,?,?,?)')
                ->execute([$pid, $lang, $f['title'], $f['lead'], $f['body'], $f['meta_title'], $f['meta_desc']]);
            return;
        }
        $set = [];
        foreach ($f as $k => $v) if ($v !== '' && ($this->overwrite || trim((string)$row[$k]) === '')) $set[$k] = $v;
        if (!$set) return;
        $sql = implode(', ', array_map(function ($k) { return "`$k` = ?"; }, array_keys($set)));
        $this->db->prepare("UPDATE page_i18n SET $sql WHERE page_id = ? AND lang = ?")->execute([...array_values($set), $pid, $lang]);
    }

    /* ---------------- AZ / RU ayrılması ---------------- */
    /** Mətnin əsasən kiril olub-olmadığı */
    private static function isRu(string $t): bool
    {
        $t = preg_replace('/[^\p{L}]/u', '', $t);
        if ($t === '') return false;
        $cyr = preg_match_all('/\p{Cyrillic}/u', $t);
        return $cyr / max(1, mb_strlen($t)) > 0.5;
    }
    /** WordPress təsvirində AZ və RU mətnlər bir yerdədir — yuxarı səviyyəli bloklara görə ayırırıq */
    public static function splitLang(string $html): array
    {
        if (trim($html) === '' || !class_exists('DOMDocument')) return [$html, ''];
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8"?><div id="__r">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        $root = $doc->getElementById('__r');
        if (!$root) return [$html, ''];
        $last = 'az';
        [$az, $ru] = self::splitNode($doc, $root, $last);
        return [trim($az), trim($ru)];
    }

    /** Kiril hərflərinin payı (0..1) */
    private static function cyrShare(string $t): float
    {
        $t = (string)preg_replace('/[^\p{L}]/u', '', $t);
        return $t === '' ? 0.0 : preg_match_all('/\p{Cyrillic}/u', $t) / mb_strlen($t);
    }

    /**
     * Uşaq node-ları dilə görə paylayır. Qarışıq blok (məs. içində həm AZ, həm RU bölmələr
     * olan <ul>) rekursiv bölünür və hər tərəfdə eyni teqlə yenidən bükülür.
     */
    private static function splitNode(DOMDocument $doc, DOMNode $parent, string &$last): array
    {
        $az = $ru = '';
        foreach (iterator_to_array($parent->childNodes) as $n) {
            $text = trim((string)$n->textContent);
            $hasImg = $n instanceof DOMElement && ($n->tagName === 'img' || $n->getElementsByTagName('img')->length > 0);
            if ($text !== '' && mb_strlen($text) < 3 && !$hasImg) continue;           // "r." kimi qırıntılar
            $share = self::cyrShare($text);
            if ($n instanceof DOMElement && $n->hasChildNodes() && $share > 0.15 && $share < 0.85 && mb_strlen($text) > 80) {
                [$a, $r] = self::splitNode($doc, $n, $last);
                $open = '<' . $n->tagName . '>'; $close = '</' . $n->tagName . '>';
                if (trim(strip_tags($a, '<img>')) !== '') $az .= $open . $a . $close;
                if (trim(strip_tags($r, '<img>')) !== '') $ru .= $open . $r . $close;
                continue;
            }
            $lang = $text === '' ? $last : ($share > 0.5 ? 'ru' : 'az');
            $h = $doc->saveHTML($n);
            if ($lang === 'ru') $ru .= $h; else $az .= $h;
            $last = $lang;
        }
        return [$az, $ru];
    }

    private static function guessBrand(string $t): string
    {
        $map = ['kronospan' => 'Kronospan', 'osb' => 'Kronospan', 'welder' => 'WELDER', 'polynor' => 'Polinor', 'polinor' => 'Polinor',
                'penoplex' => 'Penoplex', 'isomax' => 'ISOMAX RUS', 'sofit' => 'SOFIT', 'thermo plus' => 'Thermo Plus',
                'texno' => 'Technonicol', 'texnonikol' => 'Technonicol', 'technonicol' => 'Technonicol', 'logicbase' => 'Technonicol', 'bitum' => 'Technonicol'];
        $l = mb_strtolower(str_replace(['İ', 'ı'], ['i', 'i'], $t));
        foreach ($map as $k => $v) if (strpos($l, $k) !== false) return $v;
        return '';
    }

    /* ---------------- kateqoriyalar ---------------- */
    public function categories(): array
    {
        $rows = $this->json('/wp-json/wc/store/v1/products/categories?per_page=100') ?? [];
        $map = [];   // wp_id => local id
        foreach ($rows as $c) {
            $slug = (string)($c['slug'] ?? '');
            if ($slug === '' || $slug === 'uncategorized' || $slug === 'misc') continue;
            $name = html_entity_decode((string)$c['name'], ENT_QUOTES, 'UTF-8');
            $st = $this->db->prepare('SELECT id, image FROM categories WHERE slug = ?'); $st->execute([$slug]);
            $row = $st->fetch();
            $img = !empty($c['image']['src']) ? $this->image((string)$c['image']['src'], $slug) : '';
            if ($row) {
                $id = (int)$row['id'];
                $this->db->prepare('UPDATE categories SET wp_id = ?' . ($img !== '' && ($this->overwrite || $row['image'] === '') ? ', image = ?' : '') . ' WHERE id = ?')
                    ->execute($img !== '' && ($this->overwrite || $row['image'] === '') ? [(int)$c['id'], $img, $id] : [(int)$c['id'], $id]);
            } else {
                $this->db->prepare('INSERT INTO categories (slug, parent_id, image, sort, visible, wp_id) VALUES (?,0,?,?,1,?)')
                    ->execute([$slug, $img, 50 + count($map), (int)$c['id']]);
                $id = (int)$this->db->lastInsertId();
            }
            $this->upsertCatI18n($id, 'az', $name, trim(strip_tags(html_entity_decode((string)($c['description'] ?? '')))));
            $map[(int)$c['id']] = $id;
            $this->stat['cats']++;
            $this->say("Kateqoriya: $name");
        }
        foreach ($rows as $c) {      // valideyn
            if (!empty($c['parent']) && isset($map[(int)$c['id']], $map[(int)$c['parent']])) {
                $this->db->prepare('UPDATE categories SET parent_id = ? WHERE id = ?')->execute([$map[(int)$c['parent']], $map[(int)$c['id']]]);
            }
        }
        return $map;
    }
    private function upsertCatI18n(int $id, string $lang, string $name, string $descr): void
    {
        $st = $this->db->prepare('SELECT name, descr FROM category_i18n WHERE category_id = ? AND lang = ?'); $st->execute([$id, $lang]);
        if ($r = $st->fetch()) {
            $this->db->prepare('UPDATE category_i18n SET name = ?, descr = ? WHERE category_id = ? AND lang = ?')
                ->execute([$this->overwrite || $r['name'] === '' ? $name : $r['name'], ($this->overwrite && $descr !== '') || (string)$r['descr'] === '' ? $descr : $r['descr'], $id, $lang]);
        } else {
            $this->db->prepare('INSERT INTO category_i18n (category_id, lang, name, descr, meta_title, meta_desc) VALUES (?,?,?,?,\'\',\'\')')->execute([$id, $lang, $name, $descr]);
        }
    }

    /* ---------------- məhsullar ---------------- */
    public function products(array $catMap): void
    {
        $rows = $this->all('/wp-json/wc/store/v1/products');
        $this->say('WordPress-də məhsul: ' . count($rows));

        /* Yoast SEO (title / description) */
        $seo = [];
        foreach ($this->all('/wp-json/wp/v2/product?_fields=id,slug,yoast_head_json') as $s) {
            $y = $s['yoast_head_json'] ?? [];
            $clean = function ($v) { $v = html_entity_decode((string)$v, ENT_QUOTES, 'UTF-8'); return strpos($v, '%%') !== false ? trim((string)preg_replace('/\s*%%[a-z_]+%%\s*/', ' ', $v), " -|") : trim($v); };
            $seo[(int)$s['id']] = [$clean($y['title'] ?? ''), $clean($y['description'] ?? $y['og_description'] ?? '')];
        }

        $now = date('Y-m-d H:i');
        foreach ($rows as $i => $p) {
            $slug = (string)($p['slug'] ?? '');
            if ($slug === '') continue;
            $name = html_entity_decode((string)$p['name'], ENT_QUOTES, 'UTF-8');

            /* şəkillər */
            $imgs = [];
            foreach ((array)($p['images'] ?? []) as $k => $im) {
                $rel = $this->image((string)($im['src'] ?? ''), $slug . ($k ? "-$k" : ''));
                if ($rel !== '') $imgs[] = $rel;
            }
            $main = $imgs[0] ?? '';
            $gallery = array_slice($imgs, 1);

            /* qiymət */
            $price = '';
            if (!empty($p['prices']['price']) && (int)$p['prices']['price'] > 0) {
                $minor = (int)($p['prices']['currency_minor_unit'] ?? 2);
                $val = (int)$p['prices']['price'] / (10 ** $minor);
                $price = number_format($val, fmod($val, 1.0) == 0.0 ? 0 : 2, '.', ' ') . ' ' . html_entity_decode((string)($p['prices']['currency_symbol'] ?? '₼'));
            }

            /* xüsusiyyətlər (atributlar) */
            $specs = [];
            foreach ((array)($p['attributes'] ?? []) as $a) {
                $terms = array_map(function ($t) { return html_entity_decode((string)$t['name'], ENT_QUOTES, 'UTF-8'); }, (array)($a['terms'] ?? []));
                if ($terms) $specs[] = html_entity_decode((string)$a['name'], ENT_QUOTES, 'UTF-8') . ': ' . implode(', ', $terms);
            }

            /* təsvir: təmizlə, şəkilləri lokallaşdır, AZ/RU ayır */
            $desc = sanitize_html($this->localizeHtml(self::stripSpam(self::autop((string)($p['description'] ?? ''))), $slug));
            [$bodyAz, $bodyRu] = self::splitLang($desc);
            $shortHtml = sanitize_html(self::autop((string)($p['short_description'] ?? '')));
            [$shortAz, $shortRu] = self::splitLang($shortHtml);
            $shortAz = trim(preg_replace('/\s+/u', ' ', strip_tags($shortAz)));
            $shortRu = trim(preg_replace('/\s+/u', ' ', strip_tags($shortRu)));
            [$mt, $md] = $seo[(int)$p['id']] ?? ['', ''];

            $st = $this->db->prepare('SELECT * FROM products WHERE slug = ?'); $st->execute([$slug]);
            $row = $st->fetch();
            if ($row) {
                $pid = (int)$row['id'];
                $set = ['wp_id' => (int)$p['id'], 'updated_at' => $now];
                if ($main !== '' && ($this->overwrite || $row['image'] === '')) $set['image'] = $main;
                if ($gallery && ($this->overwrite || in_array((string)$row['gallery'], ['', '[]'], true))) $set['gallery'] = json_encode($gallery);
                if ($price !== '' && ($this->overwrite || $row['price'] === '')) $set['price'] = $price;
                if ($row['brand'] === '') $set['brand'] = self::guessBrand($name);
                $sql = implode(', ', array_map(function ($k) { return "`$k` = ?"; }, array_keys($set)));
                $this->db->prepare("UPDATE products SET $sql WHERE id = ?")->execute([...array_values($set), $pid]);
            } else {
                $this->db->prepare('INSERT INTO products (slug, brand, image, gallery, price, unit, options, badge, sort, visible, featured, wp_id, created_at, updated_at) VALUES (?,?,?,?,?,\'\',\'\',\'\',?,1,0,?,?,?)')
                    ->execute([$slug, self::guessBrand($name), $main, json_encode($gallery), $price, 100 + $i, (int)$p['id'], $now, $now]);
                $pid = (int)$this->db->lastInsertId();
            }

            $this->upsertI18n($pid, 'az', ['title' => $name, 'short' => $shortAz, 'body' => $bodyAz, 'specs' => implode("\n", $specs), 'meta_title' => $mt, 'meta_desc' => $md]);
            if ($bodyRu !== '' || $shortRu !== '') {
                $this->upsertI18n($pid, 'ru', ['body' => $bodyRu, 'short' => $shortRu], false);
            }

            /* kateqoriyalar */
            $cids = [];
            foreach ((array)($p['categories'] ?? []) as $c) if (isset($catMap[(int)$c['id']])) $cids[] = $catMap[(int)$c['id']];
            if ($cids) {
                $this->db->prepare('DELETE FROM product_category WHERE product_id = ?')->execute([$pid]);
                $ins = $this->db->prepare('INSERT INTO product_category (product_id, category_id) VALUES (?,?)');
                foreach (array_unique($cids) as $cid) $ins->execute([$pid, $cid]);
            }

            $this->stat['products']++;
            $this->say('Məhsul: ' . $name . ' — şəkil: ' . count($imgs) . ($bodyRu !== '' ? ', RU mətn ayrıldı' : ''));
        }
    }

    /** $force=false olduqda yalnız boş sahələr doldurulur */
    private function upsertI18n(int $pid, string $lang, array $f, bool $force = true): void
    {
        $st = $this->db->prepare('SELECT * FROM product_i18n WHERE product_id = ? AND lang = ?'); $st->execute([$pid, $lang]);
        $row = $st->fetch();
        if (!$row) {
            $f = array_merge(['title' => '', 'short' => '', 'body' => '', 'specs' => '', 'meta_title' => '', 'meta_desc' => ''], $f);
            $this->db->prepare('INSERT INTO product_i18n (product_id, lang, title, short, body, specs, meta_title, meta_desc) VALUES (?,?,?,?,?,?,?,?)')
                ->execute([$pid, $lang, $f['title'], $f['short'], $f['body'], $f['specs'], $f['meta_title'], $f['meta_desc']]);
            return;
        }
        $set = [];
        foreach ($f as $k => $v) {
            if ($v === '') continue;                                         // boş dəyər mövcud mətni silmir
            if (($force && $this->overwrite) || trim((string)$row[$k]) === '') $set[$k] = $v;
        }
        if (!$set) return;
        $sql = implode(', ', array_map(function ($k) { return "`$k` = ?"; }, array_keys($set)));
        $this->db->prepare("UPDATE product_i18n SET $sql WHERE product_id = ? AND lang = ?")->execute([...array_values($set), $pid, $lang]);
    }

    public function run(): void
    {
        $this->say('İdxal başladı: ' . $this->base . ($this->overwrite ? ' (mövcud mətnlər yenilənir)' : ' (yalnız boş sahələr)'));
        $map = $this->categories();
        $this->products($map);
        $this->pages();
        $this->say(sprintf('Hazırdır. Kateqoriya: %d, məhsul: %d, səhifə: %d, yeni şəkil: %d, xəta: %d',
            $this->stat['cats'], $this->stat['products'], $this->stat['pages'], $this->stat['images'], $this->stat['errors']));
    }
}
