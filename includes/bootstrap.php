<?php
/*
 * Saytın nüvəsi: konfiqurasiya, dil, baza, köməkçi funksiyalar.
 * Hər səhifə yalnız bu faylı daxil edir (index.php vasitəsilə).
 */
declare(strict_types=1);

require_once __DIR__ . '/compat.php';
require_once __DIR__ . '/security.php';

const ROOT_DIR = __DIR__ . '/..';

/* Dillər: kod => [qısa, tam ad]. Yeni dil: buraya + includes/lang/<kod>.php */
const LANGS = ['az' => ['AZ', 'Azərbaycan'], 'ru' => ['RU', 'Русский']];
const DEFAULT_LANG = 'az';

/* Ehtiyat əlaqə məlumatı (baza əlçatmaz olduqda) */
const FALLBACK = [
    'phone' => '+994 51 304 00 43', 'whatsapp' => '994513040043',
    'email' => 'ceo@globalprocurement.az', 'mail_to' => 'ceo@globalprocurement.az',
];

$GLOBALS['LANG'] = DEFAULT_LANG;

/* ---------------- konfiqurasiya ---------------- */
function site_cfg(string $k, $default = null) {
    static $c = null;
    if ($c === null) { $f = ROOT_DIR . '/admin/config.php'; $c = is_file($f) ? require $f : []; }
    return $c[$k] ?? $default;
}

/** Saytın kök ünvanı: '' və ya '/yeni' (alt qovluq avtomatik tapılır) */
function base_url(): string {
    static $b = null;
    if ($b !== null) return $b;
    $cfg = site_cfg('base_url');
    if (is_string($cfg)) return $b = rtrim($cfg, '/');
    $root = realpath((string)($_SERVER['DOCUMENT_ROOT'] ?? '')) ?: '';
    $here = realpath(ROOT_DIR) ?: '';
    $b = '';
    if ($root !== '' && $here !== '' && stripos($here, $root) === 0) {
        $b = str_replace('\\', '/', substr($here, strlen($root)));
        $b = '/' . trim($b, '/');
        if ($b === '/') $b = '';
    }
    return $b;
}

/** Saytın mənşəyi. Host başlığı saxtalaşdırıla bilər — yalnız icazəli adlar qəbul olunur. */
const ALLOWED_HOSTS = ['globalprocurement.az', 'www.globalprocurement.az', 'localhost', '127.0.0.1'];
function site_origin(): string {
    $host = strtolower(preg_replace('/[^A-Za-z0-9.:-]/', '', (string)($_SERVER['HTTP_HOST'] ?? '')));
    $name = (string)preg_replace('/:\d+$/', '', $host);
    if (!in_array($name, array_merge(ALLOWED_HOSTS, (array)site_cfg('allowed_hosts', [])), true)) $host = 'globalprocurement.az';
    $https = is_https_req() || $name === 'globalprocurement.az' || $name === 'www.globalprocurement.az';
    return ($https ? 'https://' : 'http://') . $host;
}

/* ---------------- dil ---------------- */
function lang(): string { return $GLOBALS['LANG']; }
function set_lang(string $l): void { if (isset(LANGS[$l])) $GLOBALS['LANG'] = $l; }

/** İnterfeys sətri; yoxdursa AZ-dan, o da yoxdursa açarın özü */
function t(string $key): string {
    static $dict = [];
    $l = lang();
    if (!isset($dict[$l])) { $f = __DIR__ . "/lang/$l.php"; $dict[$l] = is_file($f) ? require $f : []; }
    if (!isset($dict['az'])) $dict['az'] = require __DIR__ . '/lang/az.php';
    return $dict[$l][$key] ?? $dict['az'][$key] ?? $key;
}

/* ---------------- ünvanlar ---------------- */
/** url('product/osb') → /yeni/ru/product/osb/ */
function url(string $path = '', ?string $lng = null): string {
    $lng  = $lng ?? lang();
    $path = trim($path, '/');
    $pre  = $lng === DEFAULT_LANG ? '' : '/' . $lng;
    return base_url() . $pre . '/' . ($path === '' ? '' : $path . '/');
}
function asset(string $p): string {
    $f = ROOT_DIR . '/assets/' . $p;
    return base_url() . '/assets/' . $p . (is_file($f) ? '?v=' . filemtime($f) : '');
}
function img(?string $rel): string {
    $rel = trim((string)$rel);
    if ($rel === '') return base_url() . '/assets/img/placeholder.svg';
    if (preg_match('~^https?://~', $rel)) return $rel;
    return base_url() . '/assets/img/' . ltrim($rel, '/');
}

