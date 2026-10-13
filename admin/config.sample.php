<?php
/*
 * Bu faylı  config.php  adı ilə kopyalayın və məlumatlarınızı yazın.
 * config.php git-ə düşmür — parollar repozitoriyada saxlanılmır.
 *
 * driver: 'sqlite' — heç bir quraşdırma tələb etmir (lokal iş üçün)
 *         'mysql'  — cPanel hostinqi üçün (MySQL Databases bölməsindən)
 */
return [
    'driver' => 'sqlite',

    // --- sqlite ---
    'sqlite_path' => __DIR__ . '/../storage/site.sqlite',

    // --- mysql ---
    'db_host'    => 'localhost',
    'db_name'    => 'globalprocuremen_site',
    'db_user'    => 'globalprocuremen_site',
    'db_pass'    => 'CHANGE_ME',
    'db_charset' => 'utf8mb4',

    // install.php ilk admin hesabını bu məlumatlarla yaradır (sonra paneldən dəyişin)
    'install_admin_email' => 'admin@globalprocurement.az',
    'install_admin_pass'  => 'CHANGE_ME_2026',

    // install.php-ni brauzerdə işə salmaq üçün gizli açar
    'install_token' => 'CHANGE_ME_TOKEN',

    // Sayt alt qovluqdadırsa avtomatik tapılır; lazım olsa əl ilə: '/yeni'
    'base_url' => null,
];
