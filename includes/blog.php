<?php
/*
 * Bloq: məqalələr (posts + post_i18n).
 *   /blog/          siyahı           pages/blog.php
 *   /blog/<slug>/   məqalə           pages/post.php
 *   admin → «Блог»                   idarəetmə
 * Cədvəllər lazım olanda avtomatik yaradılır (deploy-dan sonra install.php lazım deyil),
 * ilk dəfə isə admin/blog-pack.php-dən 3 başlanğıc məqalə yazılır.
 */
declare(strict_types=1);

require_once ROOT_DIR . '/admin/schema.php';   // cms_create_tables()

const BLOG_SCHEMA_V = '1';
const BLOG_MAX_PRODUCTS = 8;        // məqalənin altında ən çox bu qədər məhsul kartı

/** settings cədvəlində sadə bayraq (bloqun sxem versiyası, ilkin məzmun yazılıbmı) */
function blog_flag(PDO $d, string $k, ?string $set = null): string {
    $st = $d->prepare('SELECT v FROM settings WHERE k = ? LIMIT 1');
    $st->execute([$k]);
    $cur = $st->fetchColumn();
    if ($set === null) return $cur === false ? '' : (string)$cur;
    if ($cur === false) {
        try {
            $d->prepare('INSERT INTO settings (k, v) VALUES (?, ?)')->execute([$k, $set]);
            return $set;
        } catch (PDOException $e) {
            if ((string)$e->getCode() !== '23000') throw $e;     // paralel sorğu sətri artıq yazıb — yeniləyirik
        }
    }
    $d->prepare('UPDATE settings SET v = ? WHERE k = ?')->execute([$set, $k]);
    return $set;
}

/** Cədvəllər yoxdursa yaradılır; bloq heç vaxt doldurulmayıbsa başlanğıc məqalələr yazılır. Hər sorğuda bir dəfə. */
function blog_ensure(): bool {
    static $ok = null;
    if ($ok !== null) return $ok;
    $d = db();
    if (!$d) return $ok = false;
    try {
        if (blog_flag($d, 'blog_v') !== BLOG_SCHEMA_V) {
            cms_create_tables($d);                       // CREATE TABLE IF NOT EXISTS — mövcud cədvəllərə toxunmur
            blog_flag($d, 'blog_v', BLOG_SCHEMA_V);
        }
        if (blog_flag($d, 'blog_seeded') !== '1') {
            if ((int)$d->query('SELECT COUNT(*) FROM posts')->fetchColumn() === 0) {
                require_once ROOT_DIR . '/admin/blog-pack.php';
                blog_pack_insert($d);
            }
            blog_flag($d, 'blog_seeded', '1');           // admin bütün məqalələri silsə, yenidən yaranmır
        }
        return $ok = true;
    } catch (Throwable $e) {
        error_log('blog_ensure: ' . $e->getMessage());
        return $ok = false;
    }
}

/** Dərc olunmuş məqalələr (yeni əvvəl); cari dildə boş sahələr AZ-dan */
function blog_posts(int $limit = 60, int $exclude = 0): array {
    if (!blog_ensure()) return [];
    try {
        $st = db()->prepare("SELECT p.*, COALESCE(NULLIF(i.title, ''), NULLIF(a.title, ''), p.slug) AS title, COALESCE(NULLIF(i.lead, ''), a.lead) AS `lead`
            FROM posts p LEFT JOIN post_i18n i ON i.post_id = p.id AND i.lang = ? LEFT JOIN post_i18n a ON a.post_id = p.id AND a.lang = ?
            WHERE p.visible = 1 AND p.id <> ? ORDER BY p.published_at DESC, p.id DESC LIMIT " . max(1, $limit));
        $st->execute([lang(), DEFAULT_LANG, $exclude]);
        return $st->fetchAll();
    } catch (Throwable $e) { return []; }
}

function blog_post(string $slug): ?array {
    if (!blog_ensure()) return null;
    try {
        $st = db()->prepare("SELECT p.*, COALESCE(NULLIF(i.title, ''), NULLIF(a.title, ''), p.slug) AS title, COALESCE(NULLIF(i.lead, ''), a.lead) AS `lead`,
                COALESCE(NULLIF(i.body, ''), a.body) AS body, i.meta_title, i.meta_desc
            FROM posts p LEFT JOIN post_i18n i ON i.post_id = p.id AND i.lang = ? LEFT JOIN post_i18n a ON a.post_id = p.id AND a.lang = ?
            WHERE p.slug = ? AND p.visible = 1 LIMIT 1");
        $st->execute([lang(), DEFAULT_LANG, $slug]);
        return $st->fetch() ?: null;
    } catch (Throwable $e) { return null; }
}

/** Məqalənin altındakı məhsullar (sıra admində seçildiyi kimi) */
function blog_products(array $post): array {
    $slugs = array_values(array_filter(array_map('strval', (array)json_decode((string)($post['products'] ?? ''), true))));
    return $slugs ? array_slice(products(['slugs' => $slugs]), 0, BLOG_MAX_PRODUCTS) : [];   // gizli məhsullar yer tutmur
}

/** 2026-10-16 → 16.10.2026 */
function blog_date(string $d): string {
    return preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $d, $m) ? "$m[3].$m[2].$m[1]" : '';
}

/** Siyahı kartı */
function blog_card(array $p, int $i = 0): string {
    $u = url('blog/' . $p['slug']);
    return '<article class="bcard" style="animation-delay:' . ($i * 50) . 'ms">'
        . '<a href="' . e($u) . '" class="bim">' . ($p['image'] ? '<img src="' . e(img($p['image'])) . '" alt="' . e($p['title']) . '" loading="lazy">' : '') . '</a>'
        . '<div class="bbd"><time datetime="' . e(substr((string)$p['published_at'], 0, 10)) . '">' . e(blog_date((string)$p['published_at'])) . '</time>'
        . '<a href="' . e($u) . '"><h3>' . e($p['title']) . '</h3></a>'
        . (trim((string)$p['lead']) !== '' ? '<p>' . e($p['lead']) . '</p>' : '')
        . '<a href="' . e($u) . '" class="bmore">' . e(t('blog.read')) . ' →</a></div></article>';
}
