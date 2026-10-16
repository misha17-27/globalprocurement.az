<?php
/* /blog/<slug>/ — məqalə: mətn, sorğu kartı, məqalədəki məhsullar (canlı qiymətlə), digər məqalələr */
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/blog.php';

$post = blog_post((string)($params['slug'] ?? ''));
if (!$post) { require __DIR__ . '/404.php'; return; }

$o     = site_origin();
$canon = $o . url('blog/' . $post['slug']);
$body  = trim((string)$post['body']);
$desc  = $post['meta_desc'] ?: mb_substr(plain_text((string)($post['lead'] ?: $body)), 0, 160);
$cover = $post['image'] ? img($post['image']) : '';
$date  = substr((string)$post['published_at'], 0, 10);
$mod   = substr((string)$post['updated_at'], 0, 10) ?: $date;
$rel   = blog_products($post);
$more  = blog_posts(3, (int)$post['id']);

$org = ['@type' => 'Organization', 'name' => 'Global Procurement Group MMC', 'url' => $o . url(''),
        'logo' => ['@type' => 'ImageObject', 'url' => $o . img('logo.png')]];
$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        array_filter([
            '@type' => 'BlogPosting', 'headline' => $post['title'], 'description' => $desc, 'inLanguage' => lang(),
            'image' => $cover !== '' ? $o . $cover : null, 'datePublished' => $date, 'dateModified' => $mod,
            'author' => $org, 'publisher' => $org, 'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canon], 'url' => $canon,
        ], function ($v) { return $v !== null && $v !== ''; }),
        ['@type' => 'BreadcrumbList', 'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => t('nav.home'), 'item' => $o . url('')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => t('blog.title'), 'item' => $o . url('blog')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $post['title'], 'item' => $canon],
        ]],
    ],
];
page_head([
    'title'   => $post['meta_title'] ?: $post['title'] . ' — Global Procurement',
    'desc'    => $desc,
    'image'   => $cover !== '' ? $cover : null,
    'og_type' => 'article',
    'schema'  => $schema,
]);
site_header('blog');
?>
<section class="phero">
  <div class="wrap">
    <nav class="crumbs" aria-label="breadcrumb"><a href="<?= e(url('')) ?>"><?= e(t('nav.home')) ?></a><span><a href="<?= e(url('blog')) ?>"><?= e(t('blog.title')) ?></a></span><span><?= e($post['title']) ?></span></nav>
    <h1><?= e($post['title']) ?></h1>
    <?php if (trim((string)$post['lead']) !== ''): ?><p><?= e($post['lead']) ?></p><?php endif; ?>
    <?php if ($date !== ''): ?><time class="pdate" datetime="<?= e($date) ?>"><?= e(blog_date($date)) ?></time><?php endif; ?>
  </div>
</section>

<section class="page">
  <div class="wrap">
    <div class="about" style="align-items:start">
      <article class="prose">
        <?php if ($cover !== ''): ?><img src="<?= e($cover) ?>" alt="<?= e($post['title']) ?>" class="pcover"><?php endif; ?>
        <?= $body ?>
      </article>
      <aside style="position:sticky;top:150px">
        <div class="ccard">
          <h3><?= e(text('cta_title')) ?></h3>
          <p style="color:var(--muted);margin-bottom:16px"><?= e(text('cta_text')) ?></p>
          <div style="display:grid;gap:10px">
            <a class="btn btn-blue" href="<?= e(url('elaqe')) ?>"><?= e(t('btn.send_request')) ?></a>
            <a class="btn btn-wa" href="<?= e(wa_link(t('wa.hello') . "\n" . $post['title'])) ?>" target="_blank" rel="noopener">WhatsApp</a>
            <a class="btn btn-white" href="<?= e(tel_link()) ?>"><?= e(setting('phone')) ?></a>
          </div>
        </div>
      </aside>
    </div>

    <?php if ($rel): ?>
      <div class="related"><div class="sh"><div><span class="label"><?= e(t('blog.products_label')) ?></span><h2><?= e(t('blog.products')) ?></h2></div></div>
        <div class="pgrid g4"><?php foreach ($rel as $i => $r) echo product_card($r, $i); ?></div></div>
    <?php endif; ?>

    <?php if ($more): ?>
      <div class="related"><div class="sh"><div><span class="label"><?= e(t('blog.title')) ?></span><h2><?= e(t('blog.more')) ?></h2></div>
        <a class="btn btn-white" href="<?= e(url('blog')) ?>"><?= e(t('blog.all')) ?> →</a></div>
        <div class="bgrid"><?php foreach ($more as $i => $p) echo blog_card($p, $i); ?></div></div>
    <?php endif; ?>
  </div>
</section>
<?php site_footer();
