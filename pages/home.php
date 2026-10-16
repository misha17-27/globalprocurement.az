<?php
require_once __DIR__ . '/../includes/layout.php';

$cats     = categories();
$featured = products(['featured' => 1, 'limit' => 8]);
if (count($featured) < 4) $featured = products(['limit' => 8]);

/* Material bələdçisi: zona => məhsul slug-ları */
$zones = [
    'dam'      => ['su-v-kul-k-izolyasiya-membrani-sofit-premium-a-70', 'texnonikol-mineral-izolyasiya', 'pvc-membran-logicbase-texnonikol', 'osb-3-lovh-si-kronospan-9-12-15-18-mm'],
    'fasad'    => ['thermo-plus-vent-fasad-das-yunu', 'fasad-setkasi-isomax-160-gr', 'fasad-izolyasiya-dubeli', 'yanmaz-membran-isomax'],
    'divar'    => ['osb-lovh-12-mm', 'thermo-plus-das-yunu', 'buxar-izolyasiya-membrani-sofit-premium-b', 'polynor-3d-izolyasiya-polinor'],
    'arakesme' => ['das-yunu-texnoakustik', 'osb-lovh-plit-kronospan', 'texnonikol-mineral-izolyasiya'],
    'doseme'   => ['osb-3-lovh-si-kronospan-9-12-15-18-mm', 'penoplex-xps-lovh-l-r', 'das-yunu-texnoakustik'],
    'temel'    => ['bitum-praymer-18-lt', 'pvc-membran-logicbase-texnonikol', 'penoplex-xps-lovh-l-r', 'texnonikol-stekloizol-tol'],
];

/* OSB qalınlığı => məhsul (kalkulyatordan sorğuya əlavə üçün) */
$osbMap = [];
foreach (products(['cat' => 'kronospan-osb-lovh-l-ri']) as $p) {
    if (preg_match('/\b(9|12|15|18)\s*(mm|мм)/u', $p['title'], $m)) $osbMap[$m[1]] = ['id' => (int)$p['id'], 'slug' => $p['slug'], 't' => $p['title'], 'img' => img($p['image']), 'u' => url('product/' . $p['slug'])];
}

$schema = [
    '@context' => 'https://schema.org', '@type' => 'Organization',
    'name' => 'Global Procurement Group MMC', 'url' => site_origin() . url(''),
    'logo' => site_origin() . img('logo.png'), 'telephone' => setting('phone'), 'email' => setting('email'),
    'address' => ['@type' => 'PostalAddress', 'streetAddress' => text('address'), 'addressLocality' => 'Bakı', 'addressCountry' => 'AZ'],
];

page_head(['title' => text('seo_home_title'), 'desc' => text('seo_home_desc'), 'schema' => $schema]);
site_header('');
?>

<section class="hero">
  <div class="wrap">
    <div class="reveal">
      <span class="label g">✓ <?= e(text('hero_badge')) ?></span>
      <h1><?= mark(text('hero_title')) ?></h1>
      <p class="lead"><?= e(text('hero_lead')) ?></p>
      <form class="hsbox" action="<?= e(url('shop')) ?>" method="get">
        <select name="cat" aria-label="<?= e(t('label.categories')) ?>"><option value=""><?= e(t('search.all_cats')) ?></option>
          <?php foreach ($cats as $c): ?><option value="<?= e($c['slug']) ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
        </select>
        <input name="q" placeholder="<?= e(t('search.what')) ?>" aria-label="<?= e(t('search.what')) ?>">
        <button class="btn btn-blue" style="padding:12px 20px"><?= e(t('search.btn')) ?></button>
      </form>
      <div class="popular"><?= e(t('search.popular')) ?>:
        <a href="<?= e(url('product/osb-3-lovh-si-kronospan-9-12-15-18-mm')) ?>">OSB 18 mm</a>
        <a href="<?= e(url('shop') . '?q=' . rawurlencode('daş yunu')) ?>"><?= lang() === 'ru' ? 'Каменная вата' : 'Daş yunu' ?></a>
        <a href="<?= e(url('shop') . '?q=PVC') ?>">PVC</a>
        <a href="<?= e(url('shop') . '?brand=Penoplex') ?>">Penoplex</a>
      </div>
    </div>
    <div class="collage reveal">
      <a class="c1" href="<?= e(url('product-category/kronospan-osb-lovh-l-ri')) ?>"><img src="<?= e(img('osb.jpg')) ?>" alt="OSB-3 Kronospan"><span class="tag">OSB-3 Kronospan<small>1220×2440 · 9–18 mm</small></span></a>
      <a class="ctn" href="<?= e(url('product/thermo-plus-das-yunu')) ?>"><img src="<?= e(img('thermo-plus.webp')) ?>" alt="Thermo Plus"><span class="tag">Thermo Plus<small><?= lang() === 'ru' ? 'каменная вата' : 'daş yunu' ?></small></span></a>
      <a class="ctn" href="<?= e(url('product-category/qaynaq-apparatlari')) ?>"><img src="<?= e(img('welder-fen.jpg')) ?>" alt="WELDER" style="object-fit:cover;padding:0"><span class="tag">WELDER<small><?= e(t('hero.warranty')) ?></small></span></a>
      <div class="fbadge fb1"><span class="ic" style="background:var(--green-l);color:#4d7a1f">✓</span><div><b><?= e(t('hero.official')) ?></b><span><?= e(t('hero.official_sub')) ?></span></div></div>
      <div class="fbadge fb2"><span class="ic" style="background:var(--blue-l);color:var(--blue)">⚡</span><div><b><?= e(t('hero.fast')) ?></b><span><?= e(t('hero.fast_sub')) ?></span></div></div>
    </div>
  </div>
  <div class="wrap trust">
    <?php $icons = [
      '<path d="M12 2l8 4v6c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6z"/><path d="M8 12l3 3 5-6"/>',
      '<path d="M3 7h13v10H3zM16 10h4l1 3v4h-5"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/>',
      '<path d="M4 20V10M10 20V4M16 20v-8M22 20H2"/>',
      '<path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.5 2.5-2.5-.5-.5-2.5z"/>'];
    foreach (lines(text('trust')) as $i => [$a, $b]): ?>
      <div class="reveal"><span class="ic"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><?= $icons[$i % 4] ?></svg></span><div><b><?= e($a) ?></b><span><?= e($b) ?></span></div></div>
    <?php endforeach; ?>
  </div>
