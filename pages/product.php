<?php
require_once __DIR__ . '/../includes/layout.php';

$p = product_by_slug((string)($params['slug'] ?? ''));
if (!$p) { require __DIR__ . '/404.php'; return; }

$images  = array_values(array_unique(array_filter(array_merge([$p['image']], $p['gallery_list']))));
if (!$images) $images = [''];
$options = array_values(array_filter(array_map('trim', preg_split('/\R|\|/u', (string)$p['options']))));
$specs   = [];
foreach (preg_split('/\R/u', trim((string)$p['specs'])) as $ln) {
    if (strpos($ln, ':') === false) continue;
    [$k, $v] = array_map('trim', explode(':', $ln, 2));
    if ($k !== '' && $v !== '') $specs[] = [$k, $v];
}
$cats = array_values(array_filter(array_map('category_by_slug', $p['cats'])));
$main = $cats[0] ?? null;

$related = $main ? products(['cat' => $main['slug'], 'exclude' => $p['id'], 'limit' => 4]) : [];
if (count($related) < 4) {
    $have = array_column($related, 'id');
    foreach (products(['featured' => 1, 'exclude' => $p['id'], 'limit' => 8]) as $r) {
        if (count($related) >= 4) break;
        if (!in_array($r['id'], $have, true)) $related[] = $r;
    }
}

$body = trim((string)$p['body']);
$desc = $p['meta_desc'] ?: mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags((string)($p['short'] ?: $body)))), 0, 160);
$canon = site_origin() . url('product/' . $p['slug']);
$schema = [
    '@context' => 'https://schema.org', '@type' => 'Product',
    'name' => $p['title'], 'description' => $desc, 'sku' => $p['slug'],
    'image' => array_map(function ($i) { $u = img($i); return preg_match('~^https?://~', $u) ? $u : site_origin() . $u; }, $images),
    'brand' => ['@type' => 'Brand', 'name' => $p['brand'] ?: 'Global Procurement'], 'url' => $canon,
];
$waMsg = t('wa.hello') . "\n" . $p['title'] . "\n" . $canon;
$data  = ['id' => (int)$p['id'], 'slug' => $p['slug'], 't' => $p['title'], 'img' => img($images[0]), 'u' => url('product/' . $p['slug'])];

page_head(['title' => $p['meta_title'] ?: $p['title'] . ' — Global Procurement', 'desc' => $desc, 'image' => img($images[0]), 'schema' => $schema]);
site_header('shop');
?>
<section class="phero" style="padding:22px 0">
  <div class="wrap">
    <nav class="crumbs" aria-label="breadcrumb"><a href="<?= e(url('')) ?>"><?= e(t('nav.home')) ?></a><span><a href="<?= e(url('shop')) ?>"><?= e(t('nav.catalog')) ?></a></span><?php if ($main): ?><span><a href="<?= e(url('product-category/' . $main['slug'])) ?>"><?= e($main['name']) ?></a></span><?php endif; ?><span><?= e($p['title']) ?></span></nav>
  </div>
</section>

<section class="page">
  <div class="wrap">
    <div class="prod">
      <div class="gal">
        <div class="main"><img id="galMain" src="<?= e(img($images[0])) ?>" alt="<?= e($p['title']) ?>"></div>
        <?php if (count($images) > 1): ?>
          <div class="thumbs"><?php foreach ($images as $i => $im): ?><button type="button" class="<?= $i === 0 ? 'on' : '' ?>" data-img="<?= e(img($im)) ?>" aria-label="<?= $i + 1 ?>"><img src="<?= e(img($im)) ?>" alt="" loading="lazy"></button><?php endforeach; ?></div>
        <?php endif; ?>
      </div>
      <div class="pinfo">
        <?php if ($p['brand'] !== ''): ?><span class="brand"><?= e($p['brand']) ?></span><?php endif; ?>
        <h1><?= e($p['title']) ?></h1>
        <?php if (trim((string)$p['short']) !== ''): ?><p class="lead"><?= e($p['short']) ?></p><?php endif; ?>
        <?php if ($cats): ?><div class="cats"><?php foreach ($cats as $c): ?><a href="<?= e(url('product-category/' . $c['slug'])) ?>"><?= e($c['name']) ?></a><?php endforeach; ?></div><?php endif; ?>

        <div class="order" id="order" data-product="<?= e(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>">
          <div class="price">
            <b><?= $p['price'] !== '' ? e($p['price']) . ($p['unit'] !== '' ? ' <span>/ ' . e($p['unit']) . '</span>' : '') : e(t('product.price_request')) ?></b>
            <span><?= e(t('product.price_note')) ?></span>
          </div>
          <div class="row2<?= $options ? '' : ' one' ?>">
            <?php if ($options): ?>
              <div><label for="opt"><?= e(t('label.option')) ?></label><select id="opt"><?php foreach ($options as $o): ?><option><?= e($o) ?></option><?php endforeach; ?></select></div>
            <?php endif; ?>
            <div><label for="qty"><?= e(t('label.qty')) ?></label><div class="qty"><button type="button" data-q="-1" aria-label="-">−</button><input id="qty" type="number" value="1" min="1" inputmode="numeric"><button type="button" data-q="1" aria-label="+">+</button></div></div>
          </div>
          <div class="acts">
            <button type="button" class="btn btn-blue" id="addBtn"><?= e(t('btn.add')) ?></button>
            <a class="btn btn-wa" id="waBtn" href="<?= e(wa_link($waMsg)) ?>" target="_blank" rel="noopener"><?= e(t('btn.wa')) ?></a>
          </div>
          <p class="note">ⓘ <?= e(t('cart.note')) ?></p>
        </div>
      </div>
    </div>

    <div class="ptabs">
      <div class="tabs" role="tablist">
        <?php $tabs = []; if ($body !== '') $tabs['d'] = t('label.description'); if ($specs) $tabs['s'] = t('label.specs'); $tabs['r'] = t('product.request_tab');
        $first = array_key_first($tabs);
        foreach ($tabs as $k => $l): ?><button type="button" role="tab" data-tab="<?= $k ?>"<?= $k === $first ? ' class="on"' : '' ?>><?= e($l) ?></button><?php endforeach; ?>
      </div>
      <?php if ($body !== ''): ?><div class="pane prose<?= $first === 'd' ? ' on' : '' ?>" data-pane="d"><?= $body ?></div><?php endif; ?>
      <?php if ($specs): ?><div class="pane<?= $first === 's' ? ' on' : '' ?>" data-pane="s"><table class="specs"><?php foreach ($specs as [$k, $v]): ?><tr><td><?= e($k) ?></td><td><?= e($v) ?></td></tr><?php endforeach; ?></table></div><?php endif; ?>
      <div class="pane<?= $first === 'r' ? ' on' : '' ?>" data-pane="r" style="max-width:640px">
        <p style="color:var(--muted);margin-bottom:16px"><?= e(t('product.request_text')) ?></p>
        <?php request_form(''); ?>
      </div>
    </div>

    <?php if ($related): ?>
      <div class="related">
        <div class="sh"><div><span class="label"><?= e(t('nav.catalog')) ?></span><h2><?= e(t('label.related')) ?></h2></div></div>
        <div class="pgrid g4"><?php foreach ($related as $i => $r) echo product_card($r, $i); ?></div>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php site_footer();
