<?php
/*
 * Birdəfəlik quraşdırma: cədvəllər + ilkin məzmun + ilk admin hesabı.
 *   /admin/install.php?token=<admin/config.php → install_token>
 * Təkrar işə salmaq təhlükəsizdir (mövcud məlumatlara toxunmur).
 * Bitəndən sonra faylı serverdən silin.
 */
declare(strict_types=1);
require __DIR__ . '/lib.php';
require __DIR__ . '/seed.php';
require __DIR__ . '/content-pack.php';

header('Content-Type: text/html; charset=utf-8');
$token = (string)cfg('install_token', '');
if ($token === '' || $token === 'CHANGE_ME_TOKEN' || !hash_equals($token, (string)($_GET['token'] ?? ''))) {
    http_response_code(403);
    exit('Forbidden: admin/config.php → install_token (CHANGE_ME_TOKEN olmamalıdır)');
}

$db = pdo();
/* Quraşdırılıbsa — yalnız daxil olmuş administrator təkrar işə sala bilər */
try {
    $has = (int)$db->query('SELECT COUNT(*) c FROM admins')->fetch()['c'] > 0;
} catch (Throwable $e) { $has = false; }
if ($has && !is_admin()) {
    http_response_code(403);
    exit('Sayt artıq quraşdırılıb. Təkrar işə salmaq üçün əvvəlcə panelə administrator kimi daxil olun.');
}
$tables = cms_create_tables($db);
$seed = cms_seed($db);
$pack = cms_apply_pack($db);

$email = (string)cfg('install_admin_email', 'admin@globalprocurement.az');
$pass  = (string)cfg('install_admin_pass', '');
$st = $db->query('SELECT COUNT(*) c FROM admins');
$made = false;
if ((int)$st->fetch()['c'] === 0) {
    if ($pass === '' || $pass === 'CHANGE_ME_2026') exit('admin/config.php → install_admin_pass dəyişdirilməlidir.');
    $db->prepare('INSERT INTO admins (name, email, pass_hash, role, active) VALUES (?,?,?,?,1)')
       ->execute(['Administrator', $email, password_hash($pass, PASSWORD_DEFAULT), 'admin']);
    $made = true;
}

echo '<meta charset="utf-8"><style>body{font-family:system-ui,sans-serif;max-width:640px;margin:60px auto;padding:0 20px;line-height:1.6}code{background:#eef2f7;padding:2px 8px;border-radius:6px}</style>';
echo '<h2>✓ Quraşdırma tamamlandı</h2>';
echo '<p>Cədvəllər: ' . e(implode(', ', $tables)) . '</p>';
echo '<p>Əlavə olundu — parametrlər: ' . $seed['settings'] . ', mətnlər: ' . $seed['texts'] . ', kateqoriyalar: ' . $seed['categories'] . ', məhsullar: ' . $seed['products'] . '</p>';
echo '<p>SEO və RU tərcümələr: məhsul ' . $pack['products'] . ', kateqoriya ' . $pack['categories'] . ', səhifə ' . $pack['pages'] . '</p>';
echo $made ? '<p>Admin hesabı yaradıldı: <code>' . e($email) . '</code></p>' : '<p>Admin hesabı artıq var.</p>';
echo '<p><a href="index.php">→ Panelə daxil olun</a>, sonra <b>Импорт из WordPress</b> bölməsindən məhsulları köhnə saytdan gətirin.</p>';
echo '<p style="color:#b91c1c"><b>Vacib:</b> <code>admin/install.php</code> faylını serverdən silin.</p>';