/* ---------------- baza ---------------- */
function db(): ?PDO {
    static $p = null;
    if ($p !== null) return $p ?: null;
    try { $p = require ROOT_DIR . '/admin/db.php'; $p->query('SELECT 1 FROM settings LIMIT 1'); }
    catch (Throwable $e) { $p = false; }
    return $p ?: null;
}

function setting(string $k, string $default = ''): string {
    static $all = null;
    if ($all === null) {
        $all = [];
        if ($d = db()) { try { foreach ($d->query('SELECT k, v FROM settings') as $r) $all[$r['k']] = (string)$r['v']; } catch (Throwable $e) {} }
    }
    $v = $all[$k] ?? '';
    return $v !== '' ? $v : (FALLBACK[$k] ?? $default);
}

/** Dilə görə mətn; cari dildə boşdursa AZ */
function text(string $k, string $default = ''): string {
    static $cache = [];
    foreach ([lang(), DEFAULT_LANG] as $l) {
        if (!isset($cache[$l])) {
            $cache[$l] = [];
            if ($d = db()) {
                try { $st = $d->prepare('SELECT k, v FROM texts WHERE lang=?'); $st->execute([$l]);
                      foreach ($st as $r) $cache[$l][$r['k']] = (string)$r['v']; } catch (Throwable $e) {}
            }
        }
        if (($cache[$l][$k] ?? '') !== '') return $cache[$l][$k];
    }
    return $default;
}

/* ---------------- mətn köməkçiləri ---------------- */
/** "5 товаров" / "1 товар" / "3 товара" — RU üçün düzgün hal, AZ-da sadəcə "məhsul" */
function items_label(int $n): string {
    if (lang() !== 'ru') return $n . ' ' . t('label.items');
    $m10 = $n % 10; $m100 = $n % 100;
    $w = ($m10 === 1 && $m100 !== 11) ? 'товар' : (($m10 >= 2 && $m10 <= 4 && ($m100 < 12 || $m100 > 14)) ? 'товара' : 'товаров');
    return $n . ' ' . $w;
}
function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/** HTML → sadə mətn (meta description üçün): bloklar arasında boşluq qalır, &amp; kimi entity-lər açılır (e() sonra bir dəfə escape edir) */
function plain_text(string $html): string {
    $html = (string)preg_replace('~<(br|/?(p|h[1-6]|li|ul|ol|div|blockquote|tr|td|th|table))\b[^>]*>~i', ' $0', $html);
    return trim((string)preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
}

/** "a | b | c" sətirlərini massivə çevirir */
function lines(string $s, int $cols = 2): array {
    $out = [];
    foreach (preg_split('/\R/u', trim($s)) as $ln) {
        if (trim($ln) === '') continue;
        $parts = array_map('trim', explode('|', $ln));
        $out[] = array_pad($parts, $cols, '');
    }
    return $out;
}

/** "*söz*" → vurğulu span (qalan hər şey escape olunur) */
function mark(string $s): string {
    return preg_replace('/\*(.+?)\*/u', '<span>$1</span>', e($s));
}

/** Sadə işarələmə: "## Başlıq", "- pünkt", boş sətir — abzas */
function simple_md(string $s): string {
    $html = ''; $list = false;
    foreach (preg_split('/\R/u', trim($s)) as $ln) {
        $ln = trim($ln);
        if (preg_match('/^- (.+)/u', $ln, $m)) {
            if (!$list) { $html .= '<ul class="checks">'; $list = true; }
            $html .= '<li>' . e($m[1]) . '</li>'; continue;
        }
        if ($list) { $html .= '</ul>'; $list = false; }
        if ($ln === '') continue;
        if (preg_match('/^## (.+)/u', $ln, $m)) $html .= '<h2>' . e($m[1]) . '</h2>';
        else $html .= '<p>' . e($ln) . '</p>';
    }
    if ($list) $html .= '</ul>';
    return $html;
}

/**
 * Təhlükəsiz HTML: yalnız ağ siyahıdakı teqlər və atributlar qalır.
 * WordPress-dən gələn və paneldə yazılan məhsul təsvirləri üçün.
 */
function sanitize_html(string $html): string {
    $html = trim($html);
    if ($html === '' || !class_exists('DOMDocument')) return strip_tags($html, '<p><br><strong><b><em><i><ul><ol><li><h2><h3><h4><table><thead><tbody><tr><th><td><a>');
    $allow = [
        'p' => [], 'br' => [], 'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [],
        'ul' => [], 'ol' => [], 'li' => [], 'h2' => [], 'h3' => [], 'h4' => [], 'blockquote' => [],
        'table' => [], 'thead' => [], 'tbody' => [], 'tr' => [], 'th' => ['colspan', 'rowspan'], 'td' => ['colspan', 'rowspan'],
        'a' => ['href', 'title'], 'img' => ['src', 'alt', 'width', 'height'], 'span' => [], 'div' => [],
    ];
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8"?><div id="__r">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    $root = $doc->getElementById('__r');
    if (!$root) return '';

    $walk = function (DOMNode $node) use (&$walk, $allow, $doc) {
        for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
            $c = $node->childNodes->item($i);
            if ($c instanceof DOMComment) { $node->removeChild($c); continue; }
            if (!($c instanceof DOMElement)) continue;
            $tag = strtolower($c->tagName);
            if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'svg', 'noscript'], true)) {
                $node->removeChild($c); continue;
            }
            $walk($c);
            if (!isset($allow[$tag])) {            // teqi açırıq, məzmunu saxlayırıq
                while ($c->firstChild) $node->insertBefore($c->firstChild, $c);
                $node->removeChild($c); continue;
            }
            foreach (iterator_to_array($c->attributes) as $a) {
                $name = strtolower($a->name);
                if (!in_array($name, $allow[$tag], true)) { $c->removeAttribute($a->name); continue; }
                if (in_array($name, ['href', 'src'], true) && !preg_match('~^(https?:|/|#|mailto:|tel:|assets/)~i', trim($a->value))) {
                    $c->removeAttribute($a->name);
                }
            }
            if ($tag === 'a') { $c->setAttribute('rel', 'noopener'); }
        }
    };
    $walk($root);
    $out = '';
    foreach ($root->childNodes as $c) $out .= $doc->saveHTML($c);
    $out = preg_replace('~<(p|div|span)>\s*(&nbsp;|\xC2\xA0)?\s*</\1>~u', '', $out);   // boş bloklar
    return trim((string)$out);
}