</section>

<div class="logos"><div class="track">
  <?php $brands = array_filter(array_map('trim', explode(',', text('brands'))));
  for ($r = 0; $r < 2; $r++) foreach ($brands as $b) echo '<span>' . e($b) . '</span>'; ?>
</div></div>

<!-- KATEQORİYALAR -->
<section class="sec">
  <div class="wrap">
    <div class="sh reveal"><div><span class="label"><?= e(t('label.categories')) ?></span><h2><?= e(text('cats_title')) ?></h2></div><p><?= e(text('cats_lead')) ?></p></div>
    <div class="cats">
      <?php foreach ($cats as $c): ?>
        <a href="<?= e(url('product-category/' . $c['slug'])) ?>" class="cat reveal"><div class="ci"><img src="<?= e(img($c['image'])) ?>" alt="" loading="lazy"></div><b><?= e($c['name']) ?></b><span><?= e(items_label((int)$c['cnt'])) ?></span></a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- BƏLƏDÇİ -->
<section class="sec" id="beledci" style="padding-top:0">
  <div class="wrap">
    <div class="sh reveal"><div><span class="label"><?= e(t('nav.guide')) ?></span><h2><?= e(text('guide_title')) ?></h2></div><p><?= e(text('guide_lead')) ?></p></div>
    <div class="explorer">
      <div class="ex-list reveal">
        <?php $n = 0; foreach ($zones as $z => $_): $n++; ?>
          <button class="ex-btn<?= $n === 1 ? ' on' : '' ?>" type="button" data-z="<?= $z ?>"><span class="n"><?= $n ?></span><div><b><?= e(t("zone.$z")) ?></b><span><?= e(t("zone.$z.d")) ?></span></div></button>
        <?php endforeach; ?>
      </div>
      <div class="ex-main reveal">
        <div class="house">
          <svg viewBox="0 0 600 400" role="img" aria-label="<?= e(t('nav.guide')) ?>">
            <line x1="20" y1="340" x2="580" y2="340" stroke="#c6d3e4" stroke-dasharray="4 6"/>
            <g class="zone on" data-z="dam"><path class="z" d="M70 160 L300 36 L530 160 L500 160 L300 62 L100 160 Z"/></g>
            <g class="zone" data-z="divar"><path class="z" d="M120 165 L300 72 L480 165 L480 320 L120 320 Z"/></g>
            <g class="zone" data-z="fasad"><path class="z" d="M100 160 L120 160 L120 340 L100 340 Z M480 160 L500 160 L500 340 L480 340 Z"/></g>
            <g class="zone" data-z="arakesme"><path class="z" d="M292 190 L308 190 L308 320 L292 320 Z"/></g>
            <g class="zone" data-z="doseme"><path class="z" d="M120 320 L480 320 L480 340 L120 340 Z"/></g>
            <g class="zone" data-z="temel"><path class="z" d="M90 340 L510 340 L510 390 L90 390 Z"/></g>
            <rect x="170" y="215" width="70" height="56" rx="4" fill="#f3f6fb" stroke="#c6d3e4" pointer-events="none"/>
            <rect x="360" y="215" width="70" height="56" rx="4" fill="#f3f6fb" stroke="#c6d3e4" pointer-events="none"/>
          </svg>
        </div>
        <?php $n = 0; foreach ($zones as $z => $slugs): $n++; ?>
          <div class="ex-prods" data-zone="<?= $z ?>"<?= $n === 1 ? '' : ' hidden' ?>>
            <?php foreach (products(['slugs' => $slugs]) as $p): ?>
              <a class="ex-p" href="<?= e(url('product/' . $p['slug'])) ?>"><img src="<?= e(img($p['image'])) ?>" alt="" loading="lazy"><b><?= e($p['title']) ?></b></a>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- MƏHSULLAR -->
