<?php
/* Admin panelinin ortaq köməkçiləri: PDO, auth, CSRF, yükləmə, layout. */
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';   // e(), img(), setting(), send_mail(), sanitize_html()...
require_once __DIR__ . '/schema.php';

function pdo(): PDO {
    static $p = null;
    if (!$p) { $p = require __DIR__ . '/db.php'; }
    return $p;
}
function cfg(string $k, $default = null) { return site_cfg($k, $default); }
function redirect(string $to): void { header('Location: ' . $to); exit; }

/* ---------------- sessiya / auth ---------------- */
function is_https(): bool {
    $h = $_SERVER['HTTPS'] ?? '';
    return ($h !== '' && strtolower((string)$h) !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (int)($_SERVER['SERVER_PORT'] ?? 0) === 443;
}

const SESSION_IDLE_MAX = 7200;      // 2 saat
const SESSION_ABS_MAX  = 43200;     // 12 saat

function boot_session(): void {
    if (session_status() !== PHP_SESSION_NONE) return;
    session_name('gpadm');
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Strict', 'secure' => is_https()]);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();
    $now = time();
    if (isset($_SESSION['admin'])) {
        $last  = (int)($_SESSION['seen'] ?? $now);
        $start = (int)($_SESSION['start'] ?? $now);
        if ($now - $last > SESSION_IDLE_MAX || $now - $start > SESSION_ABS_MAX) { $_SESSION = []; session_regenerate_id(true); }
    }
    $_SESSION['seen'] = $now;
}

function current_admin(): ?array { boot_session(); return $_SESSION['admin'] ?? null; }
function require_login(): array {
    $a = current_admin();
    if (!$a) redirect('index.php?section=login');
    return $a;
}

const LOGIN_MAX_TRIES = 6;
const LOGIN_WINDOW    = 900;

function login_blocked(): int {
    boot_session();
    $now  = time();
    $hits = array_values(array_filter((array)($_SESSION['login_fails'] ?? []), function ($t) use ($now) { return is_int($t) && $t > $now - LOGIN_WINDOW; }));
    $_SESSION['login_fails'] = $hits;
    if (count($hits) < LOGIN_MAX_TRIES) return 0;
    return max(1, (int)(min($hits) + LOGIN_WINDOW - $now));
}
function login_fail(): void { boot_session(); $_SESSION['login_fails'][] = time(); }

function attempt_login(string $email, string $pass): bool {
    if (login_blocked() > 0) return false;
    $st = pdo()->prepare('SELECT * FROM admins WHERE email = ? LIMIT 1');
    $st->execute([trim($email)]);
    $u = $st->fetch();
    $hash = $u['pass_hash'] ?? '$2y$12$' . str_repeat('.', 53);
    $ok   = password_verify($pass, $hash);
    if ($u && $ok && (int)($u['active'] ?? 1) === 1) {
        boot_session();
        session_regenerate_id(true);
        unset($_SESSION['login_fails']);
        $_SESSION['start'] = time();
        $_SESSION['seen']  = time();
        $_SESSION['admin'] = ['id' => (int)$u['id'], 'email' => $u['email'], 'name' => $u['name'] ?? '', 'role' => $u['role'] ?? 'editor'];
        try { pdo()->prepare('UPDATE admins SET last_login=? WHERE id=?')->execute([date('Y-m-d H:i'), (int)$u['id']]); } catch (Throwable $e) {}
        return true;
    }
    login_fail();
    return false;
}

function is_admin(): bool { $a = current_admin(); return is_array($a) && ($a['role'] ?? '') === 'admin'; }
function logout(): void {
    boot_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/* ---------------- CSRF ---------------- */
function csrf_token(): string {
    boot_session();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">'; }
function csrf_check(): void {
    boot_session();
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') return;
    $have = (string)($_SESSION['csrf'] ?? '');
    $sent = (string)($_POST['_csrf'] ?? '');
    if ($have === '' || $sent === '' || !hash_equals($have, $sent)) {
        http_response_code(400);
        header('Content-Type: text/plain; charset=utf-8');
        exit('CSRF token səhvdir. Səhifəni yeniləyin.');
    }
}

/* ---------------- flash ---------------- */
function flash(string $msg, string $type = 'ok'): void { boot_session(); $_SESSION['flash'][] = [$type, $msg]; }
function flash_render(): string {
    boot_session(); $out = '';
    foreach ($_SESSION['flash'] ?? [] as [$t, $m]) {
        $out .= '<div class="flash" style="background:' . ($t === 'err' ? '#b91c1c' : '#0f8a4f') . '">' . e($m) . '</div>';
    }
    $_SESSION['flash'] = [];
    return $out;
}

/* ---------------- key-value ---------------- */
function kv_get(string $key, string $default = ''): string {
    try { $st = pdo()->prepare('SELECT v FROM settings WHERE k = ? LIMIT 1'); $st->execute([$key]); $r = $st->fetch(); return $r ? (string)$r['v'] : $default; }
    catch (Throwable $e) { return $default; }
}
function kv_set(string $key, string $val): void {
    $db = pdo();
    $st = $db->prepare('SELECT 1 FROM settings WHERE k = ? LIMIT 1'); $st->execute([$key]);
    if ($st->fetch()) $db->prepare('UPDATE settings SET v = ? WHERE k = ?')->execute([$val, $key]);
    else              $db->prepare('INSERT INTO settings (k, v) VALUES (?, ?)')->execute([$key, $val]);
}
function txt_get(string $key, string $lang): string {
    try { $st = pdo()->prepare('SELECT v FROM texts WHERE k = ? AND lang = ? LIMIT 1'); $st->execute([$key, $lang]); $r = $st->fetch(); return $r ? (string)$r['v'] : ''; }
    catch (Throwable $e) { return ''; }
}
function txt_set(string $key, string $lang, string $val): void {
    $db = pdo();
    $st = $db->prepare('SELECT 1 FROM texts WHERE k = ? AND lang = ? LIMIT 1'); $st->execute([$key, $lang]);
    if ($st->fetch()) $db->prepare('UPDATE texts SET v = ? WHERE k = ? AND lang = ?')->execute([$val, $key, $lang]);
    else              $db->prepare('INSERT INTO texts (lang, k, v) VALUES (?,?,?)')->execute([$lang, $key, $val]);
}

/* ---------------- fayl yükləmə ---------------- */
const UPLOAD_TYPES = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif'];
const UPLOAD_MAX_BYTES = 15 * 1024 * 1024;
/* SVG qəsdən yoxdur: içində <script> ola bilər. */

/** Yüklənmiş faylı assets/img/<dir>/ altına köçürür, nisbi yolu qaytarır */
function admin_move(string $tmp, string $name, string $dir = 'uploads'): ?string {
    if (!is_uploaded_file($tmp) || filesize($tmp) > UPLOAD_MAX_BYTES) return null;
    $name = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($name));
    $name = preg_replace('/\.{2,}/', '.', (string)$name);
    $ext  = strtolower(pathinfo((string)$name, PATHINFO_EXTENSION));
    if (!isset(UPLOAD_TYPES[$ext])) return null;
    if (@getimagesize($tmp) === false) return null;
    if (class_exists('finfo')) {
        $mime = (string)(new finfo(FILEINFO_MIME_TYPE))->file($tmp);
        if ($mime !== '' && !str_starts_with($mime, 'image/')) return null;
    }
    $abs = dirname(__DIR__) . '/assets/img/' . $dir;
    if (!is_dir($abs)) @mkdir($abs, 0755, true);
    $base = mb_substr((string)pathinfo((string)$name, PATHINFO_FILENAME), 0, 50);
    $safe = date('Ymd') . '_' . bin2hex(random_bytes(3)) . '_' . $base . '.' . $ext;
    if (!move_uploaded_file($tmp, "$abs/$safe")) return null;
    @chmod("$abs/$safe", 0644);
    return $dir . '/' . $safe;
}
function admin_upload(string $field, string $dir = 'uploads'): ?string {
    if (empty($_FILES[$field]['name']) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return null;
    return admin_move($_FILES[$field]['tmp_name'], (string)$_FILES[$field]['name'], $dir);
}
/** <input type=file multiple name="x[]"> */
function admin_upload_many(string $field, string $dir = 'uploads'): array {
    $out = [];
    if (empty($_FILES[$field]['name']) || !is_array($_FILES[$field]['name'])) return $out;
    foreach ($_FILES[$field]['name'] as $i => $n) {
        if (($_FILES[$field]['error'][$i] ?? 1) !== UPLOAD_ERR_OK) continue;
        if ($p = admin_move($_FILES[$field]['tmp_name'][$i], (string)$n, $dir)) $out[] = $p;
    }
    return $out;
}
/** Yalnız assets/img daxilində şəkil yolu qəbul olunur */
function safe_img_path(string $p): string {
    $p = ltrim(str_replace('\\', '/', trim($p)), '/');
    $p = preg_replace('~\.\.+/~', '', $p);
    if (!preg_match('~^[A-Za-z0-9._/-]{1,200}$~', (string)$p)) return '';
    return in_array(strtolower((string)pathinfo((string)$p, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true) ? (string)$p : '';
}
function slugify(string $s): string {
    $map = ['ə' => 'e', 'Ə' => 'e', 'ı' => 'i', 'İ' => 'i', 'ö' => 'o', 'Ö' => 'o', 'ü' => 'u', 'Ü' => 'u', 'ğ' => 'g', 'Ğ' => 'g', 'ş' => 's', 'Ş' => 's', 'ç' => 'c', 'Ç' => 'c',
            'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'sch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ь'=>'','ъ'=>''];
    $s = mb_strtolower(strtr($s, $map));
    $s = strtr($s, $map);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim((string)$s, '-');
}

/* ---------------- dillər ---------------- */
const CMS_LANGS = ['az' => 'AZ', 'ru' => 'RU'];
function cur_lang(): string {
    boot_session();
    if (isset($_GET['lang']) && isset(CMS_LANGS[$_GET['lang']])) $_SESSION['cms_lang'] = $_GET['lang'];
    return $_SESSION['cms_lang'] ?? 'az';
}

/* ---------------- naviqasiya ---------------- */
const SECTIONS = [
    'overview'    => ['Обзор', '▤'],
    'products'    => ['Товары', '▦'],
    'categories'  => ['Категории', '❖'],
    'submissions' => ['Заявки', '✉'],
    'pages'       => ['Страницы', '❐'],
    'texts'       => ['Тексты сайта', '¶'],
    'images'      => ['Изображения', '❏'],
    'contacts'    => ['Контакты', '☏'],
    'seo'         => ['SEO', '☌'],
    'import'      => ['Импорт из WordPress', '⇩'],
    'smtp'        => ['Почта (SMTP)', '✉'],
    'security'    => ['Безопасность', '⚿'],
    'users'       => ['Пользователи', '☺'],
    'profile'     => ['Мой профиль', '☺'],
];
const GROUPS = [
    'ОСНОВНОЕ'  => ['overview', 'submissions'],
    'КАТАЛОГ'   => ['products', 'categories', 'import'],
    'КОНТЕНТ'   => ['pages', 'texts', 'images', 'contacts', 'seo'],
    'НАСТРОЙКИ' => ['smtp', 'security', 'users', 'profile'],
];
const ADMIN_ONLY = ['users', 'smtp', 'security', 'import'];
const LANG_SECTIONS = ['texts', 'seo'];

function require_admin(): void {
    require_login();
    if (!is_admin()) { flash('Недостаточно прав (нужна роль Администратор).', 'err'); redirect('index.php?section=overview'); }
}
/** SEO göstəricisi: yaşıl — hər ikisi normal uzunluqda, narıncı — var amma uzunluq uyğun deyil, boz — yoxdur */
function seo_dot(string $title, string $desc, string $label): string {
    $tl = mb_strlen(trim($title)); $dl = mb_strlen(trim($desc));
    if ($tl === 0 || $dl === 0) { $c = '#cbd5e1'; $tip = 'нет SEO'; }
    elseif ($tl >= 30 && $tl <= 65 && $dl >= 70 && $dl <= 165) { $c = '#16a34a'; $tip = 'SEO в порядке'; }
    else { $c = '#f59e0b'; $tip = "длина: title $tl, description $dl"; }
    return '<span title="' . e($label . ': ' . $tip) . '" style="display:inline-block;width:11px;height:11px;border-radius:50%;background:' . $c . ';margin-right:5px"></span>';
}

function unread_count(): int {
    try { return (int)pdo()->query('SELECT COUNT(*) c FROM submissions WHERE is_read=0')->fetch()['c']; } catch (Throwable $e) { return 0; }
}

function layout_top(string $active, string $title): void {
    $admin  = current_admin();
    $unread = unread_count();
    echo '<!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<meta name="robots" content="noindex, nofollow"><title>' . e($title) . ' — Global Procurement admin</title>';
    echo '<link rel="icon" href="../assets/img/favicon.png" type="image/png">';
    echo '<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">';
    echo '<style>' . admin_css() . '</style></head><body>';
    echo '<input type="checkbox" id="navtoggle" hidden><div class="wrap">';
    echo '<aside class="side"><div class="brand"><img src="../assets/img/logo-h-white.png" alt="Global Procurement Group" style="height:40px;width:auto"></div><nav>';
    foreach (GROUPS as $glabel => $keys) {
        echo '<div class="navgroup">' . e($glabel) . '</div>';
        foreach ($keys as $key) {
            if (in_array($key, ADMIN_ONLY, true) && !is_admin()) continue;
            [$label, $ic] = SECTIONS[$key];
            $badge = ($key === 'submissions' && $unread) ? '<b class="badge">' . $unread . '</b>' : '';
            echo '<a' . ($key === $active ? ' class="on"' : '') . ' href="index.php?section=' . $key . '"><i>' . $ic . '</i><span>' . e($label) . '</span>' . $badge . '</a>';
        }
    }
    echo '</nav><div class="who">Вы вошли как<br><b>' . e($admin['name'] ?: ($admin['email'] ?? '')) . '</b><br>'
       . '<span class="role">' . (($admin['role'] ?? '') === 'admin' ? 'Администратор' : 'Редактор') . '</span></div></aside>';
    echo '<main><header class="bar"><label for="navtoggle" class="burger" title="Меню"><span></span><span></span><span></span></label>';
    echo '<h1>' . e($title) . '</h1><div class="bar-r">';
    if (in_array($active, LANG_SECTIONS, true)) {
        $qs = $_GET; unset($qs['lang']);
        echo '<span class="langsw" title="Язык контента">';
        foreach (CMS_LANGS as $code => $label) {
            $qs['lang'] = $code;
            echo '<a' . ($code === cur_lang() ? ' class="on"' : '') . ' href="index.php?' . e(http_build_query($qs)) . '">' . $label . '</a>';
        }
        echo '</span> ';
    }
    echo '<a class="btn ghost" href="../" target="_blank">Открыть сайт</a> <a class="btn" href="index.php?section=logout">Выйти</a></div></header><div class="body">';
    echo flash_render();
}
function layout_bottom(): void {
    echo '</div></main><label for="navtoggle" class="scrim"></label></div><script src="admin.js?v=2"></script></body></html>';
}
function admin_logo(): string {
    return '<svg width="34" height="30" viewBox="0 0 48 40"><path d="M21 1 6 39h8.5L27.5 6z" fill="#47a7ff"/><path d="M30 9 19 39h8.5l7-18.5z" fill="#47a7ff"/><path d="M37 25l7 14H30z" fill="#8bc34a"/></svg>';
}

function admin_css(): string {
    return <<<CSS
*{box-sizing:border-box}
body{margin:0;font-family:"Plus Jakarta Sans",-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:#f3f6fb;color:#0e1a2f}
a{text-decoration:none;color:#1673cc}
.wrap{display:flex;min-height:100vh}
.side{width:258px;background:#14223d;color:#c5d0e3;display:flex;flex-direction:column;position:sticky;top:0;height:100vh;flex:none;z-index:30}
.brand{padding:18px 18px;border-bottom:1px solid #22355a;display:flex;gap:10px;align-items:center;color:#fff;font-weight:800;letter-spacing:.05em;font-size:14px;line-height:1.05}
.brand small{display:block;font-size:9.5px;letter-spacing:.28em;color:#8ea2c4;margin-top:2px}
.side nav{display:flex;flex-direction:column;padding:4px 0 14px;flex:1;overflow:auto}
.navgroup{color:#6f84a8;font-size:11px;letter-spacing:.09em;padding:15px 20px 5px;font-weight:700}
.side nav a{color:#b9c6dc;padding:10px 20px;font-size:14px;display:flex;gap:12px;align-items:center;border-left:3px solid transparent}
.side nav a i{width:18px;font-style:normal;opacity:.85;text-align:center}
.side nav a:hover{background:#1b2d50;color:#fff}
.side nav a.on{background:#1f8ef1;color:#fff;font-weight:700;border-left-color:#8bc34a}
.badge{margin-left:auto;background:#8bc34a;color:#14223d;font-size:11px;font-weight:800;padding:1px 8px;border-radius:20px}
.who{padding:16px 20px;font-size:12px;color:#8ea2c4;border-top:1px solid #22355a;line-height:1.5}
.who b{color:#fff}
.who .role{display:inline-block;margin-top:4px;background:#22355a;padding:2px 9px;border-radius:20px;font-size:11px}
main{flex:1;min-width:0}
.bar{display:flex;justify-content:space-between;align-items:center;gap:14px;padding:16px 26px;background:#fff;border-bottom:1px solid #e2e8f2;position:sticky;top:0;z-index:20}
.bar h1{margin:0;font-size:21px;letter-spacing:-.02em;flex:1}
.bar-r{display:flex;gap:8px;flex:none;align-items:center}
.langsw{display:inline-flex;gap:2px;border:1px solid #d5deea;border-radius:22px;padding:3px;margin-right:6px}
.langsw a{padding:6px 12px;border-radius:20px;font-size:13px;font-weight:700;color:#5b6f86}
.langsw a.on{background:#14223d;color:#fff}
.burger{display:none;flex-direction:column;gap:5px;cursor:pointer;padding:6px}
.burger span{width:22px;height:2px;background:#14223d;border-radius:2px}
.scrim{display:none}
.body{padding:24px 26px 60px;max-width:1180px}
.flash{color:#fff;padding:12px 16px;border-radius:10px;margin-bottom:14px;font-weight:600;font-size:14px}
.btn{display:inline-block;background:#1f8ef1;color:#fff;border:0;padding:10px 18px;border-radius:24px;font-weight:700;cursor:pointer;font-size:14px;font-family:inherit;line-height:1.3}
.btn:hover{background:#1673cc}
.btn.ghost{background:transparent;color:#14223d;border:1.5px solid #d5deea}
.btn.ghost:hover{background:#e6f2fe;border-color:#1f8ef1;color:#1673cc}
.btn.sm{padding:6px 12px;font-size:13px;border-radius:8px}
.btn.red{background:#b91c1c}.btn.red:hover{background:#991717}
.btn.green{background:#25a244}.btn.green:hover{background:#1d8738}
.btn.navy{background:#14223d}.btn.navy:hover{background:#0a1528}
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:22px}
.card{background:#fff;border:1px solid #e2e8f2;border-radius:14px;padding:20px 22px}
.card .n{font-size:34px;font-weight:800;color:#1f8ef1;line-height:1}
.card .l{color:#5b6f86;font-size:12.5px;text-transform:uppercase;letter-spacing:.04em;margin-top:6px}
.panel{background:#fff;border:1px solid #e2e8f2;border-radius:14px;padding:22px;margin-bottom:20px}
.panel h3{margin:0 0 6px;font-size:18px}
.panel h4{margin:22px 0 6px;font-size:15px;color:#14223d}
table{width:100%;border-collapse:collapse}
th,td{text-align:left;padding:10px 12px;border-bottom:1px solid #eef2f7;font-size:14px;vertical-align:middle}
th{color:#5b6f86;font-size:11.5px;text-transform:uppercase;letter-spacing:.04em}
.tablewrap{overflow-x:auto}
label{display:block;font-weight:600;margin:14px 0 6px;font-size:13.5px}
input[type=text],input[type=email],input[type=password],input[type=number],input[type=url],textarea,select{width:100%;padding:11px 13px;border:1px solid #d5deea;border-radius:9px;font-size:14px;font-family:inherit;background:#fbfcfe}
input:focus,textarea:focus,select:focus{outline:0;border-color:#1f8ef1;background:#fff;box-shadow:0 0 0 3px rgba(31,142,241,.12)}
textarea{min-height:90px;resize:vertical}
textarea.code{font-family:ui-monospace,Consolas,monospace;font-size:13px;min-height:240px}
.row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.row3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px}
.row4{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.muted{color:#5b6f86;font-size:13px}
.right{text-align:right}
.item{border:1px solid #eef2f7;border-radius:12px;padding:16px 18px;margin-bottom:14px;background:#fbfcfe}
.pill{display:inline-block;font-size:11px;padding:3px 10px;border-radius:20px;background:#e6f2fe;color:#1673cc;font-weight:700}
.pill.g{background:#eef7e3;color:#4d7a1f}.pill.gr{background:#eef2f7;color:#5b6f86}
.grid-media{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:14px}
.thumb{width:56px;height:56px;object-fit:contain;border-radius:8px;background:#f3f6fb;border:1px solid #e2e8f2}
.ltabs{display:flex;gap:4px;margin:18px 0 0;border-bottom:1px solid #e2e8f2}
.ltabs{border:0;margin:14px 0 4px}
.lpane{display:none;padding-top:4px}
.lpane.on{display:block}
.chips{display:flex;flex-wrap:wrap;gap:8px}
.chips label{margin:0;font-weight:500;padding:8px 12px;border:1px solid #d5deea;border-radius:20px;cursor:pointer;display:flex;gap:6px;align-items:center;font-size:13.5px}
.gal{display:flex;flex-wrap:wrap;gap:10px;margin-top:8px}
.gal div{position:relative;width:100px}
.gal img{width:100px;height:100px;object-fit:contain;background:#f3f6fb;border-radius:10px;border:1px solid #e2e8f2}
.gal label{margin:4px 0 0;font-weight:500;font-size:12px;display:flex;gap:4px;align-items:center}
.filters{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:14px}
.filters input,.filters select{width:auto;min-width:200px}
.log{background:#0e1a2f;color:#cfe3ff;font-family:ui-monospace,Consolas,monospace;font-size:12.5px;padding:16px;border-radius:12px;max-height:420px;overflow:auto;white-space:pre-wrap}
.login{min-height:100vh;display:grid;place-items:center;background:linear-gradient(150deg,#14223d,#0c1528 60%,#1f8ef1)}
.login .box{background:#fff;padding:36px 32px;border-radius:18px;width:380px;max-width:92vw;box-shadow:0 24px 70px rgba(0,0,0,.35)}
.login h2{margin:8px 0 2px;font-size:22px}
.login .sub{color:#5b6f86;margin-bottom:16px;font-size:14px}
@media(max-width:900px){
  .side{position:fixed;left:0;top:0;transform:translateX(-100%);transition:transform .25s;box-shadow:0 0 40px rgba(0,0,0,.3)}
  #navtoggle:checked ~ .wrap .side{transform:none}
  #navtoggle:checked ~ .wrap .scrim{display:block;position:fixed;inset:0;background:rgba(9,20,40,.45);z-index:25}
  .burger{display:flex}
  .body{padding:18px 16px 50px}
  .bar{padding:12px 16px}
  .bar h1{font-size:17px}
  .bar-r .btn.ghost{display:none}
  .row,.row3,.row4{grid-template-columns:1fr}
}
CSS;
}
