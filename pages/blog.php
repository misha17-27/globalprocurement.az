<?php
/* /blog/ — məqalələr siyahısı (admin → «Блог»). SEO: admin → SEO → «Блог». */
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/blog.php';

$posts = blog_posts();
$o = site_origin();
$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Blog', 'name' => t('blog.title') . ' — Global Procurement', 'url' => $o . url('blog'), 'inLanguage' => lang(),
            'blogPost' => array_map(function ($p) use ($o) {
                return ['@type' => 'BlogPosting', 'headline' => $p['title'], 'url' => $o . url('blog/' . $p['slug']),
                        'datePublished' => substr((string)$p['published_at'], 0, 10)];
            }, $posts),
        ],
        ['@type' => 'BreadcrumbList', 'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => t('nav.home'), 'item' => $o . url('')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => t('blog.title'), 'item' => $o . url('blog')],
        ]],
    ],
];
page_head([
    'title'  => text('seo_blog_title', t('blog.meta_title')),
    'desc'   => text('seo_blog_desc', t('blog.meta_desc')),
    'image'  => $posts && $posts[0]['image'] ? img($posts[0]['image']) : null,
    'schema' => $schema,
]);
site_header('blog');
?>
<section class="phero">
  <div class="wrap">
    <nav class="crumbs" aria-label="breadcrumb"><a href="<?= e(url('')) ?>"><?= e(t('nav.home')) ?></a><span><?= e(t('blog.title')) ?></span></nav>
    <h1><?= e(t('blog.h1')) ?></h1>
    <p><?= e(t('blog.lead')) ?></p>
  </div>
</section>

<section class="page">
  <div class="wrap">
    <?php if (!$posts): ?>
      <p class="muted"><?= e(t('blog.empty')) ?></p>
    <?php else: ?>
      <div class="bgrid"><?php foreach ($posts as $i => $p) echo blog_card($p, $i); ?></div>
    <?php endif; ?>
    <div class="bcta">
      <div><h3><?= e(text('cta_title')) ?></h3><p><?= e(text('cta_text')) ?></p></div>
      <div class="bcta-acts">
        <a class="btn btn-blue" href="<?= e(url('shop')) ?>"><?= e(t('nav.catalog')) ?> →</a>
        <a class="btn btn-wa" href="<?= e(wa_link(t('wa.hello'))) ?>" target="_blank" rel="noopener">WhatsApp</a>
      </div>
    </div>
  </div>
</section>
<?php site_footer();
