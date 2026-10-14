<?php
/*
 * İlkin məzmun: kateqoriyalar, məhsullar, mətnlər və parametrlər.
 * Mövcud sətirlərə toxunmur — təkrar işə salmaq təhlükəsizdir.
 * Məhsulların tam təsviri və şəkilləri WordPress-dən köçürülüb (tools/import-local.php).
 */
declare(strict_types=1);

function seed_settings(): array {
    return [
        'phone'         => '+994 51 304 00 43',
        'whatsapp'      => '994513040043',
        'email'         => 'ceo@globalprocurement.az',
        'mail_to'       => 'ceo@globalprocurement.az',
        'map_query'     => 'Dərnəgül, Bakı',
        'instagram'     => '',
        'facebook'      => '',
        'wp_url'        => 'https://globalprocurement.az',
        'ga_id'         => '',
    ];
}

function seed_texts(): array {
    return [
        'az' => [
            'site_name'      => 'Global Procurement',
            'address'        => 'Dərnəgül, Baksol yolu, Bakı',
            'work_hours'     => 'B.e. – Şənbə, 09:00 – 18:00',
            'announce'       => 'Kronospan OSB-3 — 9, 12, 15, 18 mm anbarda. Topdan alışa xüsusi qiymət',
            'hero_badge'     => 'Kronospan · Polinor · ISOMAX RUS · WELDER rəsmi distribyutoru',
            'hero_title'     => 'Tikinti və izolyasiya üçün *etibarlı* material təchizatı',
            'hero_lead'      => 'OSB-3 lövhələri, fasad, hidro, termo və akustik izolyasiya — birbaşa zavoddan idxal, topdan və pərakəndə satış Bakıda.',
            'trust'          => "Orijinal məhsul | sertifikatlı, zavoddan\nBirbaşa idxal | vasitəçisiz qiymət\nTopdan və pərakəndə | istənilən həcm\nServis Bakıda | WELDER avadanlığı",
            'brands'         => 'KRONOSPAN, TECHNONICOL, POLYNOR, ISOMAX RUS, WELDER, PENOPLEX, SOFIT, THERMO PLUS',
            'cats_title'     => 'Nə tikirsiniz?',
            'cats_lead'      => 'Kateqoriyanı seçin — uyğun məhsulları görün.',
            'guide_title'    => 'Evin hər zonası üçün düzgün həll',
            'guide_lead'     => 'Zonanı seçin və ya sxemdə klikləyin — tövsiyə olunan materiallar aşağıda görünəcək.',
            'products_title' => 'Seçilmiş məhsullar',
            'products_lead'  => 'Ən çox sorğu gələn məhsullar. Bütün çeşid kataloqdadır.',
            'osb_title'      => 'Hansı qalınlıq sizə uyğundur?',
            'osb_lead'       => 'Kronospan OSB-3 lövhələrinin tətbiq sahələrinə görə müqayisəsi və lövhə kalkulyatoru.',
            'about_title'    => 'Global Procurement Group — xarici zavodların rəsmi nümayəndəsi',
            'about_body'     => "Global Procurement Group MMC — Azərbaycanda Kronospan (UltraPly / UltraDekor) zavodunun rəsmi distribyutorudur. Biz Rusiyadan birbaşa zavoddan idxal olunan OSB lövhələrin topdan və pərakəndə satışını həyata keçiririk.\n\nOSB-3 — nəmişliyə davamlı yüksək keyfiyyətli lövhələr: qalınlıq 9, 12, 15, 18 mm, ölçü 1220 × 2440 mm. Həmçinin fasad məhsulları, hidro və termo izolyasiya materialları, qaynaq avadanlıqları təklif edirik.",
            'stats'          => "4 | | rəsmi distribyutorluq\n8 | | beynəlxalq brend\n21 | + | məhsul kataloqda\n7 | | məhsul kateqoriyası",
            'distributors'   => "Rəsmi distribyutor | Kronospan | UltraPly / UltraDekor OSB-3 lövhələri\nRəsmi distribyutor | Polinor | Polinor 3D püskürtmə poliüretan izolyasiya\nRəsmi distribyutor | ISOMAX RUS | Fasad setkası, dübel, künc profili, yanmaz membran\nRəsmi tərəfdaş | WELDER | Membran qaynaq avadanlıqları və fenlər",
            'welder_title'   => 'PVC, HDPE, LDPE membranlar üçün qaynaq avadanlıqları',
            'welder_text'    => 'Dam örtükləri, fundament hidroizolyasiyası, tunellər, rezervuarlar və sənaye obyektləri üçün möhkəm, uzunömürlü qaynaq tikişi.',
            'welder_points'  => "1 il zəmanət | bütün avadanlıqlara\nServis Bakıda | texniki dəstək\nMəsləhət və təlim | avadanlıq seçimi\nOrijinal | birbaşa istehsalçıdan",
            'faq'            => "OSB-3 lövhələri hansı ölçülərdə var? | Kronospan OSB-3 lövhələri 1220 × 2440 mm ölçüdə, 9, 12, 15 və 18 mm qalınlıqlarda mövcuddur.\nTopdan alış üçün xüsusi qiymət varmı? | Bəli, tikinti şirkətləri və dilerlər üçün həcmə görə xüsusi şərtlər var. Sorğu göndərin — kommersiya təklifi hazırlayaq.\nWELDER avadanlıqlarına zəmanət nə qədərdir? | Bütün WELDER avadanlıqlarına 1 il rəsmi zəmanət verilir, servis və texniki dəstək Bakıda göstərilir.\nSifarişi necə verə bilərəm? | Məhsulları sorğu siyahısına əlavə edin və formu göndərin — sorğu e-poçtumuza düşür və WhatsApp-da açılır. Onlayn ödəniş yoxdur, qiyməti menecer təsdiqləyir.\nAnbar harada yerləşir? | Dərnəgül, Baksol yolu. Gəlməzdən əvvəl zəng etməyiniz tövsiyə olunur.",
            'cta_title'      => 'Layihəniz üçün qiymət təklifi alın',
            'cta_text'       => 'Formu doldurun və ya birbaşa əlaqə saxlayın — menecerimiz iş günü ərzində cavab verəcək.',
            'footer_text'    => 'Global Procurement Group MMC — inşaat və izolyasiya materiallarının rəsmi distribyutoru.',
            'about_page'     => "## OSB lövhələrin topdan və pərakəndə satışı — Bakı\nGlobal Procurement Group MMC olaraq Azərbaycanda yüksək keyfiyyətli OSB lövhələrin (OSB-3) topdan və pərakəndə satışını həyata keçiririk. Biz, Rusiyada fəaliyyət göstərən Kronospan (UltraPly / UltraDekor) zavodunun Azərbaycandakı rəsmi distribyutoruyuq.\n- 9, 12, 15 və 18 mm qalınlıqlarda\n- Ölçü: 1220 × 2440 mm\n- Divar, döşəmə, dam örtüyü, arakəsmə və karkas tikintisi üçün\n## Polinor 3D — püskürtmə istilik izolyasiya materialı\nGlobal Procurement Group MMC Azərbaycanda Polinor zavodunun rəsmi distribyutorudur. Biz Polinor 3D markalı poliüretan əsaslı püskürtmə istilik izolyasiya materialının idxalını və satışını həyata keçiririk.\n- Poliüretan tərkibli — yüksək istilik izolyasiyası\n- Asan tətbiq — püskürtmə üsulu ilə\n## ISOMAX RUS — fasad izolyasiya sistemləri\nGlobal Procurement Group MMC Azərbaycanda ISOMAX RUS məhsullarının rəsmi distribyutorudur. Yüksək keyfiyyətli fasad izolyasiya materiallarının idxalını və satışını həyata keçiririk.\n- Fasad setkası 160 gr\n- Fasad izolyasiya dübeli — termo başlıqlı, dəmir mismarlı\n- Künc profili (setkalı) — fasad izolyasiyasında künclərin qorunması üçün\n- Yanğına davamlı membran — fasadlarda təhlükəsizlik və buxar nəzarəti üçün\n## WELDER — qaynaq avadanlıqları\nGlobal Procurement Group MMC Azərbaycanda WELDER brendinin rəsmi tərəfdaşı və təchizatçısıdır. PVC, HDPE, LDPE və digər hidroizolyasiya membranlarının qaynağı üçün peşəkar aparatları birbaşa idxal edirik.\n- Bütün avadanlıqlara 1 il rəsmi zəmanət\n- Bakı şəhərində peşəkar servis və texniki dəstək\n- Avadanlıq seçimi və istifadə üzrə məsləhət\n- Yalnız orijinal və sertifikatlı məhsullar",
            'seo_home_title'    => 'Global Procurement — OSB lövhələri və izolyasiya materialları, Bakı',
            'seo_home_desc'     => 'Kronospan, Polinor, ISOMAX RUS, WELDER rəsmi distribyutoru. OSB-3 lövhələri, fasad, hidro, termo və akustik izolyasiya — topdan və pərakəndə satış Bakıda.',
            'seo_shop_title'    => 'Məhsullar — Global Procurement',
            'seo_shop_desc'     => 'OSB-3 lövhələri, termo, hidro, fasad və akustik izolyasiya materialları, WELDER qaynaq avadanlıqları.',
            'seo_about_title'   => 'Haqqımızda — Global Procurement Group MMC',
            'seo_about_desc'    => 'Global Procurement Group MMC — Kronospan, Polinor, ISOMAX RUS və WELDER-in Azərbaycanda rəsmi nümayəndəsi.',
            'seo_contact_title' => 'Əlaqə — Global Procurement',
            'seo_contact_desc'  => 'Telefon, WhatsApp, e-poçt və ünvan. Qiymət sorğusu göndərin.',
        ],
        'ru' => [
            'site_name'      => 'Global Procurement',
            'address'        => 'Дярнягюль, Баксольская дорога, Баку',
            'work_hours'     => 'Пн – Сб, 09:00 – 18:00',
            'announce'       => 'Kronospan OSB-3 — 9, 12, 15, 18 мм в наличии. Специальные цены на опт',
            'hero_badge'     => 'Официальный дистрибьютор Kronospan · Polinor · ISOMAX RUS · WELDER',
            'hero_title'     => '*Надёжные* поставки строительных и изоляционных материалов',
            'hero_lead'      => 'Плиты OSB-3, фасадная, гидро-, тепло- и звукоизоляция — прямой импорт с заводов, опт и розница в Баку.',
            'trust'          => "Оригинальная продукция | сертифицировано, с завода\nПрямой импорт | цены без посредников\nОпт и розница | любой объём\nСервис в Баку | оборудование WELDER",
            'brands'         => 'KRONOSPAN, TECHNONICOL, POLYNOR, ISOMAX RUS, WELDER, PENOPLEX, SOFIT, THERMO PLUS',
            'cats_title'     => 'Что вы строите?',
            'cats_lead'      => 'Выберите категорию — и посмотрите подходящие товары.',
            'guide_title'    => 'Правильное решение для каждой зоны дома',
            'guide_lead'     => 'Выберите зону или нажмите на схему — ниже появятся рекомендуемые материалы.',
            'products_title' => 'Популярные товары',
            'products_lead'  => 'Товары, о которых спрашивают чаще всего. Весь ассортимент — в каталоге.',
            'osb_title'      => 'Какая толщина вам подходит?',
            'osb_lead'       => 'Сравнение плит Kronospan OSB-3 по областям применения и калькулятор листов.',
            'about_title'    => 'Global Procurement Group — официальный представитель зарубежных заводов',
            'about_body'     => "Global Procurement Group MMC — официальный дистрибьютор завода Kronospan (UltraPly / UltraDekor) в Азербайджане. Мы осуществляем оптовую и розничную продажу плит OSB, импортируемых напрямую с завода в России.\n\nOSB-3 — влагостойкие плиты высокого качества: толщина 9, 12, 15, 18 мм, размер 1220 × 2440 мм. Также предлагаем фасадные материалы, гидро- и теплоизоляцию и сварочное оборудование.",
            'stats'          => "4 | | официальных дистрибуции\n8 | | международных брендов\n21 | + | товаров в каталоге\n7 | | категорий товаров",
            'distributors'   => "Официальный дистрибьютор | Kronospan | Плиты OSB-3 UltraPly / UltraDekor\nОфициальный дистрибьютор | Polinor | Напыляемая полиуретановая изоляция Polinor 3D\nОфициальный дистрибьютор | ISOMAX RUS | Фасадная сетка, дюбель, угловой профиль, негорючая мембрана\nОфициальный партнёр | WELDER | Сварочное оборудование и фены для мембран",
            'welder_title'   => 'Сварочное оборудование для мембран ПВХ, HDPE, LDPE',
            'welder_text'    => 'Надёжный и долговечный сварной шов для кровельных систем, гидроизоляции фундаментов, тоннелей, резервуаров и промышленных объектов.',
            'welder_points'  => "Гарантия 1 год | на всё оборудование\nСервис в Баку | техническая поддержка\nКонсультации и обучение | подбор оборудования\nОригинал | напрямую от производителя",
            'faq'            => "Какие размеры у плит OSB-3? | Плиты Kronospan OSB-3 размером 1220 × 2440 мм, толщиной 9, 12, 15 и 18 мм.\nЕсть ли специальные цены на опт? | Да, для строительных компаний и дилеров действуют специальные условия в зависимости от объёма. Отправьте запрос — подготовим коммерческое предложение.\nКакая гарантия на оборудование WELDER? | На всё оборудование WELDER действует официальная гарантия 1 год, сервис и техподдержка — в Баку.\nКак сделать заказ? | Добавьте товары в список запроса и отправьте форму — запрос придёт к нам на почту и откроется в WhatsApp. Онлайн-оплаты нет, цену подтверждает менеджер.\nГде находится склад? | Дярнягюль, Баксольская дорога. Перед визитом рекомендуем позвонить.",
            'cta_title'      => 'Получите коммерческое предложение',
            'cta_text'       => 'Заполните форму или свяжитесь с нами напрямую — менеджер ответит в течение рабочего дня.',
            'footer_text'    => 'Global Procurement Group MMC — официальный дистрибьютор строительных и изоляционных материалов.',
            'about_page'     => "## ОСБ плиты (OSB-3) — оптовая и розничная продажа в Баку\nGlobal Procurement Group MMC — официальный дистрибьютор завода Kronospan (UltraPly / UltraDekor) в Азербайджане. Мы осуществляем оптовую и розничную продажу OSB плит напрямую с завода-производителя из России.\n- Толщина: 9 мм, 12 мм, 15 мм, 18 мм\n- Размеры: 1220 × 2440 мм\n- Подходят для стен, полов, кровли, перегородок и каркасного строительства\n- Прочные, стабильные и экологичные\n## Polinor 3D — напыляемый теплоизоляционный материал\nКомпания Global Procurement Group MMC является официальным дистрибьютором завода Polinor в Азербайджане. Мы осуществляем импорт и продажу теплоизоляции Polinor 3D — современного полиуретанового напыляемого утеплителя.\n- Полиуретановая основа — высокая теплоизоляция\n- Лёгкое нанесение: наносится распылением\n## ISOMAX RUS — фасадная изоляция и комплектующие\nКомпания Global Procurement Group MMC — официальный дистрибьютор ISOMAX RUS в Азербайджане. Мы поставляем и продаём качественные фасадные изоляционные материалы.\n- Фасадная сетка — прочная, устойчива к влаге и УФ-лучам\n- Фасадный дюбель — с термоголовкой, надёжная фиксация утеплителя\n- Угловой профиль — защита и выравнивание углов при штукатурке\n- Негорючая мембрана — для защиты фасада и пароизоляции\n## WELDER — сварочное оборудование\nКомпания Global Procurement Group MMC является официальным партнёром и поставщиком оборудования WELDER в Азербайджане. Мы напрямую импортируем профессиональные сварочные аппараты и комплектующие для сварки ПВХ, HDPE, LDPE мембран.\n- Официальная гарантия 1 год на всю линейку оборудования\n- Профессиональная сервисная поддержка в Баку\n- Консультации по подбору оборудования и обучению работе\n- Оригинальная продукция напрямую от производителя",
            'seo_home_title'    => 'Global Procurement — плиты OSB и изоляционные материалы в Баку',
            'seo_home_desc'     => 'Официальный дистрибьютор Kronospan, Polinor, ISOMAX RUS, WELDER. Плиты OSB-3, фасадная, гидро-, тепло- и звукоизоляция — опт и розница в Баку.',
            'seo_shop_title'    => 'Каталог товаров — Global Procurement',
            'seo_shop_desc'     => 'Плиты OSB-3, тепло-, гидро-, фасадная и звукоизоляция, сварочное оборудование WELDER.',
            'seo_about_title'   => 'О компании — Global Procurement Group MMC',
            'seo_about_desc'    => 'Global Procurement Group MMC — официальный представитель Kronospan, Polinor, ISOMAX RUS и WELDER в Азербайджане.',
            'seo_contact_title' => 'Контакты — Global Procurement',
            'seo_contact_desc'  => 'Телефон, WhatsApp, e-mail и адрес. Отправьте запрос цены.',
        ],
    ];
}

