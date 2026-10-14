<?php
declare(strict_types=1);
require __DIR__ . '/lib.php';
boot_session();

$section = preg_replace('/[^a-z_]/', '', (string)($_GET['section'] ?? 'overview'));

/* ---------------- baza qurulmayıbsa ---------------- */
try { pdo()->query('SELECT 1 FROM admins LIMIT 1'); }
catch (Throwable $e) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<meta charset="utf-8"><style>body{font-family:system-ui,sans-serif;max-width:640px;margin:80px auto;padding:0 20px;line-height:1.6}code{background:#eef2f7;padding:2px 8px;border-radius:6px}</style>'
       . '<h2>Panel hələ quraşdırılmayıb</h2><p>1. <code>admin/config.sample.php</code> → <code>admin/config.php</code> kopyalayın və doldurun.</p>'
       . '<p>2. Bir dəfə açın: <code>/admin/install.php?token=…</code> (token config.php-dədir).</p>'
       . '<p class="muted">Xəta: ' . e($e->getMessage()) . '</p>';
    exit;
}

if ($section === 'logout') { logout(); redirect('index.php?section=login'); }

/* ---------------- login ---------------- */
if ($section === 'login') {
    if (current_admin()) redirect('index.php');
    $err = ''; $email = '';
    require_once __DIR__ . '/turnstile.php';
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $email = trim((string)($_POST['email'] ?? ''));
        // Köhnəlmiş səhifə (sessiya bitib) — 400 əvəzinə formu yeni tokenlə yenidən göstəririk; giriş cəhdi edilmir
        if (!csrf_ok()) $err = 'Страница входа устарела. Введите e-mail и пароль ещё раз.';
        elseif (($wait = login_blocked()) > 0) $err = 'Слишком много попыток входа. Повторите через ' . ceil($wait / 60) . ' мин.';
        elseif (!turnstile_verify((string)($_POST['cf-turnstile-response'] ?? ''))) $err = 'Подтвердите, что вы не робот.';
        elseif (attempt_login($email, (string)($_POST['password'] ?? ''), $why)) redirect('index.php');
        elseif ($why === 'off') $err = 'Учётная запись отключена. Администратор может включить её в разделе «Пользователи».';
        else $err = 'Неверный e-mail или пароль. Проверьте раскладку клавиатуры и Caps Lock.';
    }
    echo '<!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex">';
    echo '<link rel="icon" href="' . e(admin_asset('assets/img/favicon.png')) . '" type="image/png">';
    echo '<title>Вход — Global Procurement</title><link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">';
    echo '<style>' . admin_css() . '</style></head><body><div class="login"><form class="box" method="post">';
    echo '<div style="text-align:center"><img src="' . e(admin_asset('assets/img/logo.png')) . '" alt="Global Procurement Group" width="128" height="96" style="height:96px;width:auto"></div>';
    echo '<h2>Вход в панель</h2><div class="sub">Управление сайтом и каталогом</div>';
    if ($err) echo '<div class="flash" style="background:#b91c1c">' . e($err) . '</div>';
    echo csrf_field() . '<label>E-mail</label><input type="email" name="email" value="' . e($email) . '" required' . ($email === '' ? ' autofocus' : '') . ' autocomplete="username" spellcheck="false">'
       . '<label>Пароль</label><input type="password" name="password" required' . ($email === '' ? '' : ' autofocus') . ' autocomplete="current-password">';
    echo '<div style="margin-top:14px;display:flex;justify-content:center">'; turnstile_widget(); echo '</div>';
    echo '<button class="btn" style="width:100%;margin-top:18px" type="submit">Войти</button></form></div>';
    echo '<script src="' . e(admin_asset('admin/admin.js')) . '"></script></body></html>';
    exit;
}

/* ---------------- auth ---------------- */
require_login();
if (!isset(SECTIONS[$section])) $section = 'overview';
if (in_array($section, ADMIN_ONLY, true) && !is_admin()) { flash('Недостаточно прав (нужна роль Администратор).', 'err'); redirect('index.php?section=overview'); }
csrf_check();
$db = pdo();
$L  = cur_lang();

/* Mətn sahələri: açar => [ad, növ, izah] */
const TEXT_FIELDS = [
    'Общее' => [
        'site_name' => ['Название сайта', 'input', ''],
        'announce' => ['Полоса сверху (объявление)', 'input', 'Пусто — полоса скрыта'],
        'footer_text' => ['Текст в подвале', 'textarea', ''],
    ],
    'Главный экран' => [
        'hero_badge' => ['Плашка над заголовком', 'input', ''],
        'hero_title' => ['Заголовок', 'input', 'Слово в *звёздочках* выделяется синим'],
        'hero_lead' => ['Подзаголовок', 'textarea', ''],
        'trust' => ['4 преимущества под экраном', 'textarea', 'Строка: Заголовок | Подпись'],
        'brands' => ['Бренды в бегущей строке', 'input', 'Через запятую'],
    ],
    'Блоки главной' => [
        'cats_title' => ['Категории — заголовок', 'input', ''], 'cats_lead' => ['Категории — текст', 'input', ''],
        'guide_title' => ['Гид — заголовок', 'input', ''], 'guide_lead' => ['Гид — текст', 'input', ''],
        'products_title' => ['Товары — заголовок', 'input', ''],
        'osb_title' => ['OSB — заголовок', 'input', ''], 'osb_lead' => ['OSB — текст', 'input', ''],
        'about_title' => ['О компании — заголовок', 'input', ''], 'about_body' => ['О компании — текст', 'textarea', 'Пустая строка — новый абзац'],
        'stats' => ['Цифры', 'textarea', 'Строка: число | суффикс | подпись'],
        'distributors' => ['Дистрибуции', 'textarea', 'Строка: метка | бренд | описание'],
        'welder_title' => ['WELDER — заголовок', 'input', ''], 'welder_text' => ['WELDER — текст', 'textarea', ''],
        'welder_points' => ['WELDER — 4 пункта', 'textarea', 'Строка: Заголовок | Подпись'],
        'faq' => ['Вопросы и ответы', 'textarea', 'Строка: Вопрос | Ответ'],
        'cta_title' => ['Блок заявки — заголовок', 'input', ''], 'cta_text' => ['Блок заявки — текст', 'textarea', ''],
    ],
    'Страница «О компании»' => [
        'about_page' => ['Содержание', 'textarea', '## Заголовок · обычная строка — абзац · - пункт списка'],
    ],
];
const SEO_PAGES = ['home' => 'Главная', 'shop' => 'Каталог', 'about' => 'О компании', 'contact' => 'Контакты'];

