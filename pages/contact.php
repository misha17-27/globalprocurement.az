<?php
require_once __DIR__ . '/../includes/layout.php';

$mapQ = setting('map_query', 'Dərnəgül, Bakı');
page_head(['title' => text('seo_contact_title'), 'desc' => text('seo_contact_desc')]);
site_header('elaqe');
?>
<section class="phero">
  <div class="wrap">
    <nav class="crumbs" aria-label="breadcrumb"><a href="<?= e(url('')) ?>"><?= e(t('nav.home')) ?></a><span><?= e(t('nav.contact')) ?></span></nav>
    <h1><?= e(t('nav.contact')) ?></h1>
    <p><?= e(text('cta_text')) ?></p>
  </div>
</section>

<section class="page">
  <div class="wrap">
    <div class="cgrid">
      <div class="ccard">
        <h3><?= e(t('contact.title')) ?></h3>
        <div class="clist2">
          <a href="<?= e(tel_link()) ?>"><span class="ic">☎</span><div><small><?= e(t('contact.phone')) ?></small><b><?= e(setting('phone')) ?></b></div></a>
          <a href="<?= e(wa_link(t('wa.hello'))) ?>" target="_blank" rel="noopener"><span class="ic">💬</span><div><small>WhatsApp</small><b><?= e(t('contact.wa_write')) ?></b></div></a>
          <a href="mailto:<?= e(setting('email')) ?>"><span class="ic">✉</span><div><small><?= e(t('contact.email')) ?></small><b><?= e(setting('email')) ?></b></div></a>
          <div><span class="ic">📍</span><div><small><?= e(t('contact.address')) ?></small><b><?= e(text('address')) ?></b></div></div>
          <?php if (text('work_hours') !== ''): ?><div><span class="ic">🕘</span><div><small><?= e(t('contact.hours')) ?></small><b><?= e(text('work_hours')) ?></b></div></div><?php endif; ?>
        </div>
      </div>
      <div class="ccard">
        <h3><?= e(t('form.title')) ?></h3>
        <?php request_form(''); ?>
      </div>
    </div>
    <?php if ($mapQ !== ''): ?>
      <div class="map"><iframe loading="lazy" title="<?= e(t('contact.address')) ?>" src="https://maps.google.com/maps?q=<?= rawurlencode($mapQ) ?>&amp;z=13&amp;output=embed"></iframe></div>
    <?php endif; ?>
  </div>
</section>

<?php site_footer();
