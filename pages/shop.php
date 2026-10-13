<?php
require_once __DIR__ . '/../includes/layout.php';

$slug  = $params['cat'] ?? (string)($_GET['cat'] ?? '');
$q     = trim(mb_substr((string)($_GET['q'] ?? ''), 0, 80));
$brand = trim(mb_substr((string)($_GET['brand'] ?? ''), 0, 80));
$cat   = $slug !== '' ? category_by_slug($slug) : null;

if ($slug !== '' && !$cat) { require __DIR__ . '/404.php'; return; }
/* ?cat=... — kanonik kateqoriya ünvanına */
if (!isset($params['cat']) && $cat) {
    $qs = http_build_query(array_filter(['q' => $q, 'brand' => $brand]));
    header('Location: ' . url('product-category/' . $cat['slug']) . ($qs ? '?' . $qs : ''), true, 301); exit;
}

$list   = products(['cat' => $cat['slug'] ?? '', 'q' => $q, 'brand' => $brand]);
$cats   = categories();
$brands = brands();
$total  = count(products());

$title = $cat ? $cat['name'] : t('nav.catalog');
$meta  = $cat
    ? ['title' => ($cat['meta_title'] ?: $cat['name'] . ' — Global Procurement'), 'desc' => ($cat['meta_desc'] ?: mb_substr(strip_tags((string)$cat['descr']), 0, 160) ?: text('seo_shop_desc'))]
    : ['title' => text('seo_shop_title'), 'desc' => text('seo_shop_desc')];
if ($q !== '' || $brand !== '') $meta['noindex'] = true;

$self = function (array $over) use ($cat, $q, $brand) {
    $p = array_merge(['q' => $q, 'brand' => $brand], $over);
    $qs = http_build_query(array_filter($p, 'strlen'));
    return ($cat ? url('product-category/' . $cat['slug']) : url('shop')) . ($qs ? '?' . $qs : '');
};

page_head($meta);
site_header('shop');
?>
<section class="phero">
  <div class="wrap">
    <nav class="crumbs" aria-label="breadcrumb"><a href="<?= e(url('')) ?>"><?= e(t('nav.home')) ?></a><span><a href="<?= e(url('shop')) ?>"><?= e(t('nav.catalog')) ?></a></span><?php if ($cat): ?><span><?= e($cat['name']) ?></span><?php endif; ?></nav>
    <h1><?= e($title) ?></h1>
    <?php if ($cat && trim((string)$cat['descr']) !== ''): ?><p><?= e(preg_split('/\R\s*\R/u', trim(strip_tags((string)$cat['descr'])))[0]) ?></p>
    <?php elseif (!$cat): ?><p><?= e(text('seo_shop_desc')) ?></p><?php endif; ?>
  </div>
</section>

<section class="page">
  <div class="wrap catalog">
    <aside class="side">
      <form class="sform" action="<?= e($cat ? url('product-category/' . $cat['slug']) : url('shop')) ?>" method="get">
        <input name="q" value="<?= e($q) ?>" placeholder="<?= e(t('search.ph')) ?>" aria-label="<?= e(t('search.ph')) ?>">
        <?php if ($brand !== ''): ?><input type="hidden" name="brand" value="<?= e($brand) ?>"><?php endif; ?>
        <button class="btn btn-blue" style="padding:10px 14px" aria-label="<?= e(t('search.btn')) ?>">→</button>
      </form>
      <h4><?= e(t('label.categories')) ?></h4>
      <a class="chk<?= !$cat ? ' on' : '' ?>" href="<?= e(url('shop')) ?>"><?= e(t('label.all')) ?><em><?= $total ?></em></a>
      <?php foreach ($cats as $c): ?>
        <a class="chk<?= $cat && $cat['id'] === $c['id'] ? ' on' : '' ?>" href="<?= e(url('product-category/' . $c['slug'])) ?>"><?= e($c['name']) ?><em><?= (int)$c['cnt'] ?></em></a>
      <?php endforeach; ?>
      <?php if ($brands): ?>
        <h4><?= e(t('label.brands')) ?></h4>
        <div class="brandf">
          <?php foreach ($brands as $b => $n): ?><a href="<?= e($self(['brand' => $brand === $b ? '' : $b])) ?>"<?= $brand === $b ? ' class="on"' : '' ?>><?= e($b) ?></a><?php endforeach; ?>
        </div>
      <?php endif; ?>
    </aside>
    <div>
      <div class="ctop">
        <span class="cnt"><b><?= count($list) ?></b> <?= e(t('label.found')) ?>
          <?php if ($q !== ''): ?> · «<?= e($q) ?>» <a href="<?= e($self(['q' => ''])) ?>" style="color:var(--blue)">✕</a><?php endif; ?>
          <?php if ($brand !== ''): ?> · <?= e($brand) ?> <a href="<?= e($self(['brand' => ''])) ?>" style="color:var(--blue)">✕</a><?php endif; ?>
        </span>
        <div class="view"><button class="on" type="button" data-v="grid">▦ <?= e(t('label.grid')) ?></button><button type="button" data-v="list">☰ <?= e(t('label.list')) ?></button></div>
      </div>
      <div class="pgrid" id="pgrid">
        <?php foreach ($list as $i => $p) echo product_card($p, $i); ?>
        <?php if (!$list): ?><div class="empty"><?= e(t('search.none')) ?><br><br><a class="btn btn-white" href="<?= e(url('shop')) ?>"><?= e(t('btn.all_products')) ?></a></div><?php endif; ?>
      </div>
      <?php $paras = $cat ? array_slice(preg_split('/\R\s*\R/u', trim(strip_tags((string)$cat['descr']))), 1) : [];
      if ($paras && $q === '' && $brand === ''): ?>
        <div class="prose" style="margin-top:50px;padding-top:34px;border-top:1px solid var(--line)"><?php foreach ($paras as $para): ?><p><?= nl2br(e(trim($para))) ?></p><?php endforeach; ?></div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php site_footer();
