<?php
/*
 * Ortaq şablon hissələri: <head>, header, footer, məhsul kartı, sorğu siyahısı.
 * $meta = ['title' => .., 'desc' => .., 'image' => .., 'noindex' => bool, 'schema' => array]
 */

const WA_SVG = '<svg width="28" height="28" viewBox="0 0 24 24" fill="#fff" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm5.3 14.1c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .2-3.3-.7-2.8-1.1-4.5-3.9-4.7-4.1-.1-.2-1.1-1.5-1.1-2.8s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.2.1.4 0 .5l-.3.5-.4.4c-.1.1-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.2 1 2.1 1.3 2.4 1.5.3.1.5.1.6-.1l.9-1c.2-.3.4-.2.6-.1l1.9.9c.3.1.5.2.5.3.1.1.1.7-.1 1.3z"/></svg>';
const CART_SVG = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg>';

/** Cari səhifənin başqa dildəki ünvanı */
function lang_url(string $l): string {
    $qs = (string)($_SERVER['QUERY_STRING'] ?? '');
    return url((string)($GLOBALS['ROUTE'] ?? ''), $l) . ($qs !== '' ? '?' . e($qs) : '');
}

function page_head(array $meta): void {
    $title = $meta['title'] ?? text('site_name', 'Global Procurement');
    $desc  = $meta['desc'] ?? '';
    $canon = site_origin() . url((string)($GLOBALS['ROUTE'] ?? ''));
    $image = $meta['image'] ?? img('osb.jpg');
    if (!preg_match('~^https?://~', $image)) $image = site_origin() . $image;
    $ga = setting('ga_id');
    ?><!doctype html>
<html lang="<?= e(lang()) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<?php if ($desc !== ''): ?><meta name="description" content="<?= e($desc) ?>">
<?php endif; ?>
<?php if (!empty($meta['noindex'])): ?><meta name="robots" content="noindex, follow">
<?php endif; ?>
<link rel="canonical" href="<?= e($canon) ?>">
<?php foreach (LANGS as $code => $_): ?><link rel="alternate" hreflang="<?= $code ?>" href="<?= e(site_origin() . url((string)($GLOBALS['ROUTE'] ?? ''), $code)) ?>">
<?php endforeach; ?>
<meta property="og:type" content="website">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:url" content="<?= e($canon) ?>">
<meta property="og:image" content="<?= e($image) ?>">
<link rel="icon" href="<?= e(img('favicon.png')) ?>" type="image/png">
<link rel="apple-touch-icon" href="<?= e(img('apple-touch-icon.png')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
<?php if (!empty($meta['schema'])): ?><script type="application/ld+json"><?= json_encode($meta['schema'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
<?php endif; ?>
<?php if ($ga !== '' && preg_match('/^G-[A-Z0-9]+$/', $ga)): ?><script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($ga) ?>"></script>
<script src="<?= e(asset('js/ga.js')) ?>" data-id="<?= e($ga) ?>"></script>
<?php endif; ?>
</head>
<body>
<?php
}

function site_header(string $active = ''): void {
    $cats = categories();
    $nav = [
        'shop' => t('nav.catalog'), '#beledci' => t('nav.guide'),
        'product-category/kronospan-osb-lovh-l-ri' => 'OSB', 'haqqimizda' => t('nav.about'), 'elaqe' => t('nav.contact'),
    ];
    $announce = text('announce');
    ?>
<?php if ($announce !== ''): ?><div class="ann"><b><?= e($announce) ?></b> <a href="<?= e(url('elaqe')) ?>"><?= e(t('btn.send_request')) ?> →</a></div><?php endif; ?>
<header id="hdr">
  <div class="wrap hd">
    <a href="<?= e(url('')) ?>" class="logo" aria-label="Global Procurement Group"><img src="<?= e(img('logo-h.png')) ?>" alt="Global Procurement Group" width="186" height="46"></a>
    <form class="hsearch" action="<?= e(url('shop')) ?>" method="get" role="search">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
      <input id="hs" name="q" placeholder="<?= e(t('search.ph')) ?>" autocomplete="off" aria-label="<?= e(t('search.ph')) ?>">
      <div class="sugg" id="sugg"></div>
    </form>
    <nav class="nav">
      <?php foreach ($nav as $p => $l): ?>
        <a href="<?= e($p[0] === '#' ? url('') . $p : url($p)) ?>"<?= $active === $p ? ' style="color:var(--blue)"' : '' ?>><?= e($l) ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="acts">
      <div class="lang"><?php foreach (LANGS as $code => [$short]): ?><a href="<?= lang_url($code) ?>"<?= $code === lang() ? ' class="on"' : '' ?> hreflang="<?= $code ?>"><?= $short ?></a><?php endforeach; ?></div>
      <a href="<?= e(tel_link()) ?>" class="btn btn-blue" style="padding:12px 18px"><?= e(setting('phone')) ?></a>
      <button class="cart-btn" type="button" data-cart-open aria-label="<?= e(t('cart.title')) ?>"><?= CART_SVG ?><b id="cartN">0</b></button>
      <button class="burger" type="button" aria-label="Menu" data-mob-open><i></i><i></i><i></i></button>
    </div>
  </div>
  <div class="subnav">
    <div class="wrap">
      <?php foreach ($cats as $c): ?>
        <a href="<?= e(url('product-category/' . $c['slug'])) ?>"><img src="<?= e(img($c['image'])) ?>" alt="" loading="lazy"><?= e($c['name']) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</header>

<div class="mob" id="mob">
  <button class="x" type="button" data-mob-close aria-label="Bağla">✕</button>
  <a href="<?= e(url('')) ?>"><?= e(t('nav.home')) ?></a>
  <?php foreach ($nav as $p => $l): ?><a href="<?= e($p[0] === '#' ? url('') . $p : url($p)) ?>" data-mob-close><?= e($l) ?></a><?php endforeach; ?>
  <div class="lang"><?php foreach (LANGS as $code => [$short]): ?><a href="<?= lang_url($code) ?>"<?= $code === lang() ? ' class="on"' : '' ?>><?= $short ?></a><?php endforeach; ?></div>
  <a href="<?= e(tel_link()) ?>" class="btn btn-blue" style="margin-top:20px;border:0;color:#fff"><?= e(setting('phone')) ?></a>
</div>
<?php
}

/** Məhsul kartı (kataloq və ana səhifə) */
function product_card(array $p, int $i = 0): string {
    $u = url('product/' . $p['slug']);
    $data = e(json_encode(['id' => (int)$p['id'], 'slug' => $p['slug'], 't' => $p['title'], 'img' => img($p['image']), 'u' => $u], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    return '<article class="pc" style="animation-delay:' . ($i * 40) . 'ms">'
        . '<a href="' . e($u) . '" class="im">' . ($p['badge'] !== '' ? '<span class="tg">' . e($p['badge']) . '</span>' : '')
        . '<img src="' . e(img($p['image'])) . '" alt="' . e($p['title']) . '" loading="lazy"></a>'
        . '<div class="bd"><span class="br">' . e($p['brand']) . '</span>'
        . '<a href="' . e($u) . '" class="ttl"><h4>' . e($p['title']) . '</h4></a>'
        . '<p class="ds">' . e((string)$p['short']) . '</p>'
        . '<div class="ft"><a href="' . e($u) . '" class="btn btn-white">' . e(t('btn.details')) . '</a>'
        . '<button type="button" class="btn btn-blue" data-add="' . $data . '">' . e(t('btn.add_short')) . '</button></div></div></article>';
}

function site_footer(): void {
    $cats = categories();
    $js = [
        'api'   => url('api/request'),
        'shop'  => url('shop'),
        'wa'    => wa_number(),
        'lang'  => lang(),
        'i18n'  => [
            'added' => t('cart.added'), 'open' => t('cart.open'), 'empty' => t('cart.empty'), 'none' => t('search.none'),
            'opt' => t('label.option'), 'qty' => t('label.qty'), 'hello' => t('wa.hello'), 'err' => t('form.err'),
            'sending' => t('form.sending'), 'pcs' => t('label.pcs'), 'need' => t('msg.needname_phone'),
        ],
        'products' => array_map(function ($p) {
            return ['id' => (int)$p['id'], 's' => $p['slug'], 't' => $p['title'], 'b' => $p['brand'], 'i' => img($p['image']), 'u' => url('product/' . $p['slug'])];
        }, products()),
    ];
    ?>
<footer>
  <div class="wrap">
    <div class="fg">
      <div>
        <a href="<?= e(url('')) ?>" class="logo logo-f"><img src="<?= e(img('logo.png')) ?>" alt="Global Procurement Group" width="134" height="100" loading="lazy"></a>
        <p style="color:var(--muted);margin-top:16px;max-width:320px;font-size:15px"><?= e(text('footer_text')) ?></p>
      </div>
      <div><h5><?= e(t('footer.products')) ?></h5><ul>
        <?php foreach (array_slice($cats, 0, 6) as $c): ?><li><a href="<?= e(url('product-category/' . $c['slug'])) ?>"><?= e($c['name']) ?></a></li><?php endforeach; ?>
      </ul></div>
      <div><h5><?= e(t('footer.company')) ?></h5><ul>
        <li><a href="<?= e(url('haqqimizda')) ?>"><?= e(t('nav.about')) ?></a></li>
        <?php foreach (menu_pages() as $mp): ?><li><a href="<?= e(url($mp['slug'])) ?>"><?= e($mp['title']) ?></a></li><?php endforeach; ?>
        <li><a href="<?= e(url('shop')) ?>"><?= e(t('nav.catalog')) ?></a></li>
        <li><a href="<?= e(url('') . '#beledci') ?>"><?= e(t('nav.guide')) ?></a></li>
        <li><a href="<?= e(url('elaqe')) ?>"><?= e(t('nav.contact')) ?></a></li>
      </ul></div>
      <div><h5><?= e(t('footer.contact')) ?></h5><ul>
        <li><a href="<?= e(tel_link()) ?>"><?= e(setting('phone')) ?></a></li>
        <li><a href="<?= e(wa_link()) ?>" target="_blank" rel="noopener">WhatsApp</a></li>
        <li><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></li>
        <li><?= e(text('address')) ?></li>
      </ul></div>
    </div>
    <div class="fb"><span>© <?= date('Y') ?> Global Procurement Group MMC</span><span><?= e(t('footer.rights')) ?></span></div>
  </div>
</footer>

<div class="fab"><a href="<?= e(wa_link(t('wa.hello'))) ?>" style="background:#25d366" target="_blank" rel="noopener" aria-label="WhatsApp"><?= WA_SVG ?></a></div>

<!-- Sorğu siyahısı -->
<div class="drawer" id="drawer" aria-hidden="true">
  <div class="bg" data-cart-close></div>
  <aside class="panel" role="dialog" aria-label="<?= e(t('cart.title')) ?>">
    <div class="dh"><h3><?= e(t('cart.title')) ?></h3><button class="x" type="button" data-cart-close aria-label="Bağla">✕</button></div>
    <div class="db" id="cartList"></div>
    <div class="df">
      <form class="rform" id="cartForm" novalidate>
        <div class="two"><input name="name" placeholder="<?= e(t('form.name')) ?> *" required maxlength="120"><input name="phone" type="tel" placeholder="<?= e(t('form.phone')) ?> *" required maxlength="60"></div>
        <input name="email" type="email" placeholder="<?= e(t('form.email')) ?>" maxlength="190">
        <textarea name="message" rows="2" placeholder="<?= e(t('form.comment')) ?>" maxlength="3000"></textarea>
        <input class="hp" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
        <div class="fmsg err" id="cartErr" hidden></div>
        <button class="btn btn-blue" type="submit"><?= e(t('cart.send')) ?></button>
        <p class="note"><?= e(t('cart.note')) ?></p>
      </form>
    </div>
  </aside>
</div>

<div class="done" id="done" role="dialog" aria-modal="true">
  <div class="box">
    <div class="ic">✓</div>
    <h3><?= e(t('form.sent_title')) ?></h3>
    <p><?= e(t('form.sent_text')) ?></p>
    <div class="acts">
      <a class="btn btn-wa" id="doneWa" href="<?= e(wa_link()) ?>" target="_blank" rel="noopener"><?= e(t('form.open_wa')) ?></a>
      <button class="btn btn-white" type="button" data-done-close><?= e(t('btn.close')) ?></button>
    </div>
  </div>
</div>
<div class="toast" id="toast"><span></span><button type="button" data-cart-open><?= e(t('cart.open')) ?></button></div>

<script type="application/json" id="gp-data"><?= json_encode($js, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<script src="<?= e(asset('js/site.js')) ?>" defer></script>
</body>
</html>
<?php
}

/** Sorğu forması (əlaqə səhifəsi və ana səhifənin sonu) */
function request_form(string $title = ''): void {
    $cats = categories();
    ?>
<form class="c-form" data-request-form novalidate>
  <?php if ($title !== ''): ?><h4><?= e($title) ?></h4><?php endif; ?>
  <div class="two"><input name="name" placeholder="<?= e(t('form.name')) ?> *" required maxlength="120"><input name="phone" type="tel" placeholder="<?= e(t('form.phone')) ?> *" required maxlength="60"></div>
  <input name="email" type="email" placeholder="<?= e(t('form.email')) ?>" maxlength="190">
  <select name="topic"><option value=""><?= e(t('form.category_ph')) ?></option><?php foreach ($cats as $c): ?><option><?= e($c['name']) ?></option><?php endforeach; ?></select>
  <textarea name="message" rows="4" placeholder="<?= e(t('form.message')) ?>" maxlength="3000"></textarea>
  <input class="hp" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
  <div class="fmsg err" hidden></div>
  <button class="btn btn-blue" type="submit"><?= e(t('form.send')) ?> →</button>
</form>
<?php
}
