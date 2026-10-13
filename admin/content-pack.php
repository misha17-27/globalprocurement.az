<?php
/*
 * Məzmun paketi: bütün məhsullar, kateqoriyalar və səhifələr üçün SEO (AZ + RU)
 * və rus dilində təsvirlər. install.php və admin → SEO → «Применить пакет» tətbiq edir.
 *
 * Qayda: SEO sahələri (meta_title / meta_desc) yazılır; mətnlər (RU ad, qısa təsvir,
 * tam təsvir) yalnız boşdursa doldurulur — paneldə əl ilə edilən dəyişikliklər qalır.
 */
declare(strict_types=1);

function content_pack(): array
{
    $osbBodyRu = function (string $mm, string $use): string {
        return "<h2>Плита OSB-3 Kronospan {$mm} мм (UltraPly / UltraDecor)</h2>"
            . "<p>OSB-3 (Oriented Strand Board) — ориентированно-стружечная плита из крупной древесной щепы, спрессованной слоями со связующим. Класс OSB-3 отличается повышенной влагостойкостью и подходит для работы во влажной среде и на улице.</p>"
            . "<p>{$use}</p>"
            . "<h3>Характеристики</h3><ul><li>Класс: OSB-3, влагостойкая</li><li>Размер листа: 1220 × 2440 мм</li><li>Толщина: {$mm} мм (в наличии также 9, 12, 15 и 18 мм)</li><li>Соответствует стандартам EN 300 и CE</li><li>Производитель: Kronospan (UltraPly / UltraDecor), Россия</li></ul>"
            . "<h3>Преимущества</h3><ul><li>Повышенная стойкость к влаге</li><li>Высокая несущая способность и жёсткость</li><li>Точная геометрия и стабильность формы</li><li>Высокая упругость и ударная прочность</li><li>Легко обрабатывается обычным столярным инструментом</li><li>Экологически чистый материал</li></ul>"
            . "<p>Global Procurement Group MMC — официальный импортёр и дистрибьютор Kronospan в Азербайджане. Опт и розница со склада в Баку, актуальную цену уточняйте по телефону или в WhatsApp.</p>";
    };

    return [
    /* ======================= MƏHSULLAR ======================= */
    'products' => [
        'osb-lovh-plit-kronospan' => [
            'az' => ['OSB-3 lövhə 9 mm Kronospan — qiymət, Bakı | Global Procurement',
                     'Kronospan OSB-3 lövhə 9 mm, 1220×2440 mm. Nəmə davamlı, EN 300. Rəsmi distribyutordan topdan və pərakəndə satış, Bakıda anbar.'],
            'ru' => ['title' => 'Плита OSB-3 Kronospan 9 мм',
                     'short' => 'Влагостойкая плита OSB-3 толщиной 9 мм, 1220×2440 мм. Прямые поставки с завода Kronospan.',
                     'body' => $osbBodyRu('9', 'Плита 9 мм применяется для обшивки стен и перегородок, в каркасном строительстве, модульных и контейнерных домах, для отделки интерьеров в натуральном стиле и в мебельном производстве.'),
                     'meta' => ['Плита OSB-3 9 мм Kronospan — купить в Баку | Global Procurement',
                                'OSB-3 Kronospan 9 мм, 1220×2440 мм, влагостойкая, EN 300. Официальный дистрибьютор: опт и розница со склада в Баку.']],
        ],
        'osb-lovh-12-mm' => [
            'az' => ['OSB-3 lövhə 12 mm Kronospan — rəsmi satış Bakıda',
                     'OSB-3 lövhə 12 mm, 1220×2440 mm — divar, dam və karkas üçün. Kronospan UltraDecor rəsmi distribyutoru, topdan qiymətlər.'],
            'ru' => ['title' => 'Плита OSB-3 Kronospan 12 мм',
                     'short' => 'Влагостойкая плита OSB-3 толщиной 12 мм, 1220×2440 мм — для стен, кровли и каркасных домов.',
                     'body' => $osbBodyRu('12', 'Плита 12 мм — универсальная толщина для обшивки стен и кровли, перегородок, каркасных домов и чернового пола.'),
                     'meta' => ['Плита OSB-3 12 мм Kronospan — цена в Баку',
                                'OSB-3 12 мм Kronospan UltraDecor, 1220×2440 мм — для стен, кровли и каркаса. Официальный дистрибьютор, оптовые цены, склад в Баку.']],
        ],
        'osb-lovh-15-mm' => [
            'az' => ['OSB-3 lövhə 15 mm Kronospan — dam və döşəmə üçün',
                     'OSB-3 lövhə 15 mm, 1220×2440 mm — dam örtüyü altlığı və döşəmə üçün. Kronospan-dan birbaşa idxal, Bakıda topdan və pərakəndə.'],
            'ru' => ['title' => 'Плита OSB-3 Kronospan 15 мм',
                     'short' => 'Влагостойкая плита OSB-3 толщиной 15 мм, 1220×2440 мм — основание под кровлю и черновой пол.',
                     'body' => $osbBodyRu('15', 'Плита 15 мм подходит для основания под кровлю, чернового пола, несущих стен, заборов и опалубки.'),
                     'meta' => ['Плита OSB-3 15 мм Kronospan — для кровли и пола',
                                'OSB-3 15 мм, 1220×2440 мм — основание под кровлю и черновой пол. Прямой импорт Kronospan, опт и розница в Баку.']],
        ],
        'osb-3-lovh-si-kronospan-9-12-15-18-mm' => [
            'az' => ['OSB-3 lövhə 18 mm Kronospan UltraDecor — rəsmi satış',
                     'OSB-3 18 mm Kronospan — yüksək yükə və rütubətə davamlı: döşəmə, dam, karkas və modul tikililər üçün. Topdan qiymət, Bakıda anbar.'],
            'ru' => ['title' => 'Плита OSB-3 Kronospan 18 мм',
                     'short' => 'Плита OSB-3 18 мм для высоких нагрузок: пол по лагам, кровля, каркасные и модульные дома.',
                     'body' => $osbBodyRu('18', 'Плита 18 мм выдерживает большие нагрузки и влажность: пол по лагам, кровля с высокой нагрузкой, каркасные стены, модульные здания и опалубка. Высокая стойкость к давлению и изгибу.'),
                     'meta' => ['Плита OSB-3 18 мм Kronospan UltraDecor — купить в Баку',
                                'OSB-3 18 мм Kronospan для пола, кровли и каркаса — выдерживает высокие нагрузки и влагу. Официальные продажи, оптовые цены, склад в Баку.']],
        ],
        'thermo-plus-das-yunu' => [
            'az' => ['Thermo Plus Fasad Pro daş yunu — suvaqaltı fasad izolyasiyası',
                     'Thermo Plus fasad daş yunu, sıxlıq 140 kq/m³, yanmaz NG sinfi. Suvaqaltı fasad (mantolama) üçün. Rəsmi distribyutor, Bakıda topdan satış.'],
            'ru' => ['title' => 'Каменная вата Thermo Plus Fasad Pro',
                     'short' => 'Фасадная каменная вата под штукатурку, плотность 140 кг/м³, негорючая (НГ).',
                     'body' => '<h2>Thermo Plus Fasad Pro — каменная вата для штукатурного фасада</h2>'
                        . '<p>Каменная вата Thermo Plus Fasad Pro — надёжный утеплитель для тонкослойных штукатурных фасадных систем. Обеспечивает высокую тепло- и звукоизоляцию, негорюча, паропроницаема и долговечна. Защищает дом от холода, жары и шума, создавая комфортный микроклимат.</p>'
                        . '<ul><li>Экономия энергии</li><li>Тишина и комфорт</li><li>Надёжная защита фасада</li></ul>'
                        . '<h3>Применение</h3><p>Основной тепло- и звукоизоляционный слой в фасадных системах с наружным штукатурным слоем — в гражданском и промышленном строительстве.</p>'
                        . '<h3>Свойства</h3><ul><li>Плотность: 140 (±14) кг/м³</li><li>Высокая теплосберегающая способность</li><li>Стойкость к щелочам</li><li>Паропроницаемость</li><li>Негорючий материал (класс НГ)</li></ul>'
                        . '<h3>Монтаж</h3><p>Перед монтажом фасад грунтуют для укрепления основания и адгезии клея. Клей наносят на всю плиту зубчатым шпателем 10–12 мм; плиту устанавливают не позднее 20 минут после нанесения. Монтаж начинают от углов здания и проёмов, ряды укладывают со смещением швов (начиная с половины плиты).</p>'
                        . '<p>Global Procurement Group MMC — официальный дистрибьютор завода Thermo Plus в Азербайджане: прямой импорт, гарантия качества, стабильные цены и быстрая доставка.</p>',
                     'meta' => ['Каменная вата Thermo Plus Fasad Pro — утепление фасада',
                                'Фасадная каменная вата Thermo Plus под штукатурку: плотность 140 кг/м³, негорючая НГ. Официальный дистрибьютор, опт в Баку.']],
        ],
        'thermo-plus-vent-fasad-das-yunu' => [
            'az' => ['Thermo Plus Vent Pro — havalandırılan fasad üçün daş yunu',
                     'Thermo Plus Vent Pro daş yunu, sıxlıq 80 kq/m³, yanmaz (NG). Ventilyasiyalı fasad sistemləri üçün. Zavoddan birbaşa idxal, Bakıda satış.'],
            'ru' => ['title' => 'Каменная вата Thermo Plus Vent Pro',
                     'short' => 'Каменная вата для вентилируемых фасадов, плотность 80 кг/м³, негорючая (НГ).',
                     'body' => '<h2>Thermo Plus Vent Pro — плиты из каменной ваты для вентилируемых фасадов</h2>'
                        . '<h3>Применение</h3><ul><li>Основной тепло- и звукоизоляционный слой при однослойном утеплении навесных вентилируемых фасадов с воздушным зазором</li><li>Наружный слой при двухслойном утеплении вентфасадов</li><li>Тепло- и звукоизоляционный слой в трёхслойных стенах из мелкоштучных материалов</li></ul>'
                        . '<h3>Свойства</h3><ul><li>Плотность: 80 (±8) кг/м³</li><li>Высокая теплозащитная способность</li><li>Высокая паропроницаемость</li><li>Полностью негорючая (класс НГ)</li></ul>'
                        . '<p>Thermo Plus Vent Pro — энергоэффективный, безопасный и долговечный утеплитель для современных фасадных систем. Global Procurement Group MMC — официальный представитель завода Thermo Plus в Азербайджане.</p>',
                     'meta' => ['Thermo Plus Vent Pro — каменная вата для вентфасада',
                                'Каменная вата Thermo Plus Vent Pro для вентилируемых фасадов: 80 кг/м³, негорючая НГ. Прямой импорт с завода, продажа в Баку.']],
        ],
        'texnonikol-mineral-izolyasiya' => [
            'az' => ['Texnonikol mineral yun «İsti Bağ Evi» — rulon izolyasiya',
                     'TEXNONIKOL MASTER «İsti Bağ Evi» — yanmayan rulon mineral yun: dam, mansard, döşəmə və karkas divarlar üçün. Rusiyadan birbaşa idxal.'],
            'ru' => ['title' => 'Минеральная вата Технониколь «Тёплая Дача»',
                     'short' => 'Рулонная негорючая минеральная вата ТЕХНОНИКОЛЬ MASTER для частных домов и дач.',
                     'body' => '<h2>ТЕХНОНИКОЛЬ MASTER «Тёплая Дача»</h2>'
                        . '<p>Негорючий минеральный тепло- и звукоизоляционный материал в рулонах (матах). Занимает минимум места при хранении и перевозке, монтируется легко и быстро. Безопасен для здоровья и окружающей среды.</p>'
                        . '<p>Предназначен для утепления частных домов, дач и хозяйственных построек: эффективно сохраняет тепло, улучшает звукоизоляцию, не горит и устойчив к влаге. Рулонный формат заполняет конструкцию без пустот.</p>'
                        . '<h3>Применение</h3><ul><li>Скатные кровли и мансарды</li><li>Полы по деревянным лагам</li><li>Каркасные стены и перегородки</li></ul>'
                        . '<h3>Преимущества</h3><ul><li>Высокая тепло- и звукоизоляция</li><li>Негорючий материал</li><li>Экологически безопасен</li></ul>'
                        . '<p>Global Procurement Group MMC напрямую импортирует минеральную вату ТЕХНОНИКОЛЬ из России в Азербайджан: стабильные поставки, оригинальная продукция и выгодные условия.</p>',
                     'meta' => ['Минеральная вата Технониколь «Тёплая Дача» — купить в Баку',
                                'Рулонная минвата ТЕХНОНИКОЛЬ MASTER «Тёплая Дача»: негорючая, для кровли, мансарды, пола и каркасных стен. Прямой импорт, Баку.']],
        ],
        'polynor-3d-izolyasiya-polinor' => [
            'az' => ['Polynor 3D püskürtmə istilik izolyasiyası — rəsmi satış',
                     'Polynor 3D — birkomponentli poliuretan püskürtmə izolyasiya köpüyü: dam, fasad, divar və borular üçün. Polinor rəsmi distribyutoru, Bakı.'],
            'ru' => ['title' => 'Напыляемый утеплитель Polynor 3D',
                     'short' => 'Однокомпонентный полиуретановый напыляемый утеплитель — баллон со встроенной насадкой.',
                     'body' => '<h2>Polynor 3D — напыляемая теплоизоляционная пена</h2>'
                        . '<p>POLYNOR 3D — профессиональный однокомпонентный полиуретановый утеплитель с 3D-формулой для тепло- и звукоизоляции строительных элементов, зданий, трубопроводов и конструкций сложной формы. Подходит для дома, офиса, кровли, фасада и складов.</p>'
                        . '<h3>Преимущества</h3><ul><li>Распылительная насадка монтируется прямо на баллон</li><li>Термическое сопротивление R = 1,79 м²·К/Вт при слое 2 см</li><li>Наносится на бетон, дерево, металл и другие поверхности</li><li>Водо- и воздухонепроницаемый слой без швов</li><li>Производство — Россия</li></ul>'
                        . '<p>Global Procurement Group MMC — официальный дистрибьютор завода Polinor в Азербайджане. Для оптовых покупателей действуют скидки.</p>',
                     'meta' => ['Polynor 3D — напыляемый утеплитель, купить в Баку',
                                'Полиуретановый напыляемый утеплитель Polynor 3D: R=1,79 при 2 см, для кровли, фасада, стен и труб. Официальный дистрибьютор Polinor.']],
        ],
        'penoplex-xps-lovh-l-r' => [
            'az' => ['Penoplex Komfort XPS lövhələri — istilik izolyasiyası, Bakı',
                     'PENOPLEX KOMFORT XPS lövhələri 20/30/50/100 mm: divar, fasad, dam və balkon üçün. Sıfıra yaxın su udma, yüksək möhkəmlik. Rusiya istehsalı.'],
            'ru' => ['title' => 'Плиты Пеноплэкс Комфорт (XPS)',
                     'short' => 'Плиты экструдированного пенополистирола PENOPLEX KOMFORT, 20–100 мм.',
                     'body' => '<h2>ПЕНОПЛЭКС КОМФОРТ — теплоизоляционные плиты XPS</h2>'
                        . '<p>Универсальные плиты для частного домостроения из экструдированного пенополистирола (XPS). Рекомендуются для стен, фасадов, кровель и балконов. Высокая плотность и минимальное водопоглощение обеспечивают эффективную теплоизоляцию.</p>'
                        . '<h3>Свойства</h3><ul><li>Низкая теплопроводность</li><li>Практически нулевое водопоглощение</li><li>Высокая прочность на сжатие</li><li>Экологичен и долговечен</li></ul>'
                        . '<h3>Размеры</h3><ul><li>Толщина: 20 / 30 / 50 / 100 мм</li><li>Плита: 585 × 1185 мм</li><li>Площадь в упаковке: 13,86 / 9,01 / 4,85 м² (в зависимости от толщины)</li></ul>'
                        . '<p>Производство — Россия (PENOPLEX). Продажа оптом и в розницу со склада в Баку.</p>',
                     'meta' => ['Пеноплэкс Комфорт XPS — плиты утеплителя в Баку',
                                'Плиты PENOPLEX KOMFORT (XPS) 20/30/50/100 мм для стен, фасада, кровли и балкона. Почти нулевое водопоглощение. Опт и розница.']],
        ],
        'das-yunu-texnoakustik' => [
            'az' => ['Texnoakustik daş yunu 50 mm — səs izolyasiyası, Texnonikol',
                     'TEXNOAKUSTİK bazalt daş yunu plitələri 50 mm, 1200×600 mm: arakəsmə və asma tavanlar üçün səs izolyasiyası. Yanmaz. Bakıda satış.'],
            'ru' => ['title' => 'Каменная вата Техноакустик 50 мм',
                     'short' => 'Звукоизоляционные плиты из каменной ваты ТЕХНОНИКОЛЬ, 50 мм, 1200×600 мм.',
                     'body' => '<h2>ТЕХНОАКУСТИК — звукоизоляция из каменной ваты</h2>'
                        . '<p>Негорючие плиты из каменной ваты на основе базальтовых пород для внутренних изоляционных работ. Применяются как звуко- и теплоизоляция в перегородках, подвесных потолках и ненагружаемых перекрытиях.</p>'
                        . '<ul><li>Толщина: 50 мм</li><li>Размер плиты: 1200 × 600 мм</li><li>Производитель: ТЕХНОНИКОЛЬ, Россия</li></ul>',
                     'meta' => ['Техноакустик 50 мм — звукоизоляция ТЕХНОНИКОЛЬ в Баку',
                                'Плиты ТЕХНОАКУСТИК из базальтовой каменной ваты, 50 мм, 1200×600 мм — звукоизоляция перегородок и потолков. Негорючие.']],
        ],
        'pvc-membran-logicbase-texnonikol' => [
            'az' => ['Logicbase PVC membran 1.5 mm — bünövrə və dam hidroizolyasiyası',
                     'Texnonikol Logicbase PVC membranı 1.5/2.0 mm: bünövrə, zirzəmi, tunel və dam üçün. Siqnal təbəqəli, 43 m² rulon. Bakıda rəsmi satış.'],
            'ru' => ['title' => 'ПВХ мембрана LOGICBASE',
                     'short' => 'ПВХ мембрана ТЕХНОНИКОЛЬ для гидроизоляции фундаментов, подвалов, тоннелей и кровель.',
                     'body' => '<h2>LOGICBASE — ПВХ мембрана для фундамента и кровли</h2>'
                        . '<p>LOGICBASE — гидроизоляционная мембрана высокой плотности на основе ПВХ. Идеальна для защиты кровель, подвалов, тоннелей и подземных конструкций.</p>'
                        . '<h3>Характеристики</h3><ul><li>Толщина: 1,5 и 2,0 мм</li><li>Водонепроницаема, стойка к химическим воздействиям</li><li>Сваривается горячим воздухом</li><li>Рабочий диапазон: от −25 °C до +60 °C</li><li>Соответствует EN 13967 и DIN</li><li>Допускает прямой контакт с бетоном</li><li>Жёлто-чёрная мембрана с сигнальным слоем 1,5 мм, рулон 43 м²</li></ul>'
                        . '<p>Высокая стойкость к деформации и разрыву при перепадах температур. Не пропускает воду, не гниёт, эластична и экологична. Производство ТЕХНОНИКОЛЬ, Россия. Официальные продажи в Азербайджане — Global Procurement Group MMC.</p>',
                     'meta' => ['ПВХ мембрана LOGICBASE ТЕХНОНИКОЛЬ — гидроизоляция в Баку',
                                'ПВХ мембрана LOGICBASE 1,5/2,0 мм для фундамента, подвала, тоннеля и кровли. Сигнальный слой, рулон 43 м². Официальные продажи.']],
        ],
        'bitum-praymer-18-lt' => [
            'az' => ['Bitum praymer 18 L (KRZ) — hidroizolyasiya üçün astar',
                     'Bitumlu praymer 18 litr, KRZ zavodu (Rusiya). Sərfiyyat 0,25–0,35 l/m². Bitumlu tol və mastika altına səthin hazırlanması. Bakıda satış.'],
            'ru' => ['title' => 'Битумный праймер 18 л (КРЗ)',
                     'short' => 'Битумная грунтовка 18 л для подготовки основания под наплавляемую гидроизоляцию.',
                     'body' => '<h2>Битумный праймер 18 л</h2>'
                        . '<p>Битумная грунтовка производства завода КРЗ (Россия) для подготовки поверхности перед укладкой наплавляемых и битумных гидроизоляционных материалов.</p>'
                        . '<ul><li>Объём: 18 л</li><li>Расход: 0,25–0,35 л/м² в зависимости от типа поверхности</li><li>Перед применением тщательно перемешать</li></ul>',
                     'meta' => ['Битумный праймер 18 л КРЗ — купить в Баку',
                                'Битумная грунтовка (праймер) 18 л, завод КРЗ. Расход 0,25–0,35 л/м². Подготовка основания под гидроизоляцию. Продажа в Баку.']],
        ],
        'texnonikol-stekloizol-tol' => [
            'az' => ['Texnonikol Stekloizol bitumlu tol — 9 m² rulon',
                     'Texnonikol bitumlu rulon materialları: Stekloizol, Bikrost, Unifleks, Texnoelast (TPP/XPP/EPP). Dam və bünövrə hidroizolyasiyası, Bakı.'],
            'ru' => ['title' => 'Стеклоизол и битумные рулонные материалы ТЕХНОНИКОЛЬ',
                     'short' => 'Наплавляемые битумные материалы ТЕХНОНИКОЛЬ для кровли и фундамента, рулон 9 м².',
                     'body' => '<h2>Битумные рулонные материалы ТЕХНОНИКОЛЬ</h2>'
                        . '<p>Наплавляемые битумные материалы российского производства (завод ТЕХНОНИКОЛЬ) для гидроизоляции кровли и фундамента.</p>'
                        . '<ul><li>Стеклоизол 2,5 — рулон 9 м²</li><li>Стеклоизол ТПП / ХПП / ХКП / ТКП</li><li>Бикрост ТПП / ХПП / ХКП / ТКП</li><li>Унифлекс ЭПП / ТПП / ЭКП</li><li>Техноэласт ЭПП / ЭКП</li></ul>'
                        . '<p>Наличие и цену конкретной марки уточняйте у менеджера.</p>',
                     'meta' => ['Стеклоизол ТЕХНОНИКОЛЬ — битумный рубероид в Баку',
                                'Битумные рулонные материалы ТЕХНОНИКОЛЬ: Стеклоизол, Бикрост, Унифлекс, Техноэласт. Гидроизоляция кровли и фундамента.']],
        ],
        'fasad-setkasi-isomax-160-gr' => [
            'az' => ['Fasad setkası 160 q/m² ISOMAX — armatur şəbəkə, 50 m²',
                     'ISOMAX RUS fasad armatur setkası 160 q/m², rulon 50 m². Fasad izolyasiya sistemində suvağın möhkəmliyi üçün. Rəsmi distribyutor, Bakı.'],
            'ru' => ['title' => 'Фасадная сетка ISOMAX 160 г/м²',
                     'short' => 'Армирующая фасадная сетка 160 г/м², рулон 50 м², производство ISOMAX RUS.',
                     'body' => '<h2>Фасадная сетка 160 г/м² — ISOMAX RUS</h2>'
                        . '<ul><li>Плотность: 160 г/м²</li><li>Рулон: 50 м²</li><li>Производство: Россия</li></ul>'
                        . '<p>Армирующая сетка применяется в системах утепления фасадов. Укладывается поверх утеплителя перед нанесением штукатурки и обеспечивает прочность штукатурного слоя. Устойчива к влаге и щелочам.</p>',
                     'meta' => ['Фасадная сетка 160 г/м² ISOMAX — купить в Баку',
                                'Армирующая фасадная сетка ISOMAX RUS 160 г/м², рулон 50 м² — для штукатурных систем утепления фасада. Официальный дистрибьютор.']],
        ],
        'fasad-izolyasiya-dubeli' => [
            'az' => ['Fasad izolyasiya dübeli ISOMAX 115/155 mm — termobaşlıqlı',
                     'ISOMAX termobaşlıqlı fasad dübeli, polad mismarlı, 115 və 155 mm. Daş yunu, XPS, EPS bərkidilməsi üçün. Topdan satış, rəsmi distribyutor.'],
            'ru' => ['title' => 'Фасадный дюбель ISOMAX с термоголовкой',
                     'short' => 'Тарельчатый дюбель для утеплителя со стальным гвоздём и термоголовкой, 115 и 155 мм.',
                     'body' => '<h2>Фасадный дюбель для утеплителя — ISOMAX</h2>'
                        . '<p>Фасадные дюбели ISOMAX — надёжное крепление систем теплоизоляции. Пластиковый дюбель первого класса со стальным гвоздём и термоголовкой крепит утеплитель (XPS, EPS, каменную вату) к несущему основанию из кирпича, бетона и газобетона.</p>'
                        . '<h3>Характеристики</h3><ul><li>Длина: 115 и 155 мм (по запросу — до 200 мм)</li><li>Стальной гвоздь, термоголовка</li><li>Подходит для EPS, XPS и каменной ваты</li><li>Стойкость к УФ и перепадам температур</li><li>Производство: ISOMAX, Россия</li></ul>'
                        . '<p>Global Procurement Group MMC — официальный дистрибьютор ISOMAX в Азербайджане. Оптовые поставки.</p>',
                     'meta' => ['Фасадный дюбель ISOMAX 115/155 мм — купить оптом в Баку',
                                'Дюбель для утеплителя ISOMAX с термоголовкой и стальным гвоздём, 115 и 155 мм. Для каменной ваты, XPS, EPS. Опт, Баку.']],
        ],
        'yanmaz-membran-isomax' => [
            'az' => ['ISOMAX yanmaz membran — su və külək izolyasiyası, 75 m²',
                     'ISOMAX RUS yanmaz külək-nəm qoruyucu membran, rulon 75 m², ГОСТ 30244-94. Havalandırılan fasad və dam üçün. Rəsmi distribyutor, Bakı.'],
            'ru' => ['title' => 'Негорючая ветро-влагозащитная мембрана ISOMAX',
                     'short' => 'Негорючая мембрана для вентфасадов и кровель, рулон 75 м², ГОСТ 30244-94.',
                     'body' => '<h2>Негорючая мембрана ISOMAX RUS</h2>'
                        . '<ul><li>Рулон: 75 м²</li><li>Негорючая, ГОСТ 30244-94</li><li>Производство: Россия</li></ul>'
                        . '<p>Ветро-влагозащитная мембрана европейского уровня качества. Защищает утеплитель в системах вентилируемых фасадов и кровель от дождя, УФ-излучения, влаги и ветра, а также препятствует возникновению и распространению огня. Очень прочная и долговечная, монтируется поверх утеплителя.</p>'
                        . '<p>Применяется для утепления всех типов кровель, фасадов и каркасных стен.</p>',
                     'meta' => ['Негорючая мембрана ISOMAX — ветрозащита фасада, Баку',
                                'Негорючая ветро-влагозащитная мембрана ISOMAX RUS, рулон 75 м², ГОСТ 30244-94 — для вентфасадов и кровель. Официальный дистрибьютор.']],
        ],
        'su-v-kul-k-izolyasiya-membrani-sofit-premium-a-70' => [
            'az' => ['SOFIT Premium A70 — su və külək izolyasiya membranı',
                     'SOFIT Premium A70 külək-nəm qoruyucu membran: eni 1.6 m, rulon 70 m². Maili dam və divarlarda izolyasiyanı nəmdən və küləkdən qoruyur.'],
            'ru' => ['title' => 'Ветро-влагозащитная мембрана SOFIT Premium A70',
                     'short' => 'Мембрана для защиты утеплителя от влаги и ветра, ширина 1,6 м, рулон 70 м².',
                     'body' => '<h2>SOFIT Premium A70</h2><ul><li>Ширина: 1,6 м</li><li>Рулон: 70 м²</li></ul>'
                        . '<p>Надёжно защищает утеплитель от внешней влаги, предотвращает потери тепла и продлевает срок службы конструкции. Применяется в скатных кровлях и стеновых конструкциях для защиты утеплителя от конденсата, влаги, ветра и осадков.</p>'
                        . '<h3>Свойства</h3><ul><li>Сохраняет теплозащитные свойства утеплителя</li><li>Оптимальные размеры и вес</li><li>Продлевает срок службы конструкции</li><li>Выводит водяной пар из утеплителя</li></ul>',
                     'meta' => ['SOFIT Premium A70 — ветро-влагозащитная мембрана в Баку',
                                'Мембрана SOFIT Premium A70: ширина 1,6 м, рулон 70 м². Защищает утеплитель в кровле и стенах от влаги, ветра и конденсата.']],
        ],
        'su-v-kul-k-izolyasiya-membrani-sofit-premium-a120' => [
            'az' => ['SOFIT Premium A120 — gücləndirilmiş külək-nəm membranı',
                     'SOFIT Premium A120 su və külək izolyasiya membranı: eni 1.6 m, rulon 70 m². Dam və divar konstruksiyalarında izolyasiyanın qorunması.'],
            'ru' => ['title' => 'Ветро-влагозащитная мембрана SOFIT Premium A120',
                     'short' => 'Усиленная ветро-влагозащитная мембрана, ширина 1,6 м, рулон 70 м².',
                     'body' => '<h2>SOFIT Premium A120</h2><ul><li>Ширина: 1,6 м</li><li>Рулон: 70 м²</li></ul>'
                        . '<p>Усиленная мембрана надёжно защищает утеплитель от внешней влаги, предотвращает потери тепла и обеспечивает долговечность конструкции. Применяется в скатных кровлях и стенах для защиты утеплителя от конденсата, влаги, ветра и дождя.</p>'
                        . '<h3>Свойства</h3><ul><li>Сохраняет теплозащитные свойства утеплителя</li><li>Оптимальные размеры и вес</li><li>Продлевает срок службы конструкции</li><li>Выводит водяной пар из утеплителя</li></ul>',
                     'meta' => ['SOFIT Premium A120 — усиленная ветрозащитная мембрана',
                                'Ветро-влагозащитная мембрана SOFIT Premium A120: ширина 1,6 м, рулон 70 м². Для кровли и стен. Продажа в Баку.']],
        ],
        'buxar-izolyasiya-membrani-sofit-premium-b' => [
            'az' => ['SOFIT Premium B — buxar izolyasiya membranı, 70 m²',
                     'SOFIT Premium B ikiqatlı buxar izolyasiya membranı, 1.6×43.75 m. Mineral yun, XPS ilə birlikdə kondensat və çürümənin qarşısını alır.'],
            'ru' => ['title' => 'Пароизоляционная мембрана SOFIT Premium B',
                     'short' => 'Двухслойная пароизоляция 1,6×43,75 м (70 м²) для утеплённых конструкций.',
                     'body' => '<h2>SOFIT Premium B — пароизоляция</h2><ul><li>Размер: 1,6 × 43,75 м</li><li>Рулон: 70 м²</li></ul>'
                        . '<p>Двухслойная пароизоляционная плёнка для разных типов конструкций. Применяется вместе с утеплителями (минеральная вата, XPS, пенопласт) и предотвращает образование конденсата и гниение.</p>'
                        . '<h3>Преимущества</h3><ul><li>Надёжный пароизоляционный барьер</li><li>Экологически чистая</li><li>Долгий срок службы</li></ul>',
                     'meta' => ['Пароизоляция SOFIT Premium B — купить в Баку',
                                'Двухслойная пароизоляционная мембрана SOFIT Premium B, 1,6×43,75 м (70 м²). Защищает утеплитель от конденсата.']],
        ],
        'buxar-izolyasiya-membrani-sofit-profline-b' => [
            'az' => ['SOFIT Profline B — buxar izolyasiya membranı',
                     'SOFIT Profline B buxar izolyasiya membranı, 1.6×43.75 m (70 m²). İstilik izolyasiyasını kondensatdan qoruyur. Bakıda topdan və pərakəndə.'],
            'ru' => ['title' => 'Пароизоляционная мембрана SOFIT Profline B',
                     'short' => 'Пароизоляционная плёнка 1,6×43,75 м (70 м²) для защиты утеплителя.',
                     'body' => '<h2>SOFIT Profline B — пароизоляция</h2><ul><li>Размер: 1,6 × 43,75 м</li><li>Рулон: 70 м²</li></ul>'
                        . '<p>Двухслойная пароизоляционная плёнка для разных типов конструкций. Используется вместе с минеральной ватой, XPS, пенопластом и другими утеплителями, предотвращает конденсат и гниение.</p>'
                        . '<h3>Преимущества</h3><ul><li>Надёжный пароизоляционный барьер</li><li>Экологически чистая</li><li>Долгий срок службы</li></ul>',
                     'meta' => ['Пароизоляция SOFIT Profline B — цена в Баку',
                                'Пароизоляционная мембрана SOFIT Profline B, 1,6×43,75 м (70 м²) — защита утеплителя от конденсата. Опт и розница в Баку.']],
        ],
        'welder-pes-kar-el-qaynaq-feni' => [
            'az' => ['WELDER əl qaynaq feni — PVC membran qaynağı, 1 il zəmanət',
                     'WELDER peşəkar isti hava feni PVC membranların qaynağı üçün. Rəsmi distribyutor: 1 il zəmanət, Bakıda servis, orijinal ehtiyat hissələri.'],
            'ru' => ['title' => 'WELDER — профессиональный ручной сварочный фен',
                     'short' => 'Ручной фен горячего воздуха для сварки ПВХ мембран. Гарантия 1 год, сервис в Баку.',
                     'body' => '<h2>WELDER — профессиональный ручной фен для сварки ПВХ мембран</h2>'
                        . '<p>Global Procurement Group MMC — официальный дистрибьютор бренда WELDER в Азербайджане. WELDER — ручной профессиональный фен горячего воздуха для сварки ПВХ мембран, идеальный инструмент для работ по мембранной гидроизоляции.</p>'
                        . '<h3>Что вы получаете</h3><ul><li>Стабильный и мощный поток горячего воздуха</li><li>Простое управление и регулировка температуры</li><li>Высокая производительность и долгий срок службы</li><li>Гибкое применение со сменными насадками</li><li>Качество сварки промышленного уровня</li></ul>'
                        . '<h3>Почему мы</h3><ul><li>Официальные продажи и права импортёра</li><li>Профессиональный сервис и оригинальные запчасти</li><li>Гарантия и техническая поддержка</li><li>Быстрая доставка по Баку и регионам</li><li>Специальные оптовые цены для мастеров и компаний</li></ul>',
                     'meta' => ['Фен WELDER для сварки ПВХ мембран — гарантия 1 год, Баку',
                                'Ручной сварочный фен WELDER для ПВХ мембран. Официальный дистрибьютор: гарантия 1 год, сервис в Баку, оригинальные запчасти.']],
        ],
    ],

    /* ======================= KATEQORİYALAR ======================= */
    'categories' => [
        'kronospan-osb-lovh-l-ri' => [
            'az' => ['name' => 'Kronospan OSB lövhələri',
                     'descr' => "Kronospan (UltraDecor / UltraPly) zavodunun rəsmi distribyutoru olaraq Azərbaycanda OSB-3 lövhələrinin topdan və pərakəndə satışını həyata keçiririk. Rusiya istehsalı nəmə davamlı OSB-3 plitələri 9, 12, 15 və 18 mm qalınlıqda, 1220×2440 mm ölçüdədir.\n\nOSB-3 lövhələr karkas evlərdə, dam örtüklərində, döşəmələrdə və arakəsmələrdə geniş istifadə olunur. Bakıda (Dərnəgül, Baksol yolu) anbarımızdan sabit təchizat, rəqabətli qiymətlər və ölkə üzrə sürətli çatdırılma təklif edirik.",
                     'meta' => ['OSB-3 lövhələri Kronospan — topdan və pərakəndə, Bakı', 'Kronospan OSB-3 lövhələri 9/12/15/18 mm, 1220×2440 mm. Rəsmi distribyutor, Bakıda anbar, topdan qiymətlər və sürətli çatdırılma.']],
            'ru' => ['name' => 'Плиты OSB Kronospan',
                     'descr' => "Global Procurement Group — официальный дистрибьютор завода Kronospan (UltraDecor / UltraPly) в Азербайджане. Продаём влагостойкие плиты OSB-3 российского производства оптом и в розницу: толщина 9, 12, 15 и 18 мм, размер 1220×2440 мм.\n\nПлиты OSB-3 широко применяются в каркасных домах, кровельных системах, полах и перегородках. Склад в Баку (Дярнягюль, Баксольская дорога), стабильные поставки, конкурентные цены и быстрая доставка по стране.",
                     'meta' => ['Плиты OSB-3 Kronospan — купить оптом и в розницу в Баку', 'Плиты OSB-3 Kronospan 9/12/15/18 мм, 1220×2440 мм. Официальный дистрибьютор, склад в Баку, оптовые цены и быстрая доставка.']],
        ],
        'termo-izolyasiya' => [
            'az' => ['name' => 'Termo izolyasiya',
                     'descr' => "Fasad, dam, divar və döşəmələrin istilik izolyasiyası üçün materiallar: Thermo Plus daş yunu, Texnonikol mineral yunu, Penoplex XPS lövhələri və Polynor 3D püskürtmə izolyasiyası. Bütün məhsullar zavodlardan birbaşa idxal olunur.",
                     'meta' => ['İstilik izolyasiyası materialları — daş yunu, XPS, Polynor', 'Daş yunu Thermo Plus, Texnonikol mineral yun, Penoplex XPS, Polynor 3D — istilik izolyasiyası materialları Bakıda topdan və pərakəndə.']],
            'ru' => ['name' => 'Теплоизоляция',
                     'descr' => "Материалы для утепления фасадов, кровель, стен и полов: каменная вата Thermo Plus, минеральная вата ТЕХНОНИКОЛЬ, плиты Пеноплэкс XPS и напыляемый утеплитель Polynor 3D. Вся продукция — прямые поставки с заводов.",
                     'meta' => ['Теплоизоляция — каменная вата, XPS, Polynor в Баку', 'Каменная вата Thermo Plus, минвата ТЕХНОНИКОЛЬ, Пеноплэкс XPS, Polynor 3D — утеплители оптом и в розницу в Баку.']],
        ],
        'hidro-izolyasiya' => [
            'az' => ['name' => 'Hidro izolyasiya',
                     'descr' => "Bünövrə, zirzəmi, tunel və damların hidroizolyasiyası: Logicbase PVC membranları, bitumlu tollar (Stekloizol, Bikrost, Texnoelast), bitum praymer, buxar izolyasiya membranları və WELDER qaynaq avadanlıqları.",
                     'meta' => ['Hidroizolyasiya materialları — PVC membran, bitum, Bakı', 'PVC membran Logicbase, bitumlu tol Stekloizol, bitum praymer və buxar izolyasiyası — bünövrə və dam hidroizolyasiyası üçün.']],
            'ru' => ['name' => 'Гидроизоляция',
                     'descr' => "Гидроизоляция фундаментов, подвалов, тоннелей и кровель: ПВХ мембраны LOGICBASE, наплавляемые битумные материалы (Стеклоизол, Бикрост, Техноэласт), битумный праймер, пароизоляционные мембраны и сварочное оборудование WELDER.",
                     'meta' => ['Гидроизоляционные материалы — ПВХ мембраны, битум, Баку', 'ПВХ мембраны LOGICBASE, Стеклоизол, битумный праймер и пароизоляция — для фундамента и кровли. Продажа в Баку.']],
        ],
        'fasad-m-hsullari' => [
            'az' => ['name' => 'Fasad məhsulları',
                     'descr' => "Fasad istilik izolyasiyası sistemləri üçün hər şey: Thermo Plus fasad daş yunu, ISOMAX armatur setkası 160 q/m², termobaşlıqlı fasad dübelləri və yanmaz külək-nəm qoruyucu membranlar. ISOMAX RUS-un rəsmi distribyutoruyuq.",
                     'meta' => ['Fasad izolyasiya materialları — daş yunu, setka, dübel', 'Fasad daş yunu Thermo Plus, ISOMAX fasad setkası, dübel və yanmaz membran — mantolama və ventilyasiyalı fasad üçün, Bakı.']],
            'ru' => ['name' => 'Фасадные материалы',
                     'descr' => "Всё для систем утепления фасадов: фасадная каменная вата Thermo Plus, армирующая сетка ISOMAX 160 г/м², дюбели с термоголовкой и негорючие ветро-влагозащитные мембраны. Мы — официальный дистрибьютор ISOMAX RUS.",
                     'meta' => ['Материалы для утепления фасада — вата, сетка, дюбель', 'Фасадная каменная вата Thermo Plus, сетка и дюбели ISOMAX, негорючая мембрана — для штукатурных и вентилируемых фасадов.']],
        ],
        'akustik-izolyasiya' => [
            'az' => ['name' => 'Akustik izolyasiya',
                     'descr' => "Arakəsmə, tavan və döşəmələrin səs izolyasiyası üçün materiallar: Texnoakustik bazalt daş yunu plitələri və buxar izolyasiya membranları.",
                     'meta' => ['Səs izolyasiyası materialları — Texnoakustik, Bakı', 'Texnoakustik daş yunu plitələri və membranlar — arakəsmə, tavan və döşəmələrin səs izolyasiyası üçün. Bakıda satış.']],
            'ru' => ['name' => 'Звукоизоляция',
                     'descr' => "Материалы для звукоизоляции перегородок, потолков и перекрытий: плиты ТЕХНОАКУСТИК из базальтовой каменной ваты и пароизоляционные мембраны.",
                     'meta' => ['Звукоизоляционные материалы — Техноакустик, Баку', 'Плиты ТЕХНОАКУСТИК и мембраны для звукоизоляции перегородок, потолков и перекрытий. Продажа в Баку.']],
        ],
        'su-v-kul-k-izolyasiya' => [
            'az' => ['name' => 'Su və külək izolyasiya',
                     'descr' => "SOFIT Premium külək-nəm qoruyucu membranları maili dam və divar konstruksiyalarında istilik izolyasiyasını nəmdən, küləkdən və kondensatdan qoruyur, buxarın çıxmasını təmin edir.",
                     'meta' => ['Külək-nəm qoruyucu membranlar SOFIT — Bakı', 'SOFIT Premium A70 və A120 su və külək izolyasiya membranları — dam və divarlarda izolyasiyanın qorunması üçün. Bakıda satış.']],
            'ru' => ['name' => 'Ветро-влагозащита',
                     'descr' => "Ветро-влагозащитные мембраны SOFIT Premium защищают утеплитель в скатных кровлях и стенах от влаги, ветра и конденсата и выводят из него водяной пар.",
                     'meta' => ['Ветро-влагозащитные мембраны SOFIT — Баку', 'Мембраны SOFIT Premium A70 и A120 для защиты утеплителя в кровле и стенах от влаги и ветра. Продажа в Баку.']],
        ],
        'qaynaq-apparatlari' => [
            'az' => ['name' => 'WELDER qaynaq aparatları',
                     'descr' => "Global Procurement Group MMC Azərbaycanda WELDER brendinin rəsmi tərəfdaşıdır. PVC, HDPE, LDPE membranlarının qaynağı üçün avtomatik aparatlar, isti hava fenləri, test alətləri və aksesuarlar — 1 il rəsmi zəmanət və Bakıda servis.",
                     'meta' => ['WELDER qaynaq aparatları — PVC membran qaynağı, Bakı', 'WELDER avtomatik qaynaq aparatları və isti hava fenləri PVC, HDPE, LDPE membranlar üçün. 1 il zəmanət, Bakıda servis.']],
            'ru' => ['name' => 'Сварочные аппараты WELDER',
                     'descr' => "Global Procurement Group MMC — официальный партнёр WELDER в Азербайджане. Автоматические аппараты, фены горячего воздуха, тестовое оборудование и аксессуары для сварки мембран ПВХ, HDPE, LDPE — гарантия 1 год и сервис в Баку.",
                     'meta' => ['Сварочное оборудование WELDER для мембран — Баку', 'Аппараты и фены WELDER для сварки ПВХ, HDPE, LDPE мембран. Официальный партнёр: гарантия 1 год, сервис в Баку.']],
        ],
    ],

    /* ======================= SƏHİFƏLƏR ======================= */
    'pages' => [
        'haqqimizda' => [
            'az' => ['lead' => 'Azərbaycanda Kronospan, Polinor, ISOMAX RUS və WELDER-in rəsmi nümayəndəsi — inşaat və izolyasiya materiallarının topdan və pərakəndə satışı.',
                     'meta' => ['Haqqımızda — Global Procurement Group MMC', 'Global Procurement Group MMC — Kronospan, Polinor, ISOMAX RUS və WELDER-in Azərbaycanda rəsmi distribyutoru. OSB, izolyasiya, fasad materialları.']],
            'ru' => ['lead' => 'Официальный представитель Kronospan, Polinor, ISOMAX RUS и WELDER в Азербайджане — оптовая и розничная продажа строительных и изоляционных материалов.',
                     'meta' => ['О компании — Global Procurement Group MMC', 'Global Procurement Group MMC — официальный дистрибьютор Kronospan, Polinor, ISOMAX RUS и WELDER в Азербайджане. OSB, изоляция, фасад.']],
        ],
        'welder-qaynag-avadanliqlari' => [
            'az' => ['lead' => 'PVC / HDPE / LDPE və digər membranların qaynağı üçün peşəkar avadanlıqlar — 1 il zəmanət, Bakıda servis.',
                     'meta' => ['WELDER qaynaq avadanlıqları — rəsmi tərəfdaş Azərbaycanda', 'PVC, HDPE, LDPE membranların qaynağı üçün WELDER aparatları və isti hava fenləri. 1 il rəsmi zəmanət, Bakıda servis və məsləhət.']],
            'ru' => ['lead' => 'Профессиональное оборудование для сварки мембран ПВХ / HDPE / LDPE — гарантия 1 год, сервис в Баку.',
                     'meta' => ['Сварочное оборудование WELDER — официальный партнёр', 'Аппараты и фены горячего воздуха WELDER для сварки мембран ПВХ, HDPE, LDPE. Официальная гарантия 1 год, сервис и консультации в Баку.']],
        ],
        'sirket-haqqinda' => [
            'az' => ['lead' => 'Azərbaycanın aparıcı inşaat və izolyasiya materialları təchizatçılarından biri: brendlər, xidmətlər və əməkdaşlıq.',
                     'meta' => ['Şirkət haqqında — tərəfdaş brendlər və xidmətlər', 'Global Procurement Group: Kronospan, ISOMAX, Polynor, Sofit, TechnoNicol, Thermo Plus, WELDER tərəfdaşı. Fasad və dam izolyasiya xidmətləri.']],
            'ru' => ['lead' => 'Один из ведущих поставщиков строительных и изоляционных материалов в Азербайджане: бренды, услуги и сотрудничество.',
                     'meta' => ['О компании подробнее — бренды-партнёры и услуги', 'Global Procurement Group — партнёр Kronospan, ISOMAX, Polynor, Sofit, ТЕХНОНИКОЛЬ, Thermo Plus, WELDER. Утепление фасадов и гидроизоляция кровель.']],
        ],
    ],

    /* ======================= SƏHİFƏ SEO (texts) ======================= */
    'texts' => [
        'az' => [
            'seo_home_title'    => 'OSB lövhələri və izolyasiya materialları Bakıda | Global Procurement',
            'seo_home_desc'     => 'Kronospan, Polinor, ISOMAX RUS, WELDER rəsmi distribyutoru. OSB-3 lövhələri, daş yunu, XPS, PVC membran, fasad materialları — topdan və pərakəndə.',
            'seo_shop_title'    => 'Kataloq — OSB, izolyasiya və fasad materialları | Global Procurement',
            'seo_shop_desc'     => 'OSB-3 lövhələri, termo, hidro, fasad və akustik izolyasiya, külək-nəm membranları, WELDER qaynaq avadanlıqları. Bakıda anbar, topdan qiymət.',
            'seo_about_title'   => 'Haqqımızda — Global Procurement Group MMC',
            'seo_about_desc'    => 'Global Procurement Group MMC — Kronospan, Polinor, ISOMAX RUS və WELDER-in Azərbaycanda rəsmi distribyutoru. OSB, izolyasiya, fasad materialları.',
            'seo_contact_title' => 'Əlaqə — Global Procurement, Bakı, Dərnəgül',
            'seo_contact_desc'  => 'Telefon +994 51 304 00 43, WhatsApp, e-poçt. Anbar: Dərnəgül, Baksol yolu, Bakı. Qiymət sorğusu göndərin — iş günü ərzində cavab.',
        ],
        'ru' => [
            'seo_home_title'    => 'Плиты OSB и изоляционные материалы в Баку | Global Procurement',
            'seo_home_desc'     => 'Официальный дистрибьютор Kronospan, Polinor, ISOMAX RUS, WELDER. Плиты OSB-3, каменная вата, XPS, ПВХ мембраны, фасадные материалы — опт и розница.',
            'seo_shop_title'    => 'Каталог — OSB, изоляция и фасадные материалы | Global Procurement',
            'seo_shop_desc'     => 'Плиты OSB-3, тепло-, гидро-, звукоизоляция, фасадные материалы, ветрозащитные мембраны и сварочное оборудование WELDER. Склад в Баку.',
            'seo_about_title'   => 'О компании — Global Procurement Group MMC',
            'seo_about_desc'    => 'Global Procurement Group MMC — официальный дистрибьютор Kronospan, Polinor, ISOMAX RUS и WELDER в Азербайджане. OSB, изоляция, фасад.',
            'seo_contact_title' => 'Контакты — Global Procurement, Баку, Дярнягюль',
            'seo_contact_desc'  => 'Телефон +994 51 304 00 43, WhatsApp, e-mail. Склад: Дярнягюль, Баксольская дорога, Баку. Отправьте запрос цены — ответим в течение дня.',
        ],
    ],
    ];
}

