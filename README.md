# globalprocurement.az — PHP sayt

Global Procurement Group MMC saytının WordPress-siz, təmiz PHP versiyası.
Dizayn: **v5 (işıqlı)** — `designs/index5.html`. Admin panel caucasuscl.com-un panelinin quruluşu ilə.
Onlayn ödəniş yoxdur: sorğu **e-poçta** gəlir, **admin → Заявки**-yə yazılır və müştəridə **WhatsApp**-da açılır.

## Lokal işə salmaq

```bash
cp admin/config.sample.php admin/config.php      # driver => sqlite
php -S localhost:8020 -t . router.php
```

Sonra bir dəfə: `http://localhost:8020/admin/install.php?token=<install_token>` — cədvəllər, ilkin mətnlər, admin hesabı.

## Serverə yükləmə (cPanel → Git Version Control)

1. cPanel → **Git Version Control** → Create → Clone URL: bu repozitoriya.
2. **Manage → Pull or Deploy → Deploy HEAD Commit** — `.cpanel.yml` faylları `public_html/yeni/`-yə kopyalayır.
3. cPanel → **MySQL Databases**: baza + istifadəçi yaradın.
4. `public_html/yeni/admin/config.php` yaradın (`config.sample.php`-dən): `driver => 'mysql'`, baza məlumatları, öz parolunuz və tokeniniz.
5. Bir dəfə açın: `/yeni/admin/install.php?token=...` → sonra panelə daxil olun.
6. Panel → **Импорт из WordPress** → «Импортировать»: köhnə saytdan bütün məhsullar, kateqoriyalar,
   səhifələr, şəkillər və SEO gəlir (eyni hostinqdə `wp-content/uploads` birbaşa kopyalanır).
7. Panel → **Почта (SMTP)**: sorğuların gedəcəyi poçt və SMTP (cPanel → Email Accounts → Connect Devices).

**Canlı:** `.cpanel.yml` → `DEPLOYPATH=/home/globalprocuremen/public_html/` (əsas domen).
Köhnə WordPress faylları `/home/globalprocuremen/wp-old/`-da (veb-dən açılmır); `admin/config.php` serverdə əl ilə saxlanılır.

## Struktur

```
index.php            front controller — bütün ünvanlar (WordPress-dəki kimi)
router.php           yalnız `php -S` üçün
includes/
  bootstrap.php      konfiqurasiya, dil, baza, köməkçilər, SMTP
  layout.php         head / header / footer / məhsul kartı / sorğu siyahısı
  request.php        POST /api/request — sorğu: baza + e-poçt + WhatsApp linki
  lang/az.php ru.php interfeys sətirləri
pages/               home, shop, product, page (mətn səhifələri), about, contact, 404, sitemap
admin/               panel (index.php), lib.php, schema.php, seed.php, import.php, install.php
assets/              css/site.css, js/site.js, img/ (products/ — idxal olunmuş şəkillər)
tools/               wpdump.py + import-local.php — SQL dump-dan lokal idxal
designs/             5 dizayn variantı (statik HTML)
```

## Ünvanlar

| Səhifə | AZ | RU |
|---|---|---|
| Ana səhifə | `/` | `/ru/` |
| Kataloq | `/shop/` (`?q=`, `?brand=`) | `/ru/shop/` |
| Kateqoriya | `/product-category/<slug>/` | `/ru/product-category/<slug>/` |
| Məhsul | `/product/<slug>/` | `/ru/product/<slug>/` |
| Haqqımızda | `/haqqimizda/` | `/ru/haqqimizda/` |
| Mətn səhifələri | `/<slug>/` (məs. `/welder-qaynag-avadanliqlari/`) | `/ru/<slug>/` |
| Əlaqə | `/elaqe/` | `/ru/elaqe/` |

Köhnə WordPress ünvanları 301 ilə yönləndirilir: `/contact-us/ → /elaqe/`, `/ana-sehife/ → /sirket-haqqinda/`,
`/about-us-3/`, `/cart/`, `/checkout/`, `/wishlist/` və s.

## Admin panel — `/admin/`