/** slug => [az ad, ru ad, şəkil, sıra] — slug-lar WordPress-dəki kimidir */
function seed_categories(): array {
    return [
        'kronospan-osb-lovh-l-ri' => ['OSB lövhələri', 'Плиты OSB', 'osb.jpg', 1],
        'termo-izolyasiya'        => ['Termo izolyasiya', 'Теплоизоляция', 'thermo-plus.webp', 2],
        'hidro-izolyasiya'        => ['Hidro izolyasiya', 'Гидроизоляция', 'pvc-logicbase.webp', 3],
        'fasad-m-hsullari'        => ['Fasad məhsulları', 'Фасадные материалы', 'dubel.webp', 4],
        'akustik-izolyasiya'      => ['Akustik izolyasiya', 'Звукоизоляция', 'texnoakustik.webp', 5],
        'su-v-kul-k-izolyasiya'   => ['Su və külək izolyasiya', 'Ветро-влагозащита', 'sofit-a70.jpg', 6],
        'qaynaq-apparatlari'      => ['WELDER qaynaq aparatları', 'Сварочные аппараты WELDER', 'welder-fen.jpg', 7],
    ];
}

/**
 * Məhsullar. Kateqoriya bağlantıları təxminidir — WordPress importu dəqiqləşdirir.
 * [slug, brand, image, [cat slugs], featured, badge, options,
 *   az => [title, short, specs], ru => [title, short, specs]]
 */
