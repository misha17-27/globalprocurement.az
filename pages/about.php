<?php
/*
 * /haqqimizda/ — admin → «Страницы» bölməsində «haqqimizda» səhifəsi varsa, onun mətni;
 * yoxdursa «Тексты сайта» → «Страница «О компании»».
 */
$pg = page_by_slug('haqqimizda') ?? [
    'slug' => 'haqqimizda', 'title' => text('about_title'), 'lead' => strtok(text('about_body'), "\n"),
    'body' => simple_md(text('about_page')), 'image' => '', 'meta_title' => text('seo_about_title'), 'meta_desc' => text('seo_about_desc'),
];
if (empty($pg['meta_title'])) $pg['meta_title'] = text('seo_about_title');
if (empty($pg['meta_desc']))  $pg['meta_desc']  = text('seo_about_desc');
require __DIR__ . '/page.php';