<section class="sec" style="padding-top:0">
  <div class="wrap">
    <div class="sh reveal"><div><span class="label"><?= e(t('nav.catalog')) ?></span><h2><?= e(text('products_title')) ?></h2></div><a href="<?= e(url('shop')) ?>" class="btn btn-white"><?= e(t('btn.all_products')) ?> →</a></div>
    <div class="pgrid g4">
      <?php foreach ($featured as $i => $p) echo product_card($p, $i); ?>
    </div>
  </div>
</section>

<!-- OSB -->
<section class="sec" id="osb" style="padding-top:0">
  <div class="wrap">
    <div class="sh reveal"><div><span class="label g"><?= e(t('osb.kicker')) ?></span><h2><?= e(text('osb_title')) ?></h2></div><p><?= e(text('osb_lead')) ?></p></div>
    <div class="osbwrap">
      <div class="osb-hero reveal"><img src="<?= e(img('osb.jpg')) ?>" alt="OSB-3 Kronospan" loading="lazy"><div class="ov"><h3>Kronospan OSB-3</h3><p><?= e(t('osb.sub')) ?></p></div></div>
      <div class="cmp reveal">
        <table id="cmp">
          <thead><tr><th><?= e(t('osb.use')) ?></th><?php foreach ([9, 12, 15, 18] as $i => $mm): ?><th data-c="<?= $i + 1 ?>"><b><?= $mm ?></b>mm</th><?php endforeach; ?></tr></thead>
          <tbody>
          <?php foreach (lines(t('osb.rows'), 5) as $row): ?>
            <tr><td><?= e($row[0]) ?></td><?php for ($k = 1; $k <= 4; $k++): $v = $row[$k]; ?><td><i class="dot<?= $v === '1' ? '' : ($v === '½' ? ' half' : ' no') ?>"></i></td><?php endfor; ?></tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <p style="font-size:12.5px;color:var(--muted);margin-top:12px"><i class="dot"></i> <?= e(t('osb.yes')) ?> &nbsp; <i class="dot half"></i> <?= e(t('osb.half')) ?> &nbsp; <i class="dot no"></i> <?= e(t('osb.no')) ?></p>
        <div class="calcrow">
          <div><label for="ct"><?= e(t('osb.thickness')) ?></label><select id="ct"><?php foreach ([9, 12, 15, 18] as $mm): ?><option value="<?= $mm ?>"<?= $mm === 15 ? ' selected' : '' ?>><?= $mm ?> mm</option><?php endforeach; ?></select></div>
          <div><label for="ca"><?= e(t('osb.area')) ?></label><input type="number" id="ca" value="80" min="1" inputmode="numeric"></div>
          <div class="out"><b id="cn">30</b><span><?= e(t('osb.sheets')) ?></span></div>
        </div>
        <button type="button" class="btn btn-navy" style="width:100%;margin-top:14px" id="cAdd" data-osb="<?= e(json_encode($osbMap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>"><?= e(t('osb.add')) ?> →</button>
      </div>
    </div>
  </div>
</section>

