<?php
/*
 * POST /api/request — sorğunun qəbulu.
 *   1) yoxlama (bot tələsi, mənşə, limit),
 *   2) bazaya yazılır (admin → «Заявки»),
 *   3) e-poçtla göndərilir (SMTP və ya mail()),
 *   4) cavabda WhatsApp ünvanı qaytarılır — brauzer onu açır.
 * {"kind":"wa"} — müştəri saytdakı WhatsApp düyməsinə basıb: əlaqə məlumatı yoxdur, amma keçid
 * də «Заявки»-yə yazılır (source = whatsapp, e-poçt göndərilmir).
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

/* Məhsullar: yalnız id-yə etibar edirik, adları bazadan götürürük */
$resolveItems = function ($raw) use ($clean): array {
    // Baza əlçatmazdırsa sorğu itməsin: məhsul id/miqdar/variant ilə qalır (ad "#id"), e-poçt və requests.log-a düşür
    $items = []; $pdo = null; $st = null;
    try { $pdo = db(); } catch (Throwable $e) {}
    foreach (array_slice(is_array($raw) ? $raw : [], 0, 50) as $it) {
        $id = is_array($it) ? (int)($it['id'] ?? 0) : 0;
        if ($id <= 0) continue;
        $row = ['id' => $id, 'slug' => '', 'title' => '#' . $id,
                'qty' => max(1, min(100000, (int)($it['qty'] ?? 1))), 'opt' => $clean($it['opt'] ?? '', 60)];
        if ($pdo) {
            try {
                $st = $st ?: $pdo->prepare('SELECT p.slug, COALESCE(NULLIF(i.title, \'\'), a.title, p.slug) title FROM products p
                    LEFT JOIN product_i18n i ON i.product_id = p.id AND i.lang = ? LEFT JOIN product_i18n a ON a.product_id = p.id AND a.lang = ?
                    WHERE p.id = ? LIMIT 1');
                $st->execute([lang(), DEFAULT_LANG, $id]);
                $p = $st->fetch();
                $st->closeCursor();
                if (!$p) continue;                                  // belə məhsul yoxdur
                $row['slug'] = $p['slug']; $row['title'] = $p['title'];
            } catch (Throwable $e) {
                error_log('api/request items: ' . $e->getMessage());
            }
        }
        $items[] = $row;
    }
    return $items;
};

/* WhatsApp-a keçid — yalnız qeyd (müştəri yazışmanı WhatsApp-da davam etdirir).
   İmzalı möhür: səhifə bu saytda son 24 saatda açılıb — kənar skriptlərə maneə.
   Eyni IP + səhifə + məhsullar 10 dəq. ərzində bir dəfə yazılır; bir IP-dən saatda ən çox 20 qeyd. */
if (($in['kind'] ?? '') === 'wa') {
    if (!form_stamp_ok((string)($in['stamp'] ?? ''), 0)) out(400, ['ok' => false, 'error' => 'stamp']);
    $ip    = client_ip();
    $page  = $clean($in['page'] ?? '', 255);
    $text  = $clean($in['text'] ?? '', 1000, true);
    $items = $resolveItems($in['items'] ?? []);
    $json  = json_encode($items, JSON_UNESCAPED_UNICODE);
    try {
        $st = db()->prepare("SELECT COUNT(*) c FROM submissions WHERE source = 'whatsapp' AND ip = ? AND created_at >= ?");
        $st->execute([$ip, date('Y-m-d H:i', time() - 3600)]);
        if ((int)$st->fetch()['c'] >= 20) out(200, ['ok' => true]);
        $st = db()->prepare("SELECT id FROM submissions WHERE source = 'whatsapp' AND ip = ? AND page = ? AND items = ? AND created_at >= ? LIMIT 1");
        $st->execute([$ip, $page, $json, date('Y-m-d H:i', time() - 600)]);
        if ($st->fetch()) out(200, ['ok' => true]);
        db()->prepare('INSERT INTO submissions (created_at, name, phone, email, company, message, items, source, lang, page, ip, mailed, is_read)
                       VALUES (?,?,?,?,?,?,?,?,?,?,?,0,0)')
            ->execute([date('Y-m-d H:i'), '', '', '', '', $text, $json, 'whatsapp', lang(), $page, $ip]);
    } catch (Throwable $e) {
        error_log('api/request wa: ' . $e->getMessage());
    }
    out(200, ['ok' => true]);
}

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
try { $st = db()->prepare('SELECT COUNT(*) c FROM submissions WHERE ip = ? AND created_at >= ? AND source <> \'whatsapp\''); $st->execute([$ip, date('Y-m-d H:i', $now - 3600)]); $byIp = (int)$st->fetch()['c']; } catch (Throwable $e) {}
if (count($hits) >= 6 || $byIp >= 15) out(429, ['ok' => false, 'error' => t('msg.toomany')]);
$hits[] = $now; $_SESSION['req_hits'] = $hits;

$items = $resolveItems($in['items'] ?? []);

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
} catch (Throwable $e) {
    error_log('api/request insert: ' . $e->getMessage());
}

$mailErr = '';
$subject = t('mail.subject') . ': ' . $d['name'] . ($items ? ' (' . count($items) . ')' : '');
$sent = send_mail(setting('mail_to') ?: setting('email'), $subject, $body, $d['email'], $mailErr);
if ($id) { try { db()->prepare('UPDATE submissions SET mailed = ? WHERE id = ?')->execute([$sent ? 1 : 0, $id]); } catch (Throwable $e) {} }
/* Bazaya yazıla bilməyibsə sorğu itməsin: fayla yazılır, admin → «Заявки» onu göstərir */
if (!$id) {
    @file_put_contents(ROOT_DIR . '/storage/requests.log', $body . "\n" . str_repeat('=', 60) . "\n", FILE_APPEND | LOCK_EX);
}

/* WhatsApp mesajı */
$wa = t('wa.hello') . "\n"
    . ($lines ? implode("\n", $lines) . "\n" : '')
    . ($d['topic'] !== '' ? t('mail.topic') . ': ' . $d['topic'] . "\n" : '')
    . ($d['message'] !== '' ? $d['message'] . "\n" : '')
    . "\n" . $d['name'] . ', ' . $d['phone'] . ($id ? "\n#" . $id : '');

out(200, ['ok' => true, 'id' => $id, 'wa' => wa_link($wa)]);