| Bölmə | Nə idarə olunur |
|---|---|
| Обзор | statistika, son sorğular, kataloqun vəziyyəti |
| Заявки | saytdan gələn sorğular: məhsullar, miqdar, WhatsApp/zəng düymələri |
| Товары | ad / təsvir / xüsusiyyətlər / SEO (AZ, RU), şəkil + qalereya, qiymət, variantlar, kateqoriyalar, «ana səhifədə» |
| Категории | ad, təsvir, SEO, şəkil, sıra |
| Импорт из WordPress | köhnə saytdan məhsul, kateqoriya, səhifə, şəkil və SEO köçürmə |
| Страницы | Haqqımızda, WELDER, Şirkət haqqında və yeni səhifələr |
| Тексты сайта | ana səhifənin bütün blokları, FAQ, rəqəmlər (AZ / RU) |
| Изображения | yükləmə və media kitabxanası |
| Контакты | telefon, **WhatsApp nömrəsi (sorğular buraya)**, e-poçt, ünvan, xəritə |
| SEO | səhifələrin title/description, Google Analytics |
| Почта (SMTP) | sorğuların gedəcəyi ünvan, SMTP, test məktubu |
| Безопасность | Cloudflare Turnstile (girişdə kapça) |
| Пользователи / Мой профиль | hesablar və parollar |

## Təhlükəsizlik

| Sahə | Müdafiə |
|---|---|
| Sorğu formaları (`/api/request`) | yalnız JSON ≤ 32 KB; mənşə yoxlaması (Origin/Referer); bot tələsi (gizli sahə); imzalı vaxt möhürü — forma ≥ 3 san. əvvəl açılmalı, ≤ 24 saat; saatda 6 sorğu / sessiya və 15 / IP; Cloudflare Turnstile (admin → Безопасность); məhsul adları yalnız bazadan |
| Real IP | `CF-Connecting-IP` yalnız Cloudflare diapazonlarından gələn sorğuda qəbul olunur (`includes/security.php`) |
| Admin girişi | CSRF; sessiyada 6 cəhd; IP üzrə 10 cəhd / 15 dəq. (bazada — cookie silməklə yayınmaq olmur); Turnstile; sessiya 2 saat boşdayanma / 12 saat; `SameSite=Strict`, `HttpOnly`, `Secure` |
| Admin əməliyyatları | hər POST-da CSRF; rol yoxlaması (Администратор / Редактор); bütün çıxış `htmlspecialchars` |
| HTML məzmun | təsvir və səhifələr ağ siyahı ilə təmizlənir: script, style, iframe, on* atributları, `javascript:` linkləri silinir |
| Yükləmələr | yalnız şəkil (uzantı + MIME + `getimagesize`), ≤ 15 MB, təsadüfi ad; `assets/`-də PHP icrası bağlıdır |
| HTTP başlıqları | CSP, X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy, HSTS (HTTPS-də) — PHP-dən göndərilir |
| Fayllar | `admin/config.php`, baza, loglar, `includes/`, `pages/`, `storage/`, gizli fayllar brauzerdən bağlıdır; `install.php` quraşdırmadan sonra bağlanır |
| Xətalar | ziyarətçiyə göstərilmir → `storage/php-errors.log` |
| Host başlığı | canonical / OG / e-poçt yalnız icazəli domenlə (`ALLOWED_HOSTS`) |

Tövsiyə: Cloudflare-də SSL rejimi **Full (strict)** + cPanel-də AutoSSL; Turnstile açarlarını admin → Безопасность-a yazın.

## Köhnə saytdan nə köçürülmədi

* **3118 «blog» yazısı** — hamısı kazino/mərc spamıdır (sayt sındırılıb), şirkətə aid yazı yoxdur.
* «About us» və «Əlaqə»-dəki FAQ — Woodmart mövzusunun demo mətnləri (lorem ipsum).
* Səhifələrə yeridilmiş spam linklər (ghostwriter və s.) idxal zamanı avtomatik silinir.

## Lokal idxal (SQL dump-dan)

```bash
python -I tools/wpdump.py globalprocuremen_7.sql global.zip <qovluq>
php tools/import-local.php <qovluq>            # --keep: yalnız boş sahələri doldur
```