function seed_products(): array {
    $osb = function (string $slug, string $mm, int $sort): array {
        return [$slug, 'Kronospan', 'osb.jpg', ['kronospan-osb-lovh-l-ri'], $mm === '18' || $mm === '9' ? 1 : 0, $mm === '18' ? 'Bestseller' : '', '',
            'az' => ["OSB-3 lövhə Kronospan $mm mm", "Nəmişliyə davamlı OSB-3 lövhəsi, $mm mm. Kronospan zavodundan birbaşa idxal.", "Sinif: OSB-3\nÖlçü: 1220 × 2440 mm\nQalınlıq: $mm mm\nİstehsalçı: Kronospan (UltraPly / UltraDekor)\nÖlkə: Rusiya"],
            'ru' => ["Плита OSB-3 Kronospan $mm мм", "Влагостойкая плита OSB-3, $mm мм. Прямой импорт с завода Kronospan.", "Класс: OSB-3\nРазмер: 1220 × 2440 мм\nТолщина: $mm мм\nПроизводитель: Kronospan (UltraPly / UltraDekor)\nСтрана: Россия"],
            'sort' => $sort];
    };
    return [
        $osb('osb-lovh-plit-kronospan', '9', 1),
        $osb('osb-lovh-12-mm', '12', 2),
        $osb('osb-lovh-15-mm', '15', 3),
        $osb('osb-3-lovh-si-kronospan-9-12-15-18-mm', '18', 4),
        ['thermo-plus-das-yunu', 'Thermo Plus', 'thermo-plus.webp', ['termo-izolyasiya', 'fasad-m-hsullari'], 1, '', '',
            'az' => ['Thermo Plus daş yunu', 'Fasad və konstruksiyaların istilik izolyasiyası üçün daş yunu lövhələri.', "Növ: Daş yunu\nTətbiq: Fasad, divar"],
            'ru' => ['Каменная вата Thermo Plus', 'Плиты из каменной ваты для теплоизоляции фасадов и конструкций.', "Тип: Каменная вата\nПрименение: Фасад, стены"]],
        ['thermo-plus-vent-fasad-das-yunu', 'Thermo Plus', 'thermo-vent.webp', ['fasad-m-hsullari', 'termo-izolyasiya'], 1, '', '',
            'az' => ['Thermo Plus Vent fasad daş yunu', 'Havalandırılan fasad sistemləri üçün daş yunu.', "Növ: Daş yunu\nTətbiq: Ventilyasiyalı fasad"],
            'ru' => ['Каменная вата Thermo Plus Vent для фасада', 'Каменная вата для вентилируемых фасадных систем.', "Тип: Каменная вата\nПрименение: Вентилируемый фасад"]],
        ['texnonikol-mineral-izolyasiya', 'Technonicol', 'texnonikol-mineral.jpg', ['termo-izolyasiya'], 1, 'Yeni', '',
            'az' => ['Texnonikol mineral izolyasiya', 'Universal mineral istilik izolyasiyası — dam, divar və arakəsmələr üçün.', "Növ: Mineral yun\nTətbiq: Dam, divar, arakəsmə"],
            'ru' => ['Минеральная изоляция Технониколь', 'Универсальная минеральная теплоизоляция для кровли, стен и перегородок.', "Тип: Минеральная вата\nПрименение: Кровля, стены, перегородки"]],
        ['polynor-3d-izolyasiya-polinor', 'Polinor', 'polynor.webp', ['termo-izolyasiya'], 1, '', '',
            'az' => ['Polynor püskürtmə izolyasiya', 'Poliüretan əsaslı püskürtmə istilik izolyasiyası. Asan tətbiq, tikişsiz örtük.', "Əsas: Poliüretan\nTətbiq: Püskürtmə"],
            'ru' => ['Напыляемая изоляция Polynor', 'Напыляемая теплоизоляция на полиуретановой основе. Лёгкое нанесение, бесшовное покрытие.', "Основа: Полиуретан\nНанесение: Напыление"]],
        ['penoplex-xps-lovh-l-r', 'Penoplex', 'penoplex.webp', ['termo-izolyasiya'], 0, '', '',
            'az' => ['Penoplex XPS lövhələr', 'Ekstruziya olunmuş polistirol lövhələr — təməl, döşəmə və dam üçün.', "Növ: XPS\nTətbiq: Təməl, döşəmə, dam"],
            'ru' => ['Плиты Penoplex XPS', 'Плиты из экструдированного пенополистирола для фундамента, пола и кровли.', "Тип: XPS\nПрименение: Фундамент, пол, кровля"]],
        ['das-yunu-texnoakustik', 'Technonicol', 'texnoakustik.webp', ['akustik-izolyasiya', 'termo-izolyasiya'], 0, '', '',
            'az' => ['Daş yunu Texnoakustik', 'Arakəsmə və döşəmələrin səs izolyasiyası üçün daş yunu.', "Növ: Daş yunu\nTətbiq: Səs izolyasiyası"],
            'ru' => ['Каменная вата Техноакустик', 'Каменная вата для звукоизоляции перегородок и перекрытий.', "Тип: Каменная вата\nПрименение: Звукоизоляция"]],
        ['pvc-membran-logicbase-texnonikol', 'Technonicol', 'pvc-logicbase.webp', ['hidro-izolyasiya'], 1, '', '',
            'az' => ['PVC membran Logicbase', 'Təməl və yeraltı konstruksiyaların hidroizolyasiyası üçün PVC membran.', "Material: PVC\nTətbiq: Təməl, tunel"],
            'ru' => ['ПВХ мембрана Logicbase', 'ПВХ мембрана для гидроизоляции фундаментов и подземных конструкций.', "Материал: ПВХ\nПрименение: Фундамент, тоннель"]],
        ['bitum-praymer-18-lt', 'Technonicol', 'bitum.webp', ['hidro-izolyasiya'], 0, '', '',
            'az' => ['Bitum Praymer 18 LT', 'Bitumlu hidroizolyasiya materiallarının yapışması üçün səthin hazırlanması.', "Həcm: 18 L\nTətbiq: Səth hazırlığı"],
            'ru' => ['Битумный праймер 18 л', 'Подготовка поверхности для наплавляемых битумных гидроизоляционных материалов.', "Объём: 18 л\nПрименение: Подготовка основания"]],
        ['texnonikol-stekloizol-tol', 'Technonicol', '', ['hidro-izolyasiya'], 0, '', '',
            'az' => ['Texnonikol Stekloizol Tol', 'Dam örtüyü və təməlin hidroizolyasiyası üçün bitumlu rulon material.', "Növ: Bitumlu rulon\nTətbiq: Dam, təməl"],
            'ru' => ['Стеклоизол Технониколь', 'Битумный рулонный материал для гидроизоляции кровли и фундамента.', "Тип: Битумный рулон\nПрименение: Кровля, фундамент"]],
        ['fasad-setkasi-isomax-160-gr', 'ISOMAX RUS', 'fasad-setka.webp', ['fasad-m-hsullari'], 0, '', '',
            'az' => ['Fasad setkası İSOMAX 160 gr', 'Möhkəm, nəmə və UV şüalarına davamlı fasad armatur setkası.', "Sıxlıq: 160 g/m²\nTətbiq: Fasad suvağı"],
            'ru' => ['Фасадная сетка ISOMAX 160 г', 'Прочная армирующая фасадная сетка, устойчивая к влаге и УФ-лучам.', "Плотность: 160 г/м²\nПрименение: Фасадная штукатурка"]],
        ['fasad-izolyasiya-dubeli', 'ISOMAX RUS', 'dubel.webp', ['fasad-m-hsullari'], 0, '', '',
            'az' => ['Fasad izolyasiya dübeli', 'Termo başlıqlı, dəmir mismarlı dübel — istilik izolyasiyasının etibarlı bərkidilməsi.', "Başlıq: Termo\nMismar: Dəmir"],
            'ru' => ['Фасадный дюбель для изоляции', 'Дюбель с термоголовкой и металлическим гвоздём — надёжная фиксация утеплителя.', "Головка: Термо\nГвоздь: Металлический"]],
        ['yanmaz-membran-isomax', 'ISOMAX RUS', 'yanmaz-membran.webp', ['fasad-m-hsullari'], 0, '', '',
            'az' => ['Yanmaz membran İSOMAX', 'Fasadlarda yanğın təhlükəsizliyi və buxar nəzarəti üçün membran.', "Xüsusiyyət: Yanmaz\nTətbiq: Fasad"],
            'ru' => ['Негорючая мембрана ISOMAX', 'Мембрана для пожарной безопасности и пароизоляции фасадов.', "Свойство: Негорючая\nПрименение: Фасад"]],
        ['su-v-kul-k-izolyasiya-membrani-sofit-premium-a-70', 'SOFIT', 'sofit-a70.jpg', ['su-v-kul-k-izolyasiya'], 0, '', '',
            'az' => ['Su və külək izolyasiya membranı SOFIT PREMIUM A 70', 'Divar və damı küləkdən və nəmdən qoruyan membran.', "Növ: Su-külək\nTətbiq: Dam, divar"],
            'ru' => ['Ветро-влагозащитная мембрана SOFIT PREMIUM A 70', 'Мембрана для защиты стен и кровли от ветра и влаги.', "Тип: Ветро-влагозащита\nПрименение: Кровля, стены"]],
        ['su-v-kul-k-izolyasiya-membrani-sofit-premium-a120', 'SOFIT', '', ['su-v-kul-k-izolyasiya'], 0, '', '',
            'az' => ['Su və külək izolyasiya membranı SOFIT PREMIUM A120', 'Divar və damı küləkdən və nəmdən qoruyan gücləndirilmiş membran.', "Növ: Su-külək\nTətbiq: Dam, divar"],
            'ru' => ['Ветро-влагозащитная мембрана SOFIT PREMIUM A120', 'Усиленная мембрана для защиты стен и кровли от ветра и влаги.', "Тип: Ветро-влагозащита\nПрименение: Кровля, стены"]],
        ['buxar-izolyasiya-membrani-sofit-premium-b', 'SOFIT', 'sofit-b.jpg', ['hidro-izolyasiya'], 0, '', '',
            'az' => ['Buxar izolyasiya membranı SOFIT Premium B', 'İzolyasiyanı daxili buxardan qoruyan buxar izolyasiya membranı.', "Növ: Buxar izolyasiyası\nTətbiq: Daxili tərəf"],
            'ru' => ['Пароизоляционная мембрана SOFIT Premium B', 'Пароизоляционная мембрана для защиты утеплителя от внутреннего пара.', "Тип: Пароизоляция\nПрименение: Внутренняя сторона"]],
        ['buxar-izolyasiya-membrani-sofit-profline-b', 'SOFIT', '', ['hidro-izolyasiya'], 0, '', '',
            'az' => ['Buxar izolyasiya membranı SOFIT PROFLINE B', 'İzolyasiyanı daxili buxardan qoruyan buxar izolyasiya membranı.', "Növ: Buxar izolyasiyası\nTətbiq: Daxili tərəf"],
            'ru' => ['Пароизоляционная мембрана SOFIT PROFLINE B', 'Пароизоляционная мембрана для защиты утеплителя от внутреннего пара.', "Тип: Пароизоляция\nПрименение: Внутренняя сторона"]],
        ['welder-pes-kar-el-qaynaq-feni', 'WELDER', 'welder-fen.jpg', ['qaynaq-apparatlari'], 1, 'Zəmanət', '',
            'az' => ['WELDER — peşəkar əl qaynaq feni', 'PVC, HDPE, LDPE membranların qaynağı üçün isti hava feni. 1 il rəsmi zəmanət, servis Bakıda.', "Zəmanət: 1 il\nMembranlar: PVC / HDPE / LDPE\nServis: Bakı"],
            'ru' => ['WELDER — профессиональный ручной сварочный фен', 'Фен горячего воздуха для сварки мембран ПВХ, HDPE, LDPE. Гарантия 1 год, сервис в Баку.', "Гарантия: 1 год\nМембраны: ПВХ / HDPE / LDPE\nСервис: Баку"]],
    ];
}