/* ---------------- kataloq məlumatları ---------------- */
function categories(bool $all = false): array {
    static $c = null;
    if ($c !== null && !$all) return $c;
    $d = db(); if (!$d) return [];
    $st = $d->prepare('SELECT c.*, COALESCE(NULLIF(i.name, \'\'), a.name, c.slug) AS name, COALESCE(i.descr, a.descr) AS descr,
            i.meta_title, i.meta_desc,
            (SELECT COUNT(*) FROM product_category pc JOIN products p ON p.id = pc.product_id WHERE pc.category_id = c.id AND p.visible = 1) AS cnt
        FROM categories c
        LEFT JOIN category_i18n i ON i.category_id = c.id AND i.lang = ?
        LEFT JOIN category_i18n a ON a.category_id = c.id AND a.lang = ?
        ' . ($all ? '' : 'WHERE c.visible = 1') . ' ORDER BY c.sort, c.id');
    $st->execute([lang(), DEFAULT_LANG]);
    $rows = $st->fetchAll();
    if (!$all) $c = $rows;
    return $rows;
}
function category_by_slug(string $slug): ?array {
    foreach (categories() as $c) if ($c['slug'] === $slug) return $c;
    return null;
}

/** Məhsul sorğusu: ['cat' => slug, 'brand' => .., 'q' => .., 'featured' => 1, 'ids' => [], 'limit' => n, 'exclude' => id] */
function products(array $o = []): array {
    $d = db(); if (!$d) return [];
    $w = ['p.visible = 1']; $args = [lang(), DEFAULT_LANG];
    if (!empty($o['cat'])) {
        $w[] = 'p.id IN (SELECT pc.product_id FROM product_category pc JOIN categories c ON c.id = pc.category_id WHERE c.slug = ?)';
        $args[] = $o['cat'];
    }
    if (!empty($o['brand']))    { $w[] = 'p.brand = ?'; $args[] = $o['brand']; }
    if (!empty($o['featured'])) { $w[] = 'p.featured = 1'; }
    if (!empty($o['exclude']))  { $w[] = 'p.id <> ?'; $args[] = (int)$o['exclude']; }
    if (!empty($o['slugs']))    { $w[] = 'p.slug IN (' . implode(',', array_fill(0, count($o['slugs']), '?')) . ')'; array_push($args, ...$o['slugs']); }
    if (($o['q'] ?? '') !== '') {
        $w[] = '(LOWER(i.title) LIKE ? OR LOWER(a.title) LIKE ? OR LOWER(p.brand) LIKE ? OR LOWER(i.short) LIKE ?)';
        $q = '%' . mb_strtolower((string)$o['q']) . '%';
        array_push($args, $q, $q, $q, $q);
    }
    $sql = 'SELECT p.*, COALESCE(NULLIF(i.title, \'\'), a.title, p.slug) AS title,
                   COALESCE(NULLIF(i.short, \'\'), a.short) AS short
            FROM products p
            LEFT JOIN product_i18n i ON i.product_id = p.id AND i.lang = ?
            LEFT JOIN product_i18n a ON a.product_id = p.id AND a.lang = ?
            WHERE ' . implode(' AND ', $w) . ' ORDER BY p.sort, p.id';
    if (!empty($o['limit'])) $sql .= ' LIMIT ' . (int)$o['limit'];
    $st = $d->prepare($sql); $st->execute($args);
    $rows = $st->fetchAll();
    if (!empty($o['slugs'])) {           // verilən sıra ilə
        $pos = array_flip($o['slugs']);
        usort($rows, fn($x, $y) => ($pos[$x['slug']] ?? 0) <=> ($pos[$y['slug']] ?? 0));
    }
    return $rows;
}

function product_by_slug(string $slug): ?array {
    $d = db(); if (!$d) return null;
    $st = $d->prepare('SELECT p.*,
            COALESCE(NULLIF(i.title, \'\'), a.title) AS title, COALESCE(NULLIF(i.short, \'\'), a.short) AS short,
            COALESCE(NULLIF(i.body, \'\'), a.body) AS body, COALESCE(NULLIF(i.specs, \'\'), a.specs) AS specs,
            i.meta_title, i.meta_desc
        FROM products p
        LEFT JOIN product_i18n i ON i.product_id = p.id AND i.lang = ?
        LEFT JOIN product_i18n a ON a.product_id = p.id AND a.lang = ?
        WHERE p.slug = ? AND p.visible = 1 LIMIT 1');
    $st->execute([lang(), DEFAULT_LANG, $slug]);
    $p = $st->fetch();
    if (!$p) return null;
    $st = $d->prepare('SELECT c.slug FROM product_category pc JOIN categories c ON c.id = pc.category_id WHERE pc.product_id = ? ORDER BY c.sort');
    $st->execute([$p['id']]);
    $p['cats'] = array_column($st->fetchAll(), 'slug');
    $p['gallery_list'] = array_values(array_filter((array)json_decode((string)$p['gallery'], true)));
    return $p;
}

/** Mətn səhifəsi (pages cədvəli); cari dildə boş sahələr AZ-dan */
function page_by_slug(string $slug): ?array {
    $d = db(); if (!$d) return null;
    try {
        $st = $d->prepare("SELECT p.*, COALESCE(NULLIF(i.title, ''), a.title) AS title, COALESCE(NULLIF(i.lead, ''), a.lead) AS `lead`,
                COALESCE(NULLIF(i.body, ''), a.body) AS body, i.meta_title, i.meta_desc
            FROM pages p LEFT JOIN page_i18n i ON i.page_id = p.id AND i.lang = ? LEFT JOIN page_i18n a ON a.page_id = p.id AND a.lang = ?
            WHERE p.slug = ? AND p.visible = 1 LIMIT 1");
        $st->execute([lang(), DEFAULT_LANG, $slug]);
        return $st->fetch() ?: null;
    } catch (Throwable $e) { return null; }
}
/** Menyuda göstərilən səhifələr */
function menu_pages(): array {
    static $m = null;
    if ($m !== null) return $m;
    $d = db(); if (!$d) return $m = [];
    try {
        $st = $d->prepare("SELECT p.slug, COALESCE(NULLIF(i.title, ''), a.title) AS title FROM pages p
            LEFT JOIN page_i18n i ON i.page_id = p.id AND i.lang = ? LEFT JOIN page_i18n a ON a.page_id = p.id AND a.lang = ?
            WHERE p.visible = 1 AND p.in_menu = 1 AND p.slug <> 'haqqimizda' ORDER BY p.sort, p.id");
        $st->execute([lang(), DEFAULT_LANG]);
        return $m = $st->fetchAll();
    } catch (Throwable $e) { return $m = []; }
}

/** "17 ₼" / "9.50 ₼" → 17.0 / 9.5 (schema.org üçün); qiymət yoxdursa null */
function price_number(string $p): ?float {
    if (!preg_match('/\d+(?:[.,]\d+)?/', str_replace(' ', '', $p), $m)) return null;
    $v = (float)str_replace(',', '.', $m[0]);
    return $v > 0 ? $v : null;
}

function brands(): array {
    $d = db(); if (!$d) return [];
    return array_column($d->query("SELECT brand, COUNT(*) n FROM products WHERE visible = 1 AND brand <> '' GROUP BY brand ORDER BY brand")->fetchAll(), 'n', 'brand');
}

/* ---------------- əlaqə ---------------- */
function wa_number(): string { return preg_replace('/\D/', '', setting('whatsapp')); }
function wa_link(string $msg = ''): string {
    return 'https://wa.me/' . wa_number() . ($msg !== '' ? '?text=' . rawurlencode($msg) : '');
}
function tel_link(): string { return 'tel:' . preg_replace('/[^\d+]/', '', setting('phone')); }

/* ---------------- poçt ---------------- */
/** SMTP qurulubsa onunla, yoxdursa mail() ilə göndərir */
function send_mail(string $to, string $subject, string $body, string $replyTo = '', string &$err = ''): bool {
    if (setting('smtp_host') !== '') return smtp_send($to, $subject, $body, $replyTo, $err);
    $domain  = 'globalprocurement.az';     // sabit: Host başlığından asılı olmasın (SPF / saxtalaşdırma)
    $headers = 'From: =?UTF-8?B?' . base64_encode('Global Procurement') . "?= <no-reply@{$domain}>\r\n"
             . "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";
    if ($replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) $headers .= "Reply-To: $replyTo\r\n";
    $ok = @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);
    if (!$ok) $err = 'mail() işləmədi';
    return $ok;
}

function smtp_send(string $to, string $subject, string $body, string $replyTo = '', string &$err = ''): bool {
    $host = setting('smtp_host');
    if ($host === '') { $err = 'SMTP-сервер не заполнен'; return false; }
    $port   = (int)(setting('smtp_port') ?: 587);
    $user   = setting('smtp_user');
    $pass   = setting('smtp_pass');
    $secure = setting('smtp_secure') ?: 'tls';
    $from   = setting('smtp_from') ?: $user;
    $fname  = setting('smtp_from_name') ?: 'Global Procurement';
    $target = $secure === 'ssl' ? "ssl://$host" : $host;

    $fp = @fsockopen($target, $port, $eno, $estr, 15);
    if (!$fp) { $err = "Подключение не удалось: $estr ($eno)"; return false; }
    stream_set_timeout($fp, 15);
    $get = function () use ($fp) { $d = ''; while (($l = fgets($fp, 515)) !== false) { $d .= $l; if (strlen($l) < 4 || $l[3] === ' ') break; } return $d; };
    $cmd = function ($c) use ($fp, $get) { fwrite($fp, $c . "\r\n"); return $get(); };
    $ok  = function ($r, $code) { return str_starts_with(trim((string)$r), $code); };

    $get();
    $cmd('EHLO globalprocurement.az');
    if ($secure === 'tls') {
        if (!$ok($cmd('STARTTLS'), '220') || !stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            $err = 'STARTTLS не удалось'; fclose($fp); return false;
        }
        $cmd('EHLO globalprocurement.az');
    }
    if ($user !== '') {
        $cmd('AUTH LOGIN'); $cmd(base64_encode($user));
        if (!$ok($cmd(base64_encode($pass)), '235')) { $err = 'Авторизация SMTP не прошла'; fclose($fp); return false; }
    }
    $cmd("MAIL FROM:<$from>");
    $cmd("RCPT TO:<$to>");
    if (!$ok($cmd('DATA'), '354')) { $err = 'Сервер не принял DATA'; fclose($fp); return false; }
    $hdr = "From: =?UTF-8?B?" . base64_encode($fname) . "?= <$from>\r\nTo: <$to>\r\n"
         . ($replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL) ? "Reply-To: <$replyTo>\r\n" : '')
         . "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\nMIME-Version: 1.0\r\n"
         . "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
    $r = $cmd($hdr . str_replace("\r\n.", "\r\n..", str_replace("\n", "\r\n", str_replace("\r\n", "\n", $body))) . "\r\n.");
    $cmd('QUIT'); fclose($fp);
    if (!$ok($r, '250')) { $err = 'Письмо не отправлено: ' . trim((string)$r); return false; }
    return true;
}
