<?php
/* Mətn səhifəsi — admin → «Страницы». /haqqimizda/ da bu şablondan istifadə edir (pages/about.php). */
require_once __DIR__ . '/../includes/layout.php';

$pg = $pg ?? page_by_slug((string)($params['slug'] ?? ''));
if (!$pg) { require __DIR__ . '/404.php'; return; }

$body = trim((string)$pg['body']);
$desc = $pg['meta_desc'] ?: mb_substr(plain_text((string)($pg['lead'] ?: $body)), 0, 160);
page_head(['title' => $pg['meta_title'] ?: $pg['title'] . ' — Global Procurement', 'desc' => $desc, 'image' => $pg['image'] ? img($pg['image']) : null]);
site_header($pg['slug']);
?>
<section class="phero">
  <div class="wrap">
    <nav class="crumbs" aria-label="breadcrumb"><a href="<?= e(url('')) ?>"><?= e(t('nav.home')) ?></a><span><?= e($pg['title']) ?></span></nav>
    <h1><?= e($pg['title']) ?></h1>
    <?php if (trim((string)$pg['lead']) !== ''): ?><p><?= e($pg['lead']) ?></p><?php endif; ?>
  </div>
</section>

<section class="page">
  <div class="wrap">
    <div class="about" style="align-items:start">
      <div class="prose">
        <?php if ($pg['image']): ?><img src="<?= e(img($pg['image'])) ?>" alt="<?= e($pg['title']) ?>" style="width:100%;margin-top:0"><?php endif; ?>
        <?= $body !== '' ? $body : simple_md(text('about_page')) ?>
      </div>
      <aside style="position:sticky;top:150px">
        <?php if ($pg['slug'] === 'haqqimizda'): ?>
          <div class="nums" style="margin-bottom:14px">
            <?php foreach (array_slice(lines(text('stats'), 3), 0, 4) as [$num, $suf, $lbl]): ?><div><b><?= (int)$num . e($suf) ?></b><span><?= e($lbl) ?></span></div><?php endforeach; ?>
          </div>
        <?php endif; ?>
        <div class="ccard">
          <h3><?= e(text('cta_title')) ?></h3>
          <p style="color:var(--muted);margin-bottom:16px"><?= e(text('cta_text')) ?></p>
          <div style="display:grid;gap:10px">
            <a class="btn btn-blue" href="<?= e(url('elaqe')) ?>"><?= e(t('btn.send_request')) ?></a>
            <a class="btn btn-wa" href="<?= e(wa_link(t('wa.hello') . "\n" . $pg['title'])) ?>" target="_blank" rel="noopener">WhatsApp</a>
            <a class="btn btn-white" href="<?= e(tel_link()) ?>"><?= e(setting('phone')) ?></a>
          </div>
        </div>
      </aside>
    </div>
    <?php if ($pg['slug'] === 'welder-qaynag-avadanliqlari' && ($rel = products(['cat' => 'qaynaq-apparatlari', 'limit' => 4]))): ?>
      <div class="related"><div class="sh"><div><span class="label">WELDER</span><h2><?= e(t('nav.catalog')) ?></h2></div></div>
        <div class="pgrid g4"><?php foreach ($rel as $i => $r) echo product_card($r, $i); ?></div></div>
    <?php endif; ?>
  </div>
</section>
<?php site_footer();