/** Bütün ilkin məzmunu bazaya yazır (mövcud olanlara toxunmur) */
function cms_seed(PDO $db): array {
    $n = ['settings' => 0, 'texts' => 0, 'categories' => 0, 'products' => 0];
    $now = date('Y-m-d H:i');

    foreach (seed_settings() as $k => $v) {
        $st = $db->prepare('SELECT 1 FROM settings WHERE k=?'); $st->execute([$k]);
        if (!$st->fetch()) { $db->prepare('INSERT INTO settings (k, v) VALUES (?,?)')->execute([$k, $v]); $n['settings']++; }
    }
    foreach (seed_texts() as $lang => $rows) {
        foreach ($rows as $k => $v) {
            $st = $db->prepare('SELECT 1 FROM texts WHERE lang=? AND k=?'); $st->execute([$lang, $k]);
            if (!$st->fetch()) { $db->prepare('INSERT INTO texts (lang, k, v) VALUES (?,?,?)')->execute([$lang, $k, $v]); $n['texts']++; }
        }
    }

    $catIds = [];
    foreach (seed_categories() as $slug => [$az, $ru, $img, $sort]) {
        $st = $db->prepare('SELECT id FROM categories WHERE slug=?'); $st->execute([$slug]);
        $id = (int)($st->fetch()['id'] ?? 0);
        if (!$id) {
            $db->prepare('INSERT INTO categories (slug, image, sort, visible) VALUES (?,?,?,1)')->execute([$slug, $img, $sort]);
            $id = (int)$db->lastInsertId();
            $ins = $db->prepare('INSERT INTO category_i18n (category_id, lang, name, descr, meta_title, meta_desc) VALUES (?,?,?,?,?,?)');
            $ins->execute([$id, 'az', $az, '', '', '']);
            $ins->execute([$id, 'ru', $ru, '', '', '']);
            $n['categories']++;
        }
        $catIds[$slug] = $id;
    }

    foreach (seed_products() as $i => $p) {
        [$slug, $brand, $img, $cats, $featured, $badge, $options] = $p;
        $st = $db->prepare('SELECT id FROM products WHERE slug=?'); $st->execute([$slug]);
        if ($st->fetch()) continue;
        $sort = $p['sort'] ?? ($i + 1);
        $db->prepare('INSERT INTO products (slug, brand, image, gallery, price, unit, options, badge, sort, visible, featured, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,1,?,?,?)')
           ->execute([$slug, $brand, $img, '[]', '', '', $options, $badge, $sort, $featured, $now, $now]);
        $pid = (int)$db->lastInsertId();
        $ins = $db->prepare('INSERT INTO product_i18n (product_id, lang, title, short, body, specs, meta_title, meta_desc) VALUES (?,?,?,?,?,?,?,?)');
        foreach (['az', 'ru'] as $lang) {
            [$t, $s, $sp] = $p[$lang];
            $ins->execute([$pid, $lang, $t, $s, '', $sp, '', '']);
        }
        foreach ($cats as $c) {
            if (isset($catIds[$c])) $db->prepare('INSERT INTO product_category (product_id, category_id) VALUES (?,?)')->execute([$pid, $catIds[$c]]);
        }
        $n['products']++;
    }
    return $n;
}