/** Paketi bazaya tətbiq edir. Qaytarır: [sahə => say] */
function cms_apply_pack(PDO $db): array
{
    $pack = content_pack();
    $n = ['products' => 0, 'categories' => 0, 'pages' => 0, 'texts' => 0];

    $row = function (string $table, string $fk, int $id, string $lang) use ($db): ?array {
        $st = $db->prepare("SELECT * FROM $table WHERE $fk = ? AND lang = ?"); $st->execute([$id, $lang]);
        return $st->fetch() ?: null;
    };
    $save = function (string $table, string $fk, int $id, string $lang, array $set) use ($db, $row): void {
        if (!$set) return;
        if ($row($table, $fk, $id, $lang)) {
            $sql = implode(', ', array_map(function ($k) { return "`$k` = ?"; }, array_keys($set)));
            $db->prepare("UPDATE $table SET $sql WHERE $fk = ? AND lang = ?")->execute([...array_values($set), $id, $lang]);
        } else {
            $set[$fk] = $id; $set['lang'] = $lang;
            $db->prepare("INSERT INTO $table (`" . implode('`,`', array_keys($set)) . '`) VALUES (' . implode(',', array_fill(0, count($set), '?')) . ')')->execute(array_values($set));
        }
    };
    $empty = function (?array $r, string $k, int $min = 1): bool { return !$r || mb_strlen(trim(strip_tags((string)($r[$k] ?? '')))) < $min; };

    foreach ($pack['products'] as $slug => $p) {
        $st = $db->prepare('SELECT id FROM products WHERE slug = ?'); $st->execute([$slug]);
        if (!($id = (int)($st->fetch()['id'] ?? 0))) continue;
        [$mt, $md] = $p['az'];
        $save('product_i18n', 'product_id', $id, 'az', ['meta_title' => $mt, 'meta_desc' => $md]);
        $ru = $p['ru']; $r = $row('product_i18n', 'product_id', $id, 'ru');
        $set = ['meta_title' => $ru['meta'][0], 'meta_desc' => $ru['meta'][1]];
        if ($empty($r, 'title')) $set['title'] = $ru['title'];
        if ($empty($r, 'short')) $set['short'] = $ru['short'];
        /* boşdursa və ya WordPress-dən gələn RU mətnə AZ hissələri qarışıbsa — paketdəki tərcümə */
        if ($empty($r, 'body', 200) || preg_match('/[əğıƏĞİ]/u', strip_tags((string)($r['body'] ?? '')))) $set['body'] = $ru['body'];
        $save('product_i18n', 'product_id', $id, 'ru', $set);
        $n['products']++;
    }
    foreach ($pack['categories'] as $slug => $c) {
        $st = $db->prepare('SELECT id FROM categories WHERE slug = ?'); $st->execute([$slug]);
        if (!($id = (int)($st->fetch()['id'] ?? 0))) continue;
        foreach (['az', 'ru'] as $l) {
            $r = $row('category_i18n', 'category_id', $id, $l);
            $set = ['meta_title' => $c[$l]['meta'][0], 'meta_desc' => $c[$l]['meta'][1]];
            if ($empty($r, 'name')) $set['name'] = $c[$l]['name'];
            /* WordPress-dən gələn təsvir AZ və RU qarışıqdır — paketdəki təmiz mətnlə əvəz edirik,
               paneldə əl ilə yazılmış (kiril/latın qarışığı olmayan) təsvirə toxunmuruq */
            $cur = trim((string)($r['descr'] ?? ''));
            $mixed = $cur !== '' && preg_match('/\p{Cyrillic}/u', $cur) && preg_match('/[əğışöüçƏĞİŞÖÜÇ]/u', $cur);
            if ($cur === '' || $mixed) $set['descr'] = $c[$l]['descr'];
            $save('category_i18n', 'category_id', $id, $l, $set);
        }
        $n['categories']++;
    }
    foreach ($pack['pages'] as $slug => $pg) {
        $st = $db->prepare('SELECT id FROM pages WHERE slug = ?'); $st->execute([$slug]);
        if (!($id = (int)($st->fetch()['id'] ?? 0))) continue;
        foreach (['az', 'ru'] as $l) {
            $r = $row('page_i18n', 'page_id', $id, $l);
            $set = ['meta_title' => $pg[$l]['meta'][0], 'meta_desc' => $pg[$l]['meta'][1]];
            if ($empty($r, 'lead')) $set['lead'] = $pg[$l]['lead'];
            $save('page_i18n', 'page_id', $id, $l, $set);
        }
        $n['pages']++;
    }
    foreach ($pack['texts'] as $l => $rows) {
        foreach ($rows as $k => $v) {
            $st = $db->prepare('SELECT 1 FROM texts WHERE lang = ? AND k = ?'); $st->execute([$l, $k]);
            if ($st->fetch()) $db->prepare('UPDATE texts SET v = ? WHERE lang = ? AND k = ?')->execute([$v, $l, $k]);
            else $db->prepare('INSERT INTO texts (lang, k, v) VALUES (?,?,?)')->execute([$l, $k, $v]);
            $n['texts']++;
        }
    }
    return $n;
}
