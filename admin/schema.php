<?php
/*
 * Cədvəllərin yaradılması. SQLite və MySQL — hər ikisi ilə işləyir.
 * İlkin məzmun: admin/seed.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/compat.php';

function cms_is_sqlite(PDO $db): bool {
    return $db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
}

function cms_pk(PDO $db): string {
    return cms_is_sqlite($db) ? 'id INTEGER PRIMARY KEY AUTOINCREMENT' : 'id INT AUTO_INCREMENT PRIMARY KEY';
}

function cms_create_tables(PDO $db): array {
    $pk   = cms_pk($db);
    $suf  = cms_is_sqlite($db) ? '' : ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';
    $made = [];

    $tables = [
        'admins' => "$pk,
            `name` VARCHAR(120) DEFAULT '',
            `email` VARCHAR(190) NOT NULL UNIQUE,
            `pass_hash` VARCHAR(255) NOT NULL,
            `role` VARCHAR(20) DEFAULT 'editor',
            `active` INT DEFAULT 1,
            `last_login` VARCHAR(40) DEFAULT NULL",

        /* Dildən asılı olmayan parametrlər: əlaqə, SMTP, Turnstile... */
        'settings' => "`k` VARCHAR(80) PRIMARY KEY, `v` TEXT",

        /* Dilə görə mətnlər: hero, bloklar, SEO, FAQ... */
        'texts' => "`lang` VARCHAR(5) NOT NULL DEFAULT 'az', `k` VARCHAR(80) NOT NULL, `v` TEXT, PRIMARY KEY (`lang`, `k`)",

        'categories' => "$pk,
            `slug` VARCHAR(160) NOT NULL UNIQUE,
            `parent_id` INT DEFAULT 0,
            `image` VARCHAR(255) DEFAULT '',
            `sort` INT DEFAULT 0,
            `visible` INT DEFAULT 1,
            `wp_id` INT DEFAULT 0",

        'category_i18n' => "`category_id` INT NOT NULL,
            `lang` VARCHAR(5) NOT NULL,
            `name` VARCHAR(255) DEFAULT '',
            `descr` TEXT,
            `meta_title` VARCHAR(255) DEFAULT '',
            `meta_desc` TEXT,
            PRIMARY KEY (`category_id`, `lang`)",

        'products' => "$pk,
            `slug` VARCHAR(190) NOT NULL UNIQUE,
            `brand` VARCHAR(120) DEFAULT '',
            `image` VARCHAR(255) DEFAULT '',
            `gallery` TEXT,
            `price` VARCHAR(60) DEFAULT '',
            `unit` VARCHAR(40) DEFAULT '',
            `options` TEXT,
            `badge` VARCHAR(40) DEFAULT '',
            `sort` INT DEFAULT 0,
            `visible` INT DEFAULT 1,
            `featured` INT DEFAULT 0,
            `wp_id` INT DEFAULT 0,
            `created_at` VARCHAR(20) DEFAULT '',
            `updated_at` VARCHAR(20) DEFAULT ''",

        'product_i18n' => "`product_id` INT NOT NULL,
            `lang` VARCHAR(5) NOT NULL,
            `title` VARCHAR(255) DEFAULT '',
            `short` TEXT,
            `body` TEXT,
            `specs` TEXT,
            `meta_title` VARCHAR(255) DEFAULT '',
            `meta_desc` TEXT,
            PRIMARY KEY (`product_id`, `lang`)",

        'product_category' => "`product_id` INT NOT NULL, `category_id` INT NOT NULL, PRIMARY KEY (`product_id`, `category_id`)",

        /* Mətn səhifələri (Haqqımızda, WELDER...) — ünvan: /<slug>/ */
        'pages' => "$pk,
            `slug` VARCHAR(160) NOT NULL UNIQUE,
            `image` VARCHAR(255) DEFAULT '',
            `sort` INT DEFAULT 0,
            `visible` INT DEFAULT 1,
            `in_menu` INT DEFAULT 1,
            `wp_id` INT DEFAULT 0,
            `updated_at` VARCHAR(20) DEFAULT ''",

        'page_i18n' => "`page_id` INT NOT NULL,
            `lang` VARCHAR(5) NOT NULL,
            `title` VARCHAR(255) DEFAULT '',
            `lead` TEXT,
            `body` TEXT,
            `meta_title` VARCHAR(255) DEFAULT '',
            `meta_desc` TEXT,
            PRIMARY KEY (`page_id`, `lang`)",

        /* Admin girişinə uğursuz cəhdlər (IP üzrə limit) */
        'login_attempts' => "$pk, `ip` VARCHAR(64) NOT NULL, `at` INT NOT NULL",

        'submissions' => "$pk,
            `created_at` VARCHAR(20) DEFAULT '',
            `name` VARCHAR(120) DEFAULT '',
            `phone` VARCHAR(60) DEFAULT '',
            `email` VARCHAR(190) DEFAULT '',
            `company` VARCHAR(190) DEFAULT '',
            `message` TEXT,
            `items` TEXT,
            `source` VARCHAR(40) DEFAULT '',
            `lang` VARCHAR(5) DEFAULT 'az',
            `page` VARCHAR(255) DEFAULT '',
            `ip` VARCHAR(64) DEFAULT '',
            `mailed` INT DEFAULT 0,
            `is_read` INT DEFAULT 0",
    ];

    foreach ($tables as $name => $cols) {
        $db->exec("CREATE TABLE IF NOT EXISTS `$name` ($cols)$suf");
        $made[] = $name;
    }
    return $made;
}
