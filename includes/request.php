<?php
/*
 * POST /api/request — sorğunun qəbulu.
 *   1) yoxlama (bot tələsi, mənşə, limit),
 *   2) bazaya yazılır (admin → «Заявки»),
 *   3) e-poçtla göndərilir (SMTP və ya mail()),
 *   4) cavabda WhatsApp ünvanı qaytarılır — brauzer onu açır.
 * Onlayn ödəniş yoxdur.
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function out(int $code, array $d): void { http_response_code($code); echo json_encode($d, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); exit; }

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') out(405, ['ok' => false, 'error' => 'POST only']);

/* Başqa saytdan göndərilən sorğular qəbul olunmur */
$host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
foreach (['HTTP_ORIGIN', 'HTTP_REFERER'] as $h) {
    $v = (string)($_SERVER[$h] ?? '');
    if ($v !== '' && strtolower((string)parse_url($v, PHP_URL_HOST) . (parse_url($v, PHP_URL_PORT) ? ':' . parse_url($v, PHP_URL_PORT) : '')) !== $host) {
        out(403, ['ok' => false, 'error' => 'origin']);
    }
}

/* Yalnız JSON, ən çox 32 KB */
if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 32768) out(413, ['ok' => false, 'error' => 'too large']);
if (stripos((string)($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json') !== 0) out(415, ['ok' => false, 'error' => 'json only']);
$raw = file_get_contents('php://input', false, null, 0, 32768);
$in  = json_decode((string)$raw, true);
if (!is_array($in)) out(400, ['ok' => false, 'error' => 'bad json']);
if (!empty($in['lang']) && isset(LANGS[$in['lang']])) set_lang((string)$in['lang']);

$clean = function ($v, int $max, bool $multi = false): string {
    $v = (string)$v;
    $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $v);
    if (!$multi) $v = str_replace(["\r", "\n", "\t"], ' ', $v);
    return mb_substr(trim((string)$v), 0, $max);
};

$d = [
    'name'    => $clean($in['name'] ?? '', 120),
    'phone'   => $clean($in['phone'] ?? '', 60),
    'email'   => $clean($in['email'] ?? '', 190),
    'topic'   => $clean($in['topic'] ?? '', 190),
    'message' => $clean($in['message'] ?? '', 3000, true),
    'page'    => $clean($in['page'] ?? '', 255),
];

/* Bot tələsi — doludursa "uğurlu" deyirik, amma heç nə etmirik */
if (trim((string)($in['website'] ?? '')) !== '') out(200, ['ok' => true, 'wa' => wa_link()]);

/* Vaxt tələsi: forma serverdə verilmiş imzalı möhürlə gəlməli, ən azı 3 san. əvvəl açılmış olmalıdır */
if (!form_stamp_ok((string)($in['stamp'] ?? ''))) out(400, ['ok' => false, 'error' => t('msg.expired')]);

$ip = client_ip();

/* Cloudflare Turnstile (admin → Безопасность-da açılıbsa) */
if (ts_forms_on() && !ts_check((string)($in['cf-turnstile-response'] ?? ''), ts_secret_key(), $ip)) {
    out(400, ['ok' => false, 'error' => t('msg.captcha')]);
}

$err = [];
if ($d['name'] === '') $err[] = t('msg.needname');
if ($d['phone'] === '' || !preg_match('/^[0-9+()\s.\-]{5,60}$/u', $d['phone'])) $err[] = t('msg.badphone');
if ($d['email'] !== '' && !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $err[] = t('msg.bademail');
if ($err) out(422, ['ok' => false, 'error' => implode(' ', $err)]);

/* Limit: bir sessiyadan saatda 6, bir IP-dən saatda 15 sorğu */
if (session_status() === PHP_SESSION_NONE) {
    @session_name('gpreq');
    @session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'path' => '/', 'secure' => is_https_req()]);
    @session_start();
}
$now  = time();
$hits = array_values(array_filter((array)($_SESSION['req_hits'] ?? []), function ($t) use ($now) { return is_int($t) && $t > $now - 3600; }));
$byIp = 0;
try { $st = db()->prepare('SELECT COUNT(*) c FROM submissions WHERE ip = ? AND created_at >= ?'); $st->execute([$ip, date('Y-m-d H:i', $now - 3600)]); $byIp = (int)$st->fetch()['c']; } catch (Throwable $e) {}
if (count($hits) >= 6 || $byIp >= 15) out(429, ['ok' => false, 'error' => t('msg.toomany')]);
$hits[] = $now; $_SESSION['req_hits'] = $hits;

/* Məhsullar: yalnız id-yə etibar edirik, adları bazadan götürürük */
$items = [];
foreach (array_slice((array)($in['items'] ?? []), 0, 50) as $it) {
    $id = (int)($it['id'] ?? 0);
    if ($id <= 0) continue;
    $st = db()->prepare('SELECT p.slug, COALESCE(NULLIF(i.title, \'\'), a.title, p.slug) title FROM products p
        LEFT JOIN product_i18n i ON i.product_id = p.id AND i.lang = ? LEFT JOIN product_i18n a ON a.product_id = p.id AND a.lang = ?
        WHERE p.id = ? LIMIT 1');
    $st->execute([lang(), DEFAULT_LANG, $id]);
    if (!($p = $st->fetch())) continue;
    $items[] = [
        'id' => $id, 'slug' => $p['slug'], 'title' => $p['title'],
        'qty' => max(1, min(100000, (int)($it['qty'] ?? 1))),
        'opt' => $clean($it['opt'] ?? '', 60),
    ];
}

/* Mətn: e-poçt və WhatsApp üçün eyni */
$lines = [];
foreach ($items as $n => $it) {
    $lines[] = ($n + 1) . '. ' . $it['title'] . ($it['opt'] !== '' ? ' (' . $it['opt'] . ')' : '') . ' — ' . $it['qty'] . ' ' . t('label.pcs');
}
$origin = site_origin();
$body = t('mail.new') . "\n" . str_repeat('-', 40) . "\n"
      . t('form.name') . ': ' . $d['name'] . "\n"
      . t('form.phone') . ': ' . $d['phone'] . "\n"
      . ($d['email'] !== '' ? t('form.email') . ': ' . $d['email'] . "\n" : '')
      . ($d['topic'] !== '' ? t('mail.topic') . ': ' . $d['topic'] . "\n" : '')
      . ($lines ? "\n" . t('mail.items') . ":\n" . implode("\n", $lines) . "\n" : '')
      . ($d['message'] !== '' ? "\n" . t('mail.message') . ":\n" . $d['message'] . "\n" : '')
      . str_repeat('-', 40) . "\n"
      . t('mail.date') . ': ' . date('d.m.Y H:i') . "\n"
      . ($d['page'] !== '' ? t('mail.page') . ': ' . $origin . $d['page'] . "\n" : '')
      . 'IP: ' . $ip . "\n";

$id = 0;
try {
    db()->prepare('INSERT INTO submissions (created_at, name, phone, email, company, message, items, source, lang, page, ip, mailed, is_read)
                   VALUES (?,?,?,?,?,?,?,?,?,?,?,0,0)')
        ->execute([date('Y-m-d H:i'), $d['name'], $d['phone'], $d['email'], $d['topic'], $d['message'],
                   json_encode($items, JSON_UNESCAPED_UNICODE), $items ? 'cart' : 'form', lang(), $d['page'], $ip]);
    $id = (int)db()->lastInsertId();
} catch (Throwable $e) {}

$mailErr = '';
$subject = t('mail.subject') . ': ' . $d['name'] . ($items ? ' (' . count($items) . ')' : '');
$sent = send_mail(setting('mail_to') ?: setting('email'), $subject, $body, $d['email'], $mailErr);
if ($id) { try { db()->prepare('UPDATE submissions SET mailed = ? WHERE id = ?')->execute([$sent ? 1 : 0, $id]); } catch (Throwable $e) {} }
if (!$sent && !$id) {
    @file_put_contents(ROOT_DIR . '/storage/requests.log', $body . "\n" . str_repeat('=', 60) . "\n", FILE_APPEND);
}

/* WhatsApp mesajı */
$wa = t('wa.hello') . "\n"
    . ($lines ? implode("\n", $lines) . "\n" : '')
    . ($d['topic'] !== '' ? t('mail.topic') . ': ' . $d['topic'] . "\n" : '')
    . ($d['message'] !== '' ? $d['message'] . "\n" : '')
    . "\n" . $d['name'] . ', ' . $d['phone'] . ($id ? "\n#" . $id : '');

out(200, ['ok' => true, 'id' => $id, 'wa' => wa_link($wa)]);