<!-- HAQQIMIZDA -->
<section class="sec" style="padding-top:0">
  <div class="wrap">
    <div class="about">
      <div class="reveal">
        <span class="label"><?= e(t('nav.about')) ?></span>
        <h2><?= e(text('about_title')) ?></h2>
        <?php foreach (preg_split('/\R\s*\R/u', text('about_body')) as $para): ?><p><?= e(trim($para)) ?></p><?php endforeach; ?>
        <a href="<?= e(url('haqqimizda')) ?>" class="btn btn-navy" style="margin-top:10px"><?= e(t('btn.more')) ?> →</a>
      </div>
      <div class="nums reveal">
        <?php foreach (array_slice(lines(text('stats'), 3), 0, 4) as [$num, $suf, $lbl]): ?>
          <div><b data-count="<?= (int)$num ?>" data-suf="<?= e($suf) ?>"><?= (int)$num . e($suf) ?></b><span><?= e($lbl) ?></span></div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="dist">
      <?php foreach (lines(text('distributors'), 3) as [$tag, $name, $d]): ?>
        <div class="reveal"><span class="label g"><?= e($tag) ?></span><b><?= e($name) ?></b><p><?= e($d) ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- WELDER -->
<section class="sec" style="padding-top:0">
  <div class="wrap">
    <div class="welder reveal">
      <div class="l">
        <span class="label" style="background:rgba(255,255,255,.1);color:#9fd36a">WELDER · <?= e(t('welder.partner')) ?></span>
        <h2><?= e(text('welder_title')) ?></h2>
        <p><?= e(text('welder_text')) ?></p>
        <div class="grid"><?php foreach (lines(text('welder_points')) as [$a, $b]): ?><div><b><?= e($a) ?></b><span><?= e($b) ?></span></div><?php endforeach; ?></div>
        <div style="display:flex;gap:10px;flex-wrap:wrap"><a href="<?= e(url('product-category/qaynaq-apparatlari')) ?>" class="btn btn-blue"><?= e(t('welder.btn')) ?> →</a><?php if (page_by_slug('welder-qaynag-avadanliqlari')): ?><a href="<?= e(url('welder-qaynag-avadanliqlari')) ?>" class="btn" style="border:1px solid rgba(255,255,255,.3);color:#fff"><?= e(t('btn.more')) ?></a><?php endif; ?></div>
      </div>
      <div class="r"><img src="<?= e(img('welder-fen.jpg')) ?>" alt="WELDER" loading="lazy"></div>
    </div>
  </div>
</section>

<!-- BLOQ: son məqalələr -->
<?php require_once __DIR__ . '/../includes/blog.php'; if ($homePosts = blog_posts(3)): ?>
<section class="sec" style="padding-top:0">
  <div class="wrap">
    <div class="sh reveal"><div><span class="label"><?= e(t('blog.title')) ?></span><h2><?= e(t('blog.h1')) ?></h2></div>
      <a href="<?= e(url('blog')) ?>" class="btn btn-white"><?= e(t('blog.all')) ?> →</a></div>
    <div class="bgrid"><?php foreach ($homePosts as $i => $hp) echo blog_card($hp, $i); ?></div>
  </div>
</section>
<?php endif; ?>

<!-- FAQ -->
<section class="sec" style="padding-top:0">
  <div class="wrap faq">
    <div class="reveal"><span class="label"><?= e(t('faq.kicker')) ?></span><h2 style="font-size:clamp(28px,3.6vw,46px);font-weight:800;margin:14px 0"><?= e(t('faq.title')) ?></h2><p style="color:var(--muted)"><?= e(t('faq.more')) ?> <a href="<?= e(tel_link()) ?>" style="color:var(--blue);font-weight:700"><?= e(setting('phone')) ?></a></p></div>
    <div class="reveal" id="faq">
      <?php foreach (lines(text('faq')) as $i => [$q, $a]): ?>
        <div class="qa<?= $i === 0 ? ' open' : '' ?>"><button type="button"><?= e($q) ?><i>+</i></button><div class="ans"><div><p><?= e($a) ?></p></div></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ƏLAQƏ -->
<section id="elaqe">
  <div class="wrap">
    <div class="cta reveal">
      <div style="position:relative;z-index:1">
        <h2><?= e(text('cta_title')) ?></h2>
        <p><?= e(text('cta_text')) ?></p>
        <div class="cl">
          <a href="<?= e(tel_link()) ?>">☎<div><small><?= e(t('contact.phone')) ?></small><b><?= e(setting('phone')) ?></b></div></a>
          <a href="<?= e(wa_link(t('wa.hello'))) ?>" target="_blank" rel="noopener">💬<div><small>WhatsApp</small><b><?= e(t('contact.wa_write')) ?></b></div></a>
          <a href="mailto:<?= e(setting('email')) ?>">✉<div><small><?= e(t('contact.email')) ?></small><b><?= e(setting('email')) ?></b></div></a>
          <div>📍<div><small><?= e(t('contact.address')) ?></small><b><?= e(text('address')) ?></b></div></div>
        </div>
      </div>
      <?php request_form(t('form.title')); ?>
    </div>
  </div>
</section>

<?php site_footer();