/* ================== POST ================== */
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $act = (string)($_POST['action'] ?? '');

    /* ---------- TOVARLAR ---------- */
    if ($section === 'products') {
        $back = 'index.php?section=products';
        if ($act === 'save') {
            $id = (int)($_POST['id'] ?? 0);
            $tr = (array)($_POST['tr'] ?? []);
            $slug = slugify((string)($_POST['slug'] ?? ''));
            if ($slug === '') $slug = slugify((string)($tr['az']['title'] ?? ''));
            if ($slug === '') { flash('Укажите название (AZ) или slug.', 'err'); redirect($back . ($id ? '&edit=' . $id : '&new=1')); }
            $st = $db->prepare('SELECT id FROM products WHERE slug = ? AND id <> ?'); $st->execute([$slug, $id]);
            if ($st->fetch()) { flash('Такой slug уже занят: ' . $slug, 'err'); redirect($back . ($id ? '&edit=' . $id : '&new=1')); }

            $old = ['image' => '', 'gallery' => '[]'];
            if ($id) { $st = $db->prepare('SELECT image, gallery FROM products WHERE id=?'); $st->execute([$id]); $old = $st->fetch() ?: $old; }

            $image = safe_img_path((string)($_POST['image'] ?? ''));
            if ($up = admin_upload('image_file', 'products')) $image = $up;
            $gallery = array_values(array_filter((array)json_decode((string)$old['gallery'], true)));
            $remove = array_map('strval', (array)($_POST['gal_rm'] ?? []));
            $gallery = array_values(array_filter($gallery, function ($g) use ($remove) { return !in_array($g, $remove, true); }));
            $gallery = array_merge($gallery, admin_upload_many('gallery_files', 'products'));

            $f = [
                'slug' => $slug, 'brand' => trim((string)($_POST['brand'] ?? '')), 'image' => $image, 'gallery' => json_encode($gallery),
                'price' => trim((string)($_POST['price'] ?? '')), 'unit' => trim((string)($_POST['unit'] ?? '')),
                'options' => trim((string)($_POST['options'] ?? '')), 'badge' => trim((string)($_POST['badge'] ?? '')),
                'sort' => (int)($_POST['sort'] ?? 0), 'visible' => isset($_POST['visible']) ? 1 : 0, 'featured' => isset($_POST['featured']) ? 1 : 0,
                'updated_at' => date('Y-m-d H:i'),
            ];
            if ($id) {
                $set = implode(',', array_map(function ($k) { return "`$k`=?"; }, array_keys($f)));
                $db->prepare("UPDATE products SET $set WHERE id=?")->execute([...array_values($f), $id]);
            } else {
                $f['created_at'] = date('Y-m-d H:i');
                $cl = '`' . implode('`,`', array_keys($f)) . '`';
                $db->prepare("INSERT INTO products ($cl) VALUES (" . implode(',', array_fill(0, count($f), '?')) . ')')->execute(array_values($f));
                $id = (int)$db->lastInsertId();
            }
            foreach (CMS_LANGS as $lc => $_) {
                $t = (array)($tr[$lc] ?? []);
                $v = [trim((string)($t['title'] ?? '')), trim((string)($t['short'] ?? '')), sanitize_html((string)($t['body'] ?? '')),
                      trim((string)($t['specs'] ?? '')), trim((string)($t['meta_title'] ?? '')), trim((string)($t['meta_desc'] ?? ''))];
                $db->prepare('DELETE FROM product_i18n WHERE product_id=? AND lang=?')->execute([$id, $lc]);
                $db->prepare('INSERT INTO product_i18n (product_id, lang, title, short, body, specs, meta_title, meta_desc) VALUES (?,?,?,?,?,?,?,?)')->execute([$id, $lc, ...$v]);
            }
            $db->prepare('DELETE FROM product_category WHERE product_id=?')->execute([$id]);
            foreach (array_unique(array_map('intval', (array)($_POST['cats'] ?? []))) as $cid) {
                if ($cid > 0) $db->prepare('INSERT INTO product_category (product_id, category_id) VALUES (?,?)')->execute([$id, $cid]);
            }
            flash('Товар сохранён.');
            redirect($back . '&edit=' . $id);             // saxlayandan sonra səhifədə qalırıq
        }
        if ($act === 'del') {
            $id = (int)$_POST['id'];
            foreach (['product_i18n', 'product_category'] as $tb) $db->prepare("DELETE FROM $tb WHERE product_id=?")->execute([$id]);
            $db->prepare('DELETE FROM products WHERE id=?')->execute([$id]);
            flash('Товар удалён.'); redirect($back);
        }
        if ($act === 'toggle') {
            $col = ($_POST['col'] ?? '') === 'featured' ? 'featured' : 'visible';
            $db->prepare("UPDATE products SET `$col` = 1 - `$col` WHERE id=?")->execute([(int)$_POST['id']]);
            redirect($back . (isset($_POST['qs']) ? '&' . preg_replace('/[^a-z0-9=&_%+-]/i', '', (string)$_POST['qs']) : ''));
        }
    }

    /* ---------- KATEQORİYALAR ---------- */
    if ($section === 'categories') {
        $back = 'index.php?section=categories';
        if ($act === 'save') {
            $id = (int)($_POST['id'] ?? 0);
            $tr = (array)($_POST['tr'] ?? []);
            $slug = slugify((string)($_POST['slug'] ?? '')) ?: slugify((string)($tr['az']['name'] ?? ''));
            if ($slug === '') { flash('Укажите название или slug.', 'err'); redirect($back); }
            $st = $db->prepare('SELECT id FROM categories WHERE slug = ? AND id <> ?'); $st->execute([$slug, $id]);
            if ($st->fetch()) { flash('Такой slug уже занят.', 'err'); redirect($back . '&edit=' . $id); }
            $image = safe_img_path((string)($_POST['image'] ?? ''));
            if ($up = admin_upload('image_file', 'uploads')) $image = $up;
            $f = [$slug, $image, (int)($_POST['sort'] ?? 0), isset($_POST['visible']) ? 1 : 0];
            if ($id) $db->prepare('UPDATE categories SET slug=?, image=?, sort=?, visible=? WHERE id=?')->execute([...$f, $id]);
            else { $db->prepare('INSERT INTO categories (slug, image, sort, visible, parent_id, wp_id) VALUES (?,?,?,?,0,0)')->execute($f); $id = (int)$db->lastInsertId(); }
            foreach (CMS_LANGS as $lc => $_) {
                $t = (array)($tr[$lc] ?? []);
                $db->prepare('DELETE FROM category_i18n WHERE category_id=? AND lang=?')->execute([$id, $lc]);
                $db->prepare('INSERT INTO category_i18n (category_id, lang, name, descr, meta_title, meta_desc) VALUES (?,?,?,?,?,?)')
                   ->execute([$id, $lc, trim((string)($t['name'] ?? '')), trim((string)($t['descr'] ?? '')), trim((string)($t['meta_title'] ?? '')), trim((string)($t['meta_desc'] ?? ''))]);
            }
            flash('Категория сохранена.'); redirect($back . '&edit=' . $id);
        }
        if ($act === 'del') {
            $id = (int)$_POST['id'];
            foreach (['category_i18n' => 'category_id', 'product_category' => 'category_id'] as $tb => $col) $db->prepare("DELETE FROM $tb WHERE $col=?")->execute([$id]);
            $db->prepare('DELETE FROM categories WHERE id=?')->execute([$id]);
            flash('Категория удалена (товары остались).'); redirect($back);
        }
    }

    /* ---------- SƏHİFƏLƏR ---------- */
    if ($section === 'pages') {
        $back = 'index.php?section=pages';
        if ($act === 'save') {
            $id = (int)($_POST['id'] ?? 0);
            $tr = (array)($_POST['tr'] ?? []);
            $slug = slugify((string)($_POST['slug'] ?? '')) ?: slugify((string)($tr['az']['title'] ?? ''));
            $reserved = ['shop', 'product', 'product-category', 'elaqe', 'api', 'admin', 'assets', 'ru', 'az', 'sitemap-xml'];
            if ($slug === '' || in_array($slug, $reserved, true)) { flash('Укажите другой адрес (slug).', 'err'); redirect($back . ($id ? '&edit=' . $id : '&new=1')); }
            $st = $db->prepare('SELECT id FROM pages WHERE slug = ? AND id <> ?'); $st->execute([$slug, $id]);
            if ($st->fetch()) { flash('Такой адрес уже занят.', 'err'); redirect($back . ($id ? '&edit=' . $id : '&new=1')); }
            $image = safe_img_path((string)($_POST['image'] ?? ''));
            if ($up = admin_upload('image_file', 'uploads')) $image = $up;
            $f = [$slug, $image, (int)($_POST['sort'] ?? 0), isset($_POST['visible']) ? 1 : 0, isset($_POST['in_menu']) ? 1 : 0, date('Y-m-d H:i')];
            if ($id) $db->prepare('UPDATE pages SET slug=?, image=?, sort=?, visible=?, in_menu=?, updated_at=? WHERE id=?')->execute([...$f, $id]);
            else { $db->prepare('INSERT INTO pages (slug, image, sort, visible, in_menu, updated_at, wp_id) VALUES (?,?,?,?,?,?,0)')->execute($f); $id = (int)$db->lastInsertId(); }
            foreach (CMS_LANGS as $lc => $_) {
                $t = (array)($tr[$lc] ?? []);
                $db->prepare('DELETE FROM page_i18n WHERE page_id=? AND lang=?')->execute([$id, $lc]);
                $db->prepare('INSERT INTO page_i18n (page_id, lang, title, `lead`, body, meta_title, meta_desc) VALUES (?,?,?,?,?,?,?)')
                   ->execute([$id, $lc, trim((string)($t['title'] ?? '')), trim((string)($t['lead'] ?? '')), sanitize_html((string)($t['body'] ?? '')),
                              trim((string)($t['meta_title'] ?? '')), trim((string)($t['meta_desc'] ?? ''))]);
            }
            flash('Страница сохранена.'); redirect($back . '&edit=' . $id);
        }
        if ($act === 'del') {
            $db->prepare('DELETE FROM page_i18n WHERE page_id=?')->execute([(int)$_POST['id']]);
            $db->prepare('DELETE FROM pages WHERE id=?')->execute([(int)$_POST['id']]);
            flash('Страница удалена.'); redirect($back);
        }
    }

    /* ---------- MƏTNLƏR ---------- */
    if ($section === 'texts' && $act === 'save') {
        foreach ((array)($_POST['t'] ?? []) as $k => $v) {
            if (preg_match('/^[a-z0-9_]{1,60}$/', (string)$k)) txt_set((string)$k, $L, (string)$v);
        }
        flash('Тексты сохранены (' . strtoupper($L) . ').'); redirect('index.php?section=texts&lang=' . $L);
    }

    /* ---------- ƏLAQƏ ---------- */
    if ($section === 'contacts' && $act === 'save') {
        foreach (['phone', 'whatsapp', 'email', 'map_query', 'instagram', 'facebook'] as $k) kv_set($k, trim((string)($_POST[$k] ?? '')));
        foreach (CMS_LANGS as $lc => $_) {
            txt_set('address', $lc, trim((string)($_POST['address'][$lc] ?? '')));
            txt_set('work_hours', $lc, trim((string)($_POST['work_hours'][$lc] ?? '')));
        }
        flash('Контакты сохранены.'); redirect('index.php?section=contacts');
    }

    /* ---------- SEO ---------- */
    if ($section === 'seo' && $act === 'pack') {
        require_once __DIR__ . '/content-pack.php';
        $n = cms_apply_pack($db);
        flash(sprintf('Пакет применён: товаров %d, категорий %d, страниц %d. SEO обновлено, пустые RU-тексты заполнены.', $n['products'], $n['categories'], $n['pages']));
        redirect('index.php?section=seo');
    }
    if ($section === 'seo') {
        if ($act === 'save') {
            foreach (SEO_PAGES as $k => $_) {
                txt_set("seo_{$k}_title", $L, trim((string)($_POST['title'][$k] ?? '')));
                txt_set("seo_{$k}_desc", $L, trim((string)($_POST['desc'][$k] ?? '')));
            }
            flash('SEO сохранено (' . strtoupper($L) . ').');
        } elseif ($act === 'save_ga') {
            $ga = strtoupper(trim((string)($_POST['ga_id'] ?? '')));
            if ($ga !== '' && !preg_match('/^G-[A-Z0-9]{4,20}$/', $ga)) flash('Формат: G-XXXXXXXXXX', 'err');
            else { kv_set('ga_id', $ga); flash('Google Analytics сохранён.'); }
        }
        redirect('index.php?section=seo&lang=' . $L);
    }

    /* ---------- SORĞULAR ---------- */
    if ($section === 'submissions') {
        if ($act === 'read')        $db->prepare('UPDATE submissions SET is_read=1 WHERE id=?')->execute([(int)$_POST['id']]);
        elseif ($act === 'del')     { $db->prepare('DELETE FROM submissions WHERE id=?')->execute([(int)$_POST['id']]); flash('Заявка удалена.'); }
        elseif ($act === 'allread') { $db->exec('UPDATE submissions SET is_read=1'); flash('Все отмечены прочитанными.'); }
        redirect('index.php?section=submissions');
    }

    /* ---------- ŞƏKİLLƏR ---------- */
    if ($section === 'images' && $act === 'upload') {
        $n = count(admin_upload_many('files', 'uploads'));
        flash($n ? "Загружено файлов: $n" : 'Не удалось загрузить (jpg / png / webp / gif, до 15 МБ).', $n ? 'ok' : 'err');
        redirect('index.php?section=images');
    }

    /* ---------- SMTP ---------- */
    if ($section === 'smtp') {
        if ($act === 'save') {
            foreach (['smtp_host', 'smtp_port', 'smtp_user', 'smtp_from', 'smtp_from_name', 'smtp_secure', 'mail_to'] as $k) kv_set($k, trim((string)($_POST[$k] ?? '')));
            if (($_POST['smtp_pass'] ?? '') !== '') kv_set('smtp_pass', (string)$_POST['smtp_pass']);
            flash('Настройки почты сохранены.');
        } elseif ($act === 'test') {
            $err = ''; $to = trim((string)($_POST['test_to'] ?? ''));
            if (send_mail($to, 'Тест почты — globalprocurement.az', "Это тестовое письмо из админ-панели.\nЕсли вы его получили — отправка заявок работает.", '', $err)) flash('Тестовое письмо отправлено на ' . $to);
            else flash('Ошибка отправки: ' . $err, 'err');
        }
        redirect('index.php?section=smtp');
    }

    /* ---------- TƏHLÜKƏSİZLİK ---------- */
    if ($section === 'security' && $act === 'save') {
        kv_set('turnstile_site', trim((string)($_POST['turnstile_site'] ?? '')));
        kv_set('turnstile_forms', isset($_POST['turnstile_forms']) ? '1' : '0');
        if (($_POST['turnstile_secret'] ?? '') !== '') kv_set('turnstile_secret', trim((string)$_POST['turnstile_secret']));
        if (isset($_POST['clear'])) { kv_set('turnstile_site', ''); kv_set('turnstile_secret', ''); }
        flash('Сохранено.'); redirect('index.php?section=security');
    }

    /* ---------- PROFİL ---------- */
    if ($section === 'profile') {
        $a = current_admin();
        $st = $db->prepare('SELECT * FROM admins WHERE id=?'); $st->execute([$a['id']]); $u = $st->fetch();
        if (!password_verify((string)($_POST['cur_pass'] ?? ''), (string)$u['pass_hash'])) { flash('Текущий пароль неверный.', 'err'); redirect('index.php?section=profile'); }
        $name = trim((string)($_POST['name'] ?? '')); $email = trim((string)($_POST['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { flash('Неверный e-mail.', 'err'); redirect('index.php?section=profile'); }
        try { $db->prepare('UPDATE admins SET name=?, email=? WHERE id=?')->execute([$name, $email, $a['id']]); }
        catch (Throwable $e) { flash('Этот e-mail уже занят другим пользователем.', 'err'); redirect('index.php?section=profile'); }
        $np = (string)($_POST['new_pass'] ?? '');
        if ($np !== '') {
            if (mb_strlen($np) < 10) { flash('Новый пароль — минимум 10 символов.', 'err'); redirect('index.php?section=profile'); }
            $db->prepare('UPDATE admins SET pass_hash=? WHERE id=?')->execute([password_hash($np, PASSWORD_DEFAULT), $a['id']]);
        }
        $_SESSION['admin']['name'] = $name; $_SESSION['admin']['email'] = $email;
        flash('Профиль сохранён.'); redirect('index.php?section=profile');
    }

    /* ---------- İSTİFADƏÇİLƏR ---------- */
    if ($section === 'users') {
        $me = (int)current_admin()['id'];
        if ($act === 'add') {
            $email = trim((string)($_POST['email'] ?? '')); $pass = (string)($_POST['pass'] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($pass) < 10) { flash('E-mail и пароль (мин. 10 символов) обязательны.', 'err'); redirect('index.php?section=users'); }
            try {
                $db->prepare('INSERT INTO admins (name, email, pass_hash, role, active) VALUES (?,?,?,?,1)')
                   ->execute([trim((string)($_POST['name'] ?? '')), $email, password_hash($pass, PASSWORD_DEFAULT), ($_POST['role'] ?? '') === 'admin' ? 'admin' : 'editor']);
                flash('Пользователь добавлен.');
            } catch (Throwable $e) { flash('Такой e-mail уже есть.', 'err'); }
        } elseif ($act === 'setpass') {
            $np = (string)($_POST['new_pass'] ?? '');
            if (mb_strlen($np) < 10) { flash('Пароль — минимум 10 символов.', 'err'); redirect('index.php?section=users'); }
            $st = $db->prepare('SELECT email FROM admins WHERE id=?'); $st->execute([(int)$_POST['id']]);
            if ($u = $st->fetch()) {
                $db->prepare('UPDATE admins SET pass_hash=?, active=1 WHERE id=?')->execute([password_hash($np, PASSWORD_DEFAULT), (int)$_POST['id']]);
                ip_attempts_table();
                $db->exec('DELETE FROM login_attempts');   // köhnə uğursuz cəhdlər yeni parolla girişə mane olmasın
                flash('Новый пароль задан для ' . $u['email'] . '. Учётная запись включена, блокировки входа сняты.');
            }
        } elseif ($act === 'unblock') {
            ip_attempts_table();
            $db->exec('DELETE FROM login_attempts');
            flash('Блокировки входа сняты.');
        } elseif ($act === 'toggle' && (int)$_POST['id'] !== $me) {
            $db->prepare('UPDATE admins SET active = 1 - active WHERE id=?')->execute([(int)$_POST['id']]);
        } elseif ($act === 'del' && (int)$_POST['id'] !== $me) {
            $db->prepare('DELETE FROM admins WHERE id=?')->execute([(int)$_POST['id']]); flash('Пользователь удалён.');
        }
        redirect('index.php?section=users');
    }
}

/* ================== GET (görünüş) ================== */
$count = function (string $sql) use ($db): int { try { return (int)$db->query($sql)->fetch()['c']; } catch (Throwable $e) { return 0; } };
$catList = function () use ($db): array {
    return $db->query("SELECT c.*, COALESCE(NULLIF(a.name,''), c.slug) name,
        (SELECT COUNT(*) FROM product_category pc WHERE pc.category_id = c.id) cnt
        FROM categories c LEFT JOIN category_i18n a ON a.category_id = c.id AND a.lang = 'az' ORDER BY c.sort, c.id")->fetchAll();
};
$langTabs = function (string $prefix): string {
    $h = '<div class="ltabs">';
    foreach (CMS_LANGS as $lc => $lb) $h .= '<button type="button" class="btn sm ' . ($lc === 'az' ? '' : 'ghost') . '" data-ltab="' . $prefix . $lc . '">' . $lb . '</button>';
    return $h . '</div>';
};
$siteUrl = function (string $path): string { return base_url() . '/' . ltrim($path, '/'); };

layout_top($section, SECTIONS[$section][0]);

/* ---------------- OVERVIEW ---------------- */
if ($section === 'overview') {
    $unread = unread_count();
    echo '<div class="cards">';
    foreach ([['SELECT COUNT(*) c FROM products', 'товаров'], ['SELECT COUNT(*) c FROM products WHERE visible=1', 'на сайте'], ['SELECT COUNT(*) c FROM categories', 'категорий'], ['SELECT COUNT(*) c FROM submissions', 'заявок всего']] as [$q, $l])
        echo '<div class="card"><div class="n">' . $count($q) . '</div><div class="l">' . $l . '</div></div>';
    echo '</div>';
    if ($unread) echo '<div class="panel" style="border-color:#8bc34a;background:#f7fbf1"><h3>🔔 Новых заявок: ' . $unread . '</h3><a class="btn" href="index.php?section=submissions">Посмотреть</a></div>';

    $rows = $db->query('SELECT * FROM submissions ORDER BY id DESC LIMIT 5')->fetchAll();
    echo '<div class="panel"><h3>Последние заявки</h3><div class="tablewrap"><table><tr><th>Дата</th><th>Имя</th><th>Телефон</th><th>Товары</th></tr>';
    if (!$rows) echo '<tr><td colspan="4" class="muted">Заявок пока нет.</td></tr>';
    foreach ($rows as $r) {
        $items = (array)json_decode((string)$r['items'], true);
        echo '<tr><td class="muted">' . e($r['created_at']) . '</td><td>' . e($r['name']) . '</td><td>' . e($r['phone']) . '</td><td>' . ($items ? count($items) . ' поз.' : '<span class="muted">общий запрос</span>') . '</td></tr>';
    }
    echo '</table></div></div>';

    $noImg = $count("SELECT COUNT(*) c FROM products WHERE image = ''");
    $noBody = $count("SELECT COUNT(*) c FROM product_i18n WHERE lang='az' AND (body IS NULL OR body = '')");
    echo '<div class="panel"><h3>Состояние каталога</h3><table>';
    echo '<tr><td>Товары без фото</td><td><b>' . $noImg . '</b></td></tr><tr><td>Товары без описания (AZ)</td><td><b>' . $noBody . '</b></td></tr>';
    echo '<tr><td>Отправка почты</td><td>' . (kv_get('smtp_host') !== '' ? '<span class="pill g">SMTP</span>' : '<span class="pill gr">функция mail()</span>') . ' → ' . e(kv_get('mail_to')) . '</td></tr>';
    echo '<tr><td>WhatsApp для заявок</td><td><b>+' . e(preg_replace('/\D/', '', kv_get('whatsapp'))) . '</b></td></tr>';
    echo '<tr><td>База данных</td><td>' . ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite' ? 'SQLite' : 'MySQL') . ' · PHP ' . e(PHP_VERSION) . '</td></tr></table>';
    echo '</div>';
}

/* ---------------- PRODUCTS ---------------- */
elseif ($section === 'products') {
    $mode = isset($_GET['edit']) ? 'edit' : (isset($_GET['new']) ? 'new' : 'list');
    if ($mode === 'list') {
        $q = trim((string)($_GET['q'] ?? '')); $fc = (int)($_GET['cat'] ?? 0);
        $w = ['1=1']; $args = [];
        if ($q !== '') { $w[] = '(LOWER(a.title) LIKE ? OR p.slug LIKE ? OR LOWER(p.brand) LIKE ?)'; $ql = '%' . mb_strtolower($q) . '%'; array_push($args, $ql, $ql, $ql); }
        if ($fc) { $w[] = 'p.id IN (SELECT product_id FROM product_category WHERE category_id = ?)'; $args[] = $fc; }
        $st = $db->prepare("SELECT p.*, a.title, (SELECT b.title FROM product_i18n b WHERE b.product_id = p.id AND b.lang = 'ru') title_ru,
              (SELECT LENGTH(b.body) FROM product_i18n b WHERE b.product_id = p.id AND b.lang = 'az') body_len,
              a.meta_title mt_az, a.meta_desc md_az,
              (SELECT b.meta_title FROM product_i18n b WHERE b.product_id = p.id AND b.lang = 'ru') mt_ru,
              (SELECT b.meta_desc FROM product_i18n b WHERE b.product_id = p.id AND b.lang = 'ru') md_ru
            FROM products p LEFT JOIN product_i18n a ON a.product_id = p.id AND a.lang = 'az' WHERE " . implode(' AND ', $w) . ' ORDER BY p.sort, p.id');
        $st->execute($args); $rows = $st->fetchAll();
        $pc = [];
        foreach ($db->query("SELECT pc.product_id, COALESCE(a.name, c.slug) n FROM product_category pc JOIN categories c ON c.id = pc.category_id LEFT JOIN category_i18n a ON a.category_id = c.id AND a.lang='az'") as $r) $pc[$r['product_id']][] = $r['n'];
        $qs = http_build_query(array_filter(['q' => $q, 'cat' => $fc ?: '']));

        echo '<div class="panel"><form class="filters" method="get"><input type="hidden" name="section" value="products">';
        echo '<input type="text" name="q" value="' . e($q) . '" placeholder="Поиск по названию, бренду, slug">';
        echo '<select name="cat"><option value="0">Все категории</option>';
        foreach ($catList() as $c) echo '<option value="' . $c['id'] . '"' . ($fc === (int)$c['id'] ? ' selected' : '') . '>' . e($c['name']) . ' (' . $c['cnt'] . ')</option>';
        echo '</select><button class="btn ghost">Фильтр</button><span style="flex:1"></span><a class="btn" href="index.php?section=products&new=1">+ Добавить товар</a></form>';
        echo '<div class="muted" style="margin-bottom:8px">Найдено: ' . count($rows) . '. ★ — показывается на главной.</div>';
        echo '<div class="tablewrap"><table><tr><th></th><th>Название</th><th>Бренд</th><th>Категории</th><th>Описание</th><th title="SEO title + description: AZ, RU">SEO</th><th>Сайт</th><th></th></tr>';
        foreach ($rows as $r) {
            echo '<tr><td><img class="thumb" src="' . e($siteUrl('assets/img/' . ($r['image'] ?: 'placeholder.svg'))) . '" alt=""></td>';
            echo '<td><b>' . e($r['title'] ?: $r['slug']) . '</b><br><span class="muted">' . e($r['title_ru'] ?: '— RU нет') . '</span></td>';
            echo '<td>' . e($r['brand']) . '</td><td class="muted">' . e(implode(', ', $pc[$r['id']] ?? [])) . '</td>';
            echo '<td>' . ((int)$r['body_len'] > 0 ? '<span class="pill g">есть</span>' : '<span class="pill gr">нет</span>') . '</td>';
            echo '<td style="white-space:nowrap">' . seo_dot((string)$r['mt_az'], (string)$r['md_az'], 'AZ') . seo_dot((string)$r['mt_ru'], (string)$r['md_ru'], 'RU') . '</td>';
            echo '<td style="white-space:nowrap">';
            foreach (['visible' => ['👁', 'Показ на сайте'], 'featured' => ['★', 'На главной']] as $col => [$ic, $tl]) {
                echo '<form method="post" style="display:inline">' . csrf_field() . '<input type="hidden" name="action" value="toggle"><input type="hidden" name="col" value="' . $col . '"><input type="hidden" name="id" value="' . $r['id'] . '"><input type="hidden" name="qs" value="' . e($qs) . '">'
                   . '<button class="btn sm ' . ((int)$r[$col] ? '' : 'ghost') . '" title="' . $tl . '">' . $ic . '</button></form> ';
            }
            echo '</td><td class="right" style="white-space:nowrap"><a class="btn sm ghost" href="index.php?section=products&edit=' . $r['id'] . '">Изменить</a> ';
            echo '<a class="btn sm ghost" href="' . e($siteUrl('product/' . $r['slug'] . '/')) . '" target="_blank">↗</a> ';
            echo '<form method="post" style="display:inline" data-confirm="Удалить товар?">' . csrf_field() . '<input type="hidden" name="action" value="del"><input type="hidden" name="id" value="' . $r['id'] . '"><button class="btn sm red">✕</button></form></td></tr>';
        }
        if (!$rows) echo '<tr><td colspan="7" class="muted">Ничего не найдено.</td></tr>';
        echo '</table></div></div>';
    } else {
        $p = ['id' => 0, 'slug' => '', 'brand' => '', 'image' => '', 'gallery' => '[]', 'price' => '', 'unit' => '', 'options' => '', 'badge' => '', 'sort' => 0, 'visible' => 1, 'featured' => 0];
        $tr = []; $sel = [];
        if ($mode === 'edit') {
            $st = $db->prepare('SELECT * FROM products WHERE id=?'); $st->execute([(int)$_GET['edit']]);
            $p = $st->fetch() ?: $p;
            $st = $db->prepare('SELECT * FROM product_i18n WHERE product_id=?'); $st->execute([$p['id']]);
            foreach ($st as $r) $tr[$r['lang']] = $r;
            $st = $db->prepare('SELECT category_id FROM product_category WHERE product_id=?'); $st->execute([$p['id']]);
            $sel = array_map('intval', array_column($st->fetchAll(), 'category_id'));
        }
        $g = function ($lc, $k) use ($tr) { return e($tr[$lc][$k] ?? ''); };
        echo '<form method="post" enctype="multipart/form-data">' . csrf_field() . '<input type="hidden" name="action" value="save"><input type="hidden" name="id" value="' . (int)$p['id'] . '">';
        echo '<div class="panel"><h3>' . ($mode === 'edit' ? 'Изменить товар' : 'Новый товар') . '</h3>';
        if ($mode === 'edit') echo '<p class="muted">Адрес на сайте: <a href="' . e($siteUrl('product/' . $p['slug'] . '/')) . '" target="_blank">/product/' . e($p['slug']) . '/</a></p>';
        echo $langTabs('p');
        foreach (CMS_LANGS as $lc => $lb) {
            echo '<div class="lpane' . ($lc === 'az' ? ' on' : '') . '" data-lpane="p' . $lc . '">';
            echo '<label>Название (' . $lb . ')' . ($lc === 'az' ? ' *' : '') . '</label><input type="text" name="tr[' . $lc . '][title]" value="' . $g($lc, 'title') . '">';
            echo '<label>Короткое описание (для карточки)</label><textarea name="tr[' . $lc . '][short]" style="min-height:70px">' . $g($lc, 'short') . '</textarea>';
            echo '<label>Полное описание</label><textarea class="code" data-editor="1" name="tr[' . $lc . '][body]">' . $g($lc, 'body') . '</textarea>';
            echo '<p class="muted">Выделите текст и нажмите кнопку на панели. «HTML» — правка кода. При вставке из Word и сайтов лишнее форматирование убирается; скрипты и стили удаляются при сохранении.</p>';
            echo '<label>Характеристики</label><textarea name="tr[' . $lc . '][specs]" placeholder="Толщина: 9 мм">' . $g($lc, 'specs') . '</textarea><p class="muted">Каждая строка: <code>Название: Значение</code></p>';
            echo '<label>SEO Title <span class="muted" data-len-for="mt' . $lc . '"></span></label><input type="text" id="mt' . $lc . '" data-len="30-65" name="tr[' . $lc . '][meta_title]" value="' . $g($lc, 'meta_title') . '">';
            echo '<label>Meta Description <span class="muted" data-len-for="md' . $lc . '"></span></label><textarea id="md' . $lc . '" data-len="70-165" name="tr[' . $lc . '][meta_desc]" style="min-height:70px">' . $g($lc, 'meta_desc') . '</textarea></div>';
        }
        echo '</div>';

        echo '<div class="panel"><h3>Фото</h3><div class="row"><div>';
        echo '<label>Главное фото</label>' . ($p['image'] ? '<div class="gal">' . img_tile($siteUrl('assets/img/' . $p['image']), '', 'image', 'big') . '</div>' : '');
        echo '<input type="text" name="image" value="' . e($p['image']) . '" placeholder="путь внутри assets/img"><label>Загрузить новое</label><input type="file" name="image_file" accept="image/*"></div><div>';
        echo '<label>Галерея</label><div class="gal">';
        foreach (array_filter((array)json_decode((string)$p['gallery'], true)) as $gi) echo img_tile($siteUrl('assets/img/' . $gi), '<input type="checkbox" name="gal_rm[]" value="' . e($gi) . '" hidden>');
        echo '</div><label>Добавить фото в галерею</label><input type="file" name="gallery_files[]" accept="image/*" multiple></div></div></div>';

        echo '<div class="panel"><h3>Категории и параметры</h3><div class="chips">';
        foreach ($catList() as $c) echo '<label><input type="checkbox" name="cats[]" value="' . $c['id'] . '"' . (in_array((int)$c['id'], $sel, true) ? ' checked' : '') . '> ' . e($c['name']) . '</label>';
        echo '</div><div class="row3"><div><label>Бренд</label><input type="text" name="brand" value="' . e($p['brand']) . '" list="brands"></div>';
        echo '<datalist id="brands">'; foreach ($db->query("SELECT DISTINCT brand FROM products WHERE brand <> ''") as $b) echo '<option value="' . e($b['brand']) . '">'; echo '</datalist>';
        echo '<div><label>Цена (пусто — «по запросу»)</label><input type="text" name="price" value="' . e($p['price']) . '" placeholder="12.50 ₼"></div>';
        echo '<div><label>Единица</label><input type="text" name="unit" value="' . e($p['unit']) . '" placeholder="лист, м², рулон"></div></div>';
        echo '<div class="row3"><div><label>Метка на фото</label><input type="text" name="badge" value="' . e($p['badge']) . '" placeholder="Yeni, Bestseller"></div>';
        echo '<div><label>Slug (адрес)</label><input type="text" name="slug" value="' . e($p['slug']) . '" placeholder="из названия AZ"></div>';
        echo '<div><label>Порядок</label><input type="number" name="sort" value="' . (int)$p['sort'] . '"></div></div>';
        echo '<label>Варианты для выбора клиентом</label><textarea name="options" placeholder="9 mm&#10;12 mm&#10;15 mm" style="min-height:70px">' . e($p['options']) . '</textarea><p class="muted">По одному в строке. Пусто — без выбора.</p>';
        echo '<label style="font-weight:500"><input type="checkbox" name="visible"' . ((int)$p['visible'] ? ' checked' : '') . '> Показывать на сайте</label>';
        echo '<label style="font-weight:500"><input type="checkbox" name="featured"' . ((int)$p['featured'] ? ' checked' : '') . '> ★ Показывать на главной</label></div>';
        echo '<div style="display:flex;gap:10px;flex-wrap:wrap"><button class="btn">Сохранить</button><a class="btn ghost" href="index.php?section=products">Назад к списку</a></div></form>';
    }
}

/* ---------------- CATEGORIES ---------------- */
elseif ($section === 'categories') {
    if (isset($_GET['edit']) || isset($_GET['new'])) {
        $c = ['id' => 0, 'slug' => '', 'image' => '', 'sort' => 0, 'visible' => 1]; $tr = [];
        if (isset($_GET['edit'])) {
            $st = $db->prepare('SELECT * FROM categories WHERE id=?'); $st->execute([(int)$_GET['edit']]); $c = $st->fetch() ?: $c;
            $st = $db->prepare('SELECT * FROM category_i18n WHERE category_id=?'); $st->execute([$c['id']]); foreach ($st as $r) $tr[$r['lang']] = $r;
        }
        echo '<form method="post" enctype="multipart/form-data" class="panel">' . csrf_field() . '<input type="hidden" name="action" value="save"><input type="hidden" name="id" value="' . (int)$c['id'] . '">';
        echo '<h3>' . ($c['id'] ? 'Изменить категорию' : 'Новая категория') . '</h3>' . $langTabs('c');
        foreach (CMS_LANGS as $lc => $lb) {
            echo '<div class="lpane' . ($lc === 'az' ? ' on' : '') . '" data-lpane="c' . $lc . '">';
            echo '<label>Название (' . $lb . ')</label><input type="text" name="tr[' . $lc . '][name]" value="' . e($tr[$lc]['name'] ?? '') . '">';
            echo '<label>Описание (под заголовком страницы)</label><textarea name="tr[' . $lc . '][descr]">' . e($tr[$lc]['descr'] ?? '') . '</textarea>';
            echo '<div class="row"><div><label>SEO Title</label><input type="text" name="tr[' . $lc . '][meta_title]" value="' . e($tr[$lc]['meta_title'] ?? '') . '"></div>';
            echo '<div><label>Meta Description</label><input type="text" name="tr[' . $lc . '][meta_desc]" value="' . e($tr[$lc]['meta_desc'] ?? '') . '"></div></div></div>';
        }
        echo '<div class="row3"><div><label>Slug (адрес)</label><input type="text" name="slug" value="' . e($c['slug']) . '"></div><div><label>Порядок</label><input type="number" name="sort" value="' . (int)$c['sort'] . '"></div>';
        echo '<div><label>Картинка</label><input type="text" name="image" value="' . e($c['image']) . '"></div></div>';
        if ($c['image']) echo '<div class="gal">' . img_tile($siteUrl('assets/img/' . $c['image']), '', 'image') . '</div>';
        echo '<label>Загрузить картинку</label><input type="file" name="image_file" accept="image/*">';
        echo '<label style="font-weight:500"><input type="checkbox" name="visible"' . ((int)$c['visible'] ? ' checked' : '') . '> Показывать на сайте</label>';
        echo '<div style="margin-top:16px"><button class="btn">Сохранить</button> <a class="btn ghost" href="index.php?section=categories">Назад к списку</a></div></form>';
    } else {
        echo '<div class="panel"><div style="display:flex;justify-content:space-between;margin-bottom:12px"><span class="muted">Адрес категории: /product-category/slug/</span><a class="btn" href="index.php?section=categories&new=1">+ Категория</a></div>';
        echo '<div class="tablewrap"><table><tr><th></th><th>Название</th><th>Slug</th><th>Товаров</th><th>Порядок</th><th></th></tr>';
        foreach ($catList() as $c) {
            echo '<tr><td><img class="thumb" src="' . e($siteUrl('assets/img/' . ($c['image'] ?: 'placeholder.svg'))) . '" alt=""></td><td><b>' . e($c['name']) . '</b>' . ((int)$c['visible'] ? '' : ' <span class="muted">(скрыта)</span>') . '</td>';
            echo '<td class="muted">' . e($c['slug']) . '</td><td>' . (int)$c['cnt'] . '</td><td>' . (int)$c['sort'] . '</td>';
            echo '<td class="right" style="white-space:nowrap"><a class="btn sm ghost" href="index.php?section=categories&edit=' . $c['id'] . '">Изменить</a> <a class="btn sm ghost" href="' . e($siteUrl('product-category/' . $c['slug'] . '/')) . '" target="_blank">↗</a> ';
            echo '<form method="post" style="display:inline" data-confirm="Удалить категорию? Товары останутся.">' . csrf_field() . '<input type="hidden" name="action" value="del"><input type="hidden" name="id" value="' . $c['id'] . '"><button class="btn sm red">✕</button></form></td></tr>';
        }
        echo '</table></div></div>';
    }
}

/* ---------------- PAGES ---------------- */
elseif ($section === 'pages') {
    if (isset($_GET['edit']) || isset($_GET['new'])) {
        $pg = ['id' => 0, 'slug' => '', 'image' => '', 'sort' => 0, 'visible' => 1, 'in_menu' => 1]; $tr = [];
        if (isset($_GET['edit'])) {
            $st = $db->prepare('SELECT * FROM pages WHERE id=?'); $st->execute([(int)$_GET['edit']]); $pg = $st->fetch() ?: $pg;
            $st = $db->prepare('SELECT * FROM page_i18n WHERE page_id=?'); $st->execute([$pg['id']]); foreach ($st as $r) $tr[$r['lang']] = $r;
        }
        echo '<form method="post" enctype="multipart/form-data">' . csrf_field() . '<input type="hidden" name="action" value="save"><input type="hidden" name="id" value="' . (int)$pg['id'] . '">';
        echo '<div class="panel"><h3>' . ($pg['id'] ? 'Изменить страницу' : 'Новая страница') . '</h3>';
        if ($pg['id']) echo '<p class="muted">Адрес: <a href="' . e($siteUrl($pg['slug'] . '/')) . '" target="_blank">/' . e($pg['slug']) . '/</a></p>';
        echo $langTabs('g');
        foreach (CMS_LANGS as $lc => $lb) {
            $v = function ($k) use ($tr, $lc) { return e($tr[$lc][$k] ?? ''); };
            echo '<div class="lpane' . ($lc === 'az' ? ' on' : '') . '" data-lpane="g' . $lc . '">';
            echo '<label>Заголовок (' . $lb . ')</label><input type="text" name="tr[' . $lc . '][title]" value="' . $v('title') . '">';
            echo '<label>Подзаголовок (под заголовком)</label><textarea name="tr[' . $lc . '][lead]" style="min-height:60px">' . $v('lead') . '</textarea>';
            echo '<label>Содержание</label><textarea class="code" data-editor="1" name="tr[' . $lc . '][body]" style="min-height:380px">' . $v('body') . '</textarea>';
            echo '<p class="muted">Выделите текст и нажмите кнопку на панели. «HTML» — правка кода. При вставке из Word и сайтов лишнее форматирование убирается; скрипты и стили удаляются при сохранении. Пустое содержание на RU — показывается AZ.</p>';
            echo '<div class="row"><div><label>SEO Title</label><input type="text" name="tr[' . $lc . '][meta_title]" value="' . $v('meta_title') . '"></div>';
            echo '<div><label>Meta Description</label><input type="text" name="tr[' . $lc . '][meta_desc]" value="' . $v('meta_desc') . '"></div></div></div>';
        }
        echo '</div><div class="panel"><div class="row3"><div><label>Адрес (slug)</label><input type="text" name="slug" value="' . e($pg['slug']) . '"></div>';
        echo '<div><label>Порядок в меню</label><input type="number" name="sort" value="' . (int)$pg['sort'] . '"></div><div><label>Картинка вверху</label><input type="text" name="image" value="' . e($pg['image']) . '"></div></div>';
        if ($pg['image']) echo '<div class="gal">' . img_tile($siteUrl('assets/img/' . $pg['image']), '', 'image') . '</div>';
        echo '<label>Загрузить картинку</label><input type="file" name="image_file" accept="image/*">';
        echo '<label style="font-weight:500"><input type="checkbox" name="visible"' . ((int)$pg['visible'] ? ' checked' : '') . '> Опубликована</label>';
        echo '<label style="font-weight:500"><input type="checkbox" name="in_menu"' . ((int)$pg['in_menu'] ? ' checked' : '') . '> Показывать в подвале сайта</label></div>';
        echo '<div style="display:flex;gap:10px;flex-wrap:wrap"><button class="btn">Сохранить</button><a class="btn ghost" href="index.php?section=pages">Назад</a></div></form>';
    } else {
        $rows = $db->query("SELECT p.*, a.title, (SELECT LENGTH(b.body) FROM page_i18n b WHERE b.page_id = p.id AND b.lang = 'ru') ru_len
            FROM pages p LEFT JOIN page_i18n a ON a.page_id = p.id AND a.lang = 'az' ORDER BY p.sort, p.id")->fetchAll();
        echo '<div class="panel"><div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:12px"><span class="muted">Главная, каталог и контакты — отдельные разделы («Тексты сайта», «Товары», «Контакты»).</span><a class="btn" href="index.php?section=pages&new=1">+ Страница</a></div>';
        echo '<div class="tablewrap"><table><tr><th>Заголовок</th><th>Адрес</th><th>RU</th><th>Статус</th><th></th></tr>';
        if (!$rows) echo '<tr><td colspan="5" class="muted">Страниц пока нет — нажмите «+ Страница».</td></tr>';
        foreach ($rows as $r) {
            echo '<tr><td><b>' . e($r['title'] ?: $r['slug']) . '</b></td><td class="muted">/' . e($r['slug']) . '/</td>';
            echo '<td>' . ((int)$r['ru_len'] > 0 ? '<span class="pill g">есть</span>' : '<span class="pill gr">нет</span>') . '</td>';
            echo '<td>' . ((int)$r['visible'] ? '<span class="pill">опубликована</span>' : '<span class="pill gr">скрыта</span>') . '</td>';
            echo '<td class="right" style="white-space:nowrap"><a class="btn sm ghost" href="index.php?section=pages&edit=' . $r['id'] . '">Изменить</a> <a class="btn sm ghost" href="' . e($siteUrl($r['slug'] . '/')) . '" target="_blank">↗</a> ';
            echo '<form method="post" style="display:inline" data-confirm="Удалить страницу?">' . csrf_field() . '<input type="hidden" name="action" value="del"><input type="hidden" name="id" value="' . $r['id'] . '"><button class="btn sm red">✕</button></form></td></tr>';
        }
        echo '</table></div></div>';
    }
}

/* ---------------- SUBMISSIONS ---------------- */
elseif ($section === 'submissions') {
    $rows = $db->query('SELECT * FROM submissions ORDER BY id DESC LIMIT 500')->fetchAll();
    echo '<div class="panel"><div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;gap:12px;flex-wrap:wrap">';
    echo '<div class="muted">Всего: ' . count($rows) . ', новых: ' . unread_count() . '. Заявки приходят на почту <b>' . e(kv_get('mail_to')) . '</b> и открываются у клиента в WhatsApp.</div>';
    if (unread_count()) echo '<form method="post">' . csrf_field() . '<input type="hidden" name="action" value="allread"><button class="btn ghost sm">Отметить все прочитанными</button></form>';
    echo '</div>';
    if (!$rows) echo '<p class="muted">Заявок пока нет.</p>';
    foreach ($rows as $r) {
        $items = (array)json_decode((string)$r['items'], true);
        $phone = preg_replace('/\D/', '', (string)$r['phone']);
        if (strlen($phone) === 9 || (strlen($phone) === 10 && $phone[0] === '0')) $phone = '994' . ltrim($phone, '0');
        echo '<div class="item"' . ((int)$r['is_read'] ? '' : ' style="background:#f3faea;border-color:#cfe7b4"') . '>';
        echo '<div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap"><div><b>#' . $r['id'] . ' · ' . e($r['name']) . '</b> <span class="muted">' . e($r['created_at']) . ' · ' . strtoupper(e($r['lang'])) . '</span>'
           . ((int)$r['mailed'] ? ' <span class="pill g">письмо отправлено</span>' : ' <span class="pill gr">письмо не ушло</span>') . '</div>';
        echo '<div style="white-space:nowrap">';
        if ($phone !== '') echo '<a class="btn sm green" href="https://wa.me/' . e($phone) . '" target="_blank" rel="noopener">WhatsApp</a> ';
        if ((string)$r['phone'] !== '') echo '<a class="btn sm ghost" href="tel:' . e($r['phone']) . '">☎ ' . e($r['phone']) . '</a> ';
        if (!(int)$r['is_read']) echo '<form method="post" style="display:inline">' . csrf_field() . '<input type="hidden" name="action" value="read"><input type="hidden" name="id" value="' . $r['id'] . '"><button class="btn sm ghost">Прочитано</button></form> ';
        echo '<form method="post" style="display:inline" data-confirm="Удалить заявку?">' . csrf_field() . '<input type="hidden" name="action" value="del"><input type="hidden" name="id" value="' . $r['id'] . '"><button class="btn sm red">✕</button></form></div></div>';
        if ($r['email']) echo '<div class="muted" style="margin-top:6px">✉ <a href="mailto:' . e($r['email']) . '">' . e($r['email']) . '</a></div>';
        if ($r['company']) echo '<div class="muted">Тема: ' . e($r['company']) . '</div>';
        if ($items) {
            echo '<table style="margin-top:10px;background:#fff;border-radius:8px"><tr><th>Товар</th><th>Вариант</th><th>Кол-во</th></tr>';
            foreach ($items as $it) echo '<tr><td><a href="' . e($siteUrl('product/' . ($it['slug'] ?? '') . '/')) . '" target="_blank">' . e($it['title'] ?? '') . '</a></td><td>' . e($it['opt'] ?? '') . '</td><td><b>' . (int)($it['qty'] ?? 1) . '</b></td></tr>';
            echo '</table>';
        }
        if (trim((string)$r['message']) !== '') echo '<p style="margin:10px 0 0">' . nl2br(e($r['message'])) . '</p>';
        if ($r['page']) echo '<div class="muted" style="margin-top:6px">Страница: ' . e($r['page']) . '</div>';
        echo '</div>';
    }
    echo '</div>';
}

/* ---------------- TEXTS ---------------- */
elseif ($section === 'texts') {
    echo '<form method="post">' . csrf_field() . '<input type="hidden" name="action" value="save">';
    echo '<p class="muted">Язык: <b>' . strtoupper($L) . '</b> — переключается вверху справа. Пустое поле на RU — сайт покажет AZ-текст.</p>';
    foreach (TEXT_FIELDS as $group => $fields) {
        echo '<div class="panel"><h3>' . e($group) . '</h3>';
        foreach ($fields as $k => [$label, $type, $hint]) {
            $v = txt_get($k, $L);
            echo '<label>' . e($label) . '</label>';
            if ($type === 'input') echo '<input type="text" name="t[' . $k . ']" value="' . e($v) . '">';
            else echo '<textarea name="t[' . $k . ']" style="min-height:' . ($k === 'about_page' ? 320 : (substr_count($v, "\n") > 2 ? 150 : 90)) . 'px">' . e($v) . '</textarea>';
            if ($hint) echo '<div class="muted" style="margin-top:4px">' . e($hint) . '</div>';
        }
        echo '</div>';
    }
    echo '<button class="btn">Сохранить</button></form>';
}

/* ---------------- CONTACTS ---------------- */
elseif ($section === 'contacts') {
    echo '<form method="post" class="panel">' . csrf_field() . '<input type="hidden" name="action" value="save"><h3>Контакты</h3>';
    echo '<div class="row"><div><label>Телефон (как показывать)</label><input type="text" name="phone" value="' . e(kv_get('phone')) . '"></div>';
    echo '<div><label>WhatsApp — номер для заявок</label><input type="text" name="whatsapp" value="' . e(kv_get('whatsapp')) . '" placeholder="994513040043"><div class="muted">Только цифры с кодом страны. Сюда открываются все заказы с сайта.</div></div></div>';
    echo '<div class="row"><div><label>E-mail на сайте</label><input type="email" name="email" value="' . e(kv_get('email')) . '"></div>';
    echo '<div><label>Карта (адрес для Google Maps)</label><input type="text" name="map_query" value="' . e(kv_get('map_query')) . '"></div></div>';
    echo '<div class="row"><div><label>Instagram</label><input type="url" name="instagram" value="' . e(kv_get('instagram')) . '"></div><div><label>Facebook</label><input type="url" name="facebook" value="' . e(kv_get('facebook')) . '"></div></div>';
    foreach (CMS_LANGS as $lc => $lb) {
        echo '<div class="row"><div><label>Адрес (' . $lb . ')</label><input type="text" name="address[' . $lc . ']" value="' . e(txt_get('address', $lc)) . '"></div>';
        echo '<div><label>Время работы (' . $lb . ')</label><input type="text" name="work_hours[' . $lc . ']" value="' . e(txt_get('work_hours', $lc)) . '"></div></div>';
    }
    echo '<div style="margin-top:18px"><button class="btn">Сохранить</button></div><p class="muted">Адрес почты для заявок — в разделе «Почта (SMTP)».</p></form>';
}

/* ---------------- SEO ---------------- */
elseif ($section === 'seo') {
    echo '<form method="post" class="panel">' . csrf_field() . '<input type="hidden" name="action" value="save"><h3>SEO страниц (' . strtoupper($L) . ')</h3>';
    echo '<p class="muted">Для товаров и категорий SEO задаётся в их карточках. Карта сайта: <a href="' . e(base_url() . '/sitemap.xml') . '" target="_blank">/sitemap.xml</a></p>';
    foreach (SEO_PAGES as $k => $lb) {
        echo '<div class="item"><b>' . e($lb) . '</b><label>Title</label><input type="text" name="title[' . $k . ']" value="' . e(txt_get("seo_{$k}_title", $L)) . '">';
        echo '<label>Meta description</label><textarea name="desc[' . $k . ']" style="min-height:70px">' . e(txt_get("seo_{$k}_desc", $L)) . '</textarea></div>';
    }
    echo '<button class="btn">Сохранить</button></form>';
    echo '<form method="post" class="panel" data-confirm="Записать SEO title/description для всех товаров, категорий и страниц (AZ + RU)? Ваши ручные SEO-правки будут заменены.">' . csrf_field() . '<input type="hidden" name="action" value="pack">';
    echo '<h3>Пакет SEO и русских переводов</h3><p class="muted">SEO title и description на AZ и RU для всех 21 товара, 7 категорий и 3 страниц + русские описания товаров (заполняются только пустые). Применяется автоматически при установке и после импорта.</p><button class="btn ghost">Применить пакет</button></form>';
    echo '<form method="post" class="panel">' . csrf_field() . '<input type="hidden" name="action" value="save_ga"><h3>Google Analytics 4</h3>';
    echo '<label>Идентификатор потока</label><input type="text" name="ga_id" value="' . e(kv_get('ga_id')) . '" placeholder="G-XXXXXXXXXX"><div style="margin-top:12px"><button class="btn">Сохранить</button></div></form>';
}

/* ---------------- IMAGES ---------------- */
elseif ($section === 'images') {
    $root = dirname(__DIR__) . '/assets/img';
    echo '<form method="post" enctype="multipart/form-data" class="panel">' . csrf_field() . '<input type="hidden" name="action" value="upload">';
    echo '<h3>Загрузить изображения</h3><input type="file" name="files[]" accept="image/*" multiple required><div style="margin-top:14px"><button class="btn">Загрузить</button></div>';
    echo '<p class="muted">jpg / png / webp / gif до 15 МБ. Файлы попадают в <code>assets/img/uploads/</code>. Кликните по пути, чтобы скопировать.</p></form>';
    $files = [];
    if (is_dir($root)) {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) {
            if ($f->isFile() && in_array(strtolower($f->getExtension()), ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true)) $files[] = ['p' => $f->getPathname(), 'm' => $f->getMTime()];
        }
    }
    usort($files, function ($a, $b) { return $b['m'] <=> $a['m']; });
    echo '<div class="panel"><h3>Медиа (' . count($files) . ')</h3><div class="grid-media">';
    foreach ($files as $m) {
        $rel = ltrim(str_replace('\\', '/', substr($m['p'], strlen($root))), '/');
        echo '<div style="border:1px solid #eef2f7;border-radius:10px;padding:8px;text-align:center"><img src="' . e(base_url() . '/assets/img/' . $rel) . '" loading="lazy" style="width:100%;height:90px;object-fit:contain;background:#f3f6fb;border-radius:6px">';
        echo '<input type="text" readonly value="' . e($rel) . '" data-copy="1" style="width:100%;font-size:10.5px;margin-top:6px;padding:4px;cursor:pointer"></div>';
    }
    echo '</div></div>';
}

/* ---------------- SMTP ---------------- */
elseif ($section === 'smtp') {
    $g = function ($k) { return e(kv_get($k)); };
    echo '<form method="post" class="panel">' . csrf_field() . '<input type="hidden" name="action" value="save">';
    echo '<h3>Отправка заявок на почту <span class="muted" style="font-weight:400;font-size:13px">— сейчас: <b>' . (kv_get('smtp_host') !== '' ? 'SMTP' : 'функция mail()') . '</b></span></h3>';
    echo '<label>Куда приходят заявки</label><input type="email" name="mail_to" value="' . $g('mail_to') . '">';
    echo '<div class="row3"><div><label>SMTP-сервер</label><input type="text" name="smtp_host" value="' . $g('smtp_host') . '" placeholder="mail.globalprocurement.az"></div>';
    echo '<div><label>Порт</label><input type="text" name="smtp_port" value="' . $g('smtp_port') . '" placeholder="465"></div><div><label>Шифрование</label><select name="smtp_secure">';
    foreach (['ssl' => 'SSL/TLS (465)', 'tls' => 'STARTTLS (587)', 'none' => 'Без шифрования'] as $v => $l) echo '<option value="' . $v . '"' . (kv_get('smtp_secure') === $v ? ' selected' : '') . '>' . $l . '</option>';
    echo '</select></div></div><div class="row"><div><label>Пользователь (полный адрес почты)</label><input type="text" name="smtp_user" value="' . $g('smtp_user') . '"></div>';
    echo '<div><label>Пароль</label><input type="password" name="smtp_pass" placeholder="' . (kv_get('smtp_pass') !== '' ? 'сохранён — оставьте пустым' : '') . '"></div></div>';
    echo '<div class="row"><div><label>Адрес отправителя</label><input type="text" name="smtp_from" value="' . $g('smtp_from') . '"></div><div><label>Имя отправителя</label><input type="text" name="smtp_from_name" value="' . $g('smtp_from_name') . '" placeholder="Global Procurement"></div></div>';
    echo '<div style="margin-top:18px"><button class="btn">Сохранить</button></div></form>';
    echo '<form method="post" class="panel">' . csrf_field() . '<input type="hidden" name="action" value="test"><h3>Проверка</h3><div style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap"><div style="flex:1;min-width:220px"><label>Адрес получателя</label><input type="email" name="test_to" value="' . e(kv_get('mail_to')) . '" required></div><button class="btn ghost">Отправить тест</button></div></form>';
    echo '<div class="panel"><h3>Где взять данные</h3><p class="muted">cPanel → <b>Email Accounts</b> → у ящика нажмите <b>Connect Devices</b>: там сервер, порт и шифрование. Пользователь — полный адрес почты.</p></div>';
}

/* ---------------- SECURITY ---------------- */
elseif ($section === 'security') {
    echo '<form method="post" class="panel">' . csrf_field() . '<input type="hidden" name="action" value="save"><h3>Cloudflare Turnstile — защита от ботов</h3>';
    echo '<p class="muted">Ключи: dash.cloudflare.com → Turnstile → Add site (домен globalprocurement.az). Пусто — капча выключена. Работает на входе в админку и, если отмечено, во всех формах сайта.</p>';
    echo '<label>Site key</label><input type="text" name="turnstile_site" value="' . e(kv_get('turnstile_site')) . '">';
    echo '<label>Secret key</label><input type="password" name="turnstile_secret" placeholder="' . (kv_get('turnstile_secret') !== '' ? 'сохранён — оставьте пустым' : '') . '">';
    echo '<label style="font-weight:500"><input type="checkbox" name="turnstile_forms"' . (kv_get('turnstile_forms', '1') !== '0' ? ' checked' : '') . '> Капча во всех формах сайта (заявки, контакты, список запроса)</label>';
    echo '<div style="margin-top:16px;display:flex;gap:10px"><button class="btn">Сохранить</button><button class="btn ghost" name="clear" value="1" data-confirm="Выключить капчу?">Выключить</button></div></form>';
    echo '<div class="panel"><h3>Уже включено</h3><ul class="muted" style="line-height:1.9;margin:0;padding-left:18px">'
       . '<li>Формы: скрытая ловушка для ботов, подписанная метка времени (бот не может отправить мгновенно или повторно старую форму), лимит 6 заявок в час с браузера и 15 с IP</li>'
       . '<li>Реальный IP посетителя за Cloudflare (CF-Connecting-IP принимается только от серверов Cloudflare)</li>'
       . '<li>Вход: CSRF-токен, лимит 6 попыток на сессию и 10 попыток с IP за 15 минут, сессия 2 ч бездействия / 12 ч максимум</li>'
       . '<li>Заголовки: Content-Security-Policy, X-Frame-Options, nosniff, Referrer-Policy, HSTS (по HTTPS)</li>'
       . '<li>Загрузки: только изображения (проверка содержимого), случайные имена, выполнение скриптов в assets/ запрещено</li>'
       . '<li>HTML в описаниях очищается от скриптов, стилей, iframe и опасных ссылок</li>'
       . '<li>install.php закрыт после установки; config.php, база и логи недоступны из браузера; ошибки PHP не показываются посетителям</li>'
       . '</ul></div>';
}

/* ---------------- USERS ---------------- */
elseif ($section === 'users') {
    $me = (int)current_admin()['id'];
    echo '<div class="panel"><p class="muted" style="margin-top:0">Логин для входа — это e-mail пользователя из таблицы. Если пользователь не может войти, задайте ему новый пароль кнопкой «Задать пароль».</p>';
    echo '<div class="tablewrap"><table><tr><th>Имя</th><th>E-mail (логин)</th><th>Роль</th><th>Последний вход</th><th></th></tr>';
    foreach ($db->query('SELECT * FROM admins ORDER BY id') as $u) {
        echo '<tr><td>' . e($u['name']) . '</td><td>' . e($u['email']) . '</td><td>' . ($u['role'] === 'admin' ? 'Администратор' : 'Редактор') . ((int)$u['active'] ? '' : ' <span class="pill gr">выкл.</span>') . '</td><td class="muted">' . e($u['last_login'] ?? '—') . '</td><td class="right">';
        if ((int)$u['id'] !== $me) {
            echo '<form method="post" style="display:inline-flex;gap:6px;align-items:center;margin:0 6px 6px 0" autocomplete="off">' . csrf_field() . '<input type="hidden" name="action" value="setpass"><input type="hidden" name="id" value="' . $u['id'] . '">'
               . '<input type="password" name="new_pass" minlength="10" required placeholder="новый пароль" autocomplete="new-password" style="width:170px;padding:6px 10px"><button class="btn sm">Задать пароль</button></form> ';
            echo '<form method="post" style="display:inline">' . csrf_field() . '<input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="' . $u['id'] . '"><button class="btn sm ghost">' . ((int)$u['active'] ? 'Отключить' : 'Включить') . '</button></form> ';
            echo '<form method="post" style="display:inline" data-confirm="Удалить пользователя?">' . csrf_field() . '<input type="hidden" name="action" value="del"><input type="hidden" name="id" value="' . $u['id'] . '"><button class="btn sm red">✕</button></form>';
        } else echo '<span class="muted">это вы</span>';
        echo '</td></tr>';
    }
    echo '</table></div></div>';
    ip_attempts_table();
    $blocked = 0;
    try { $st = $db->prepare('SELECT COUNT(DISTINCT ip) c FROM login_attempts WHERE at > ?'); $st->execute([time() - LOGIN_WINDOW]); $blocked = (int)$st->fetch()['c']; } catch (Throwable $e) {}
    echo '<form method="post" class="panel">' . csrf_field() . '<input type="hidden" name="action" value="unblock"><h3>Блокировки входа</h3>'
       . '<p class="muted">После 10 неверных паролей с одного IP вход закрывается на 15 минут. Сейчас IP с неудачными попытками: <b>' . $blocked . '</b>.</p>'
       . '<button class="btn ghost"' . ($blocked ? '' : ' disabled') . '>Снять блокировки</button></form>';
    echo '<form method="post" class="panel" autocomplete="off">' . csrf_field() . '<input type="hidden" name="action" value="add"><h3>Добавить пользователя</h3>';
    echo '<div class="row"><div><label>Имя</label><input type="text" name="name" autocomplete="off"></div><div><label>E-mail (будет логином)</label><input type="email" name="email" required autocomplete="off" spellcheck="false"></div></div>';
    echo '<div class="row"><div><label>Пароль (мин. 10 символов)</label><input type="password" name="pass" required minlength="10" autocomplete="new-password"></div><div><label>Роль</label><select name="role"><option value="editor">Редактор — товары, тексты, заявки</option><option value="admin">Администратор — всё</option></select></div></div>';
    echo '<div style="margin-top:16px"><button class="btn">Добавить</button></div></form>';
}

/* ---------------- PROFILE ---------------- */
elseif ($section === 'profile') {
    $a = current_admin();
    echo '<form method="post" class="panel">' . csrf_field() . '<h3>Мой профиль</h3>';
    echo '<p class="muted" style="margin-top:0">Здесь меняются данные только вашей учётной записи — <b>' . e($a['email']) . '</b>.'
       . (is_admin() ? ' Пароль другого пользователя задаётся в разделе <a href="index.php?section=users">«Пользователи»</a> → «Задать пароль».' : '') . '</p>';
    echo '<div class="row"><div><label>Имя</label><input type="text" name="name" value="' . e($a['name']) . '"></div><div><label>E-mail (логин)</label><input type="email" name="email" value="' . e($a['email']) . '" required autocomplete="username"></div></div>';
    echo '<div class="row"><div><label>Новый пароль (пусто — не менять)</label><input type="password" name="new_pass" minlength="10" autocomplete="new-password"></div><div><label>Текущий пароль *</label><input type="password" name="cur_pass" required autocomplete="current-password"></div></div>';
    echo '<div style="margin-top:16px"><button class="btn">Сохранить</button></div></form>';
}

layout_bottom();
