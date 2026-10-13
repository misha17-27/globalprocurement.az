<?php
require_once __DIR__ . '/../includes/layout.php';

http_response_code(404);
if (!db()) { echo '<meta charset="utf-8"><h1>404</h1>'; return; }
page_head(['title' => '404 — Global Procurement', 'noindex' => true]);
site_header();
?>
<section class="nf">
  <div class="wrap">
    <b>404</b>
    <h1><?= e(t('404.title')) ?></h1>
    <p><?= e(t('404.text')) ?></p>
    <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap">
      <a class="btn btn-blue" href="<?= e(url('shop')) ?>"><?= e(t('nav.catalog')) ?></a>
      <a class="btn btn-white" href="<?= e(url('')) ?>"><?= e(t('nav.home')) ?></a>
    </div>
  </div>
</section>
<?php site_footer();
