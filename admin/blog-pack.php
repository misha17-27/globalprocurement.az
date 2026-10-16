<?php
/*
 * Bloqun başlanğıc məqalələri (AZ + RU, SEO ilə). includes/blog.php → blog_ensure() bloq boş olanda
 * bir dəfə yazır; sonra hər şey admin → «Блог»-da redaktə olunur.
 */
declare(strict_types=1);

function blog_pack_insert(PDO $d): int {
    /* /blog/ siyahısının SEO-su (admin → SEO → «Блог»); artıq yazılıbsa toxunulmur */
    $seo = [
        'az' => ['seo_blog_title' => 'Bloq: OSB lövhə və izolyasiya seçimi — Bakı',
                 'seo_blog_desc'  => 'OSB-3 lövhə, daş yunu, XPS və hidroizolyasiya materiallarının seçimi üzrə məsləhətlər. Bakıda topdan və pərakəndə qiymət üçün sorğu göndərin.'],
        'ru' => ['seo_blog_title' => 'Блог: выбор плит OSB и утеплителя — Баку',
                 'seo_blog_desc'  => 'Советы по выбору плит OSB-3, каменной ваты, XPS и гидроизоляции. Оптовые и розничные цены в Баку — отправьте запрос или напишите в WhatsApp.'],
    ];
    foreach ($seo as $l => $kv) foreach ($kv as $k => $v) {
        try {
            $st = $d->prepare('SELECT 1 FROM texts WHERE lang = ? AND k = ?'); $st->execute([$l, $k]);
            if (!$st->fetch()) $d->prepare('INSERT INTO texts (lang, k, v) VALUES (?,?,?)')->execute([$l, $k, $v]);
        } catch (Throwable $e) {}
    }

    $n = 0;
    foreach (blog_pack_data() as $p) {
        try {
            $d->beginTransaction();
            $d->prepare('INSERT INTO posts (slug, image, products, published_at, visible, updated_at) VALUES (?,?,?,?,1,?)')
              ->execute([$p['slug'], $p['image'], json_encode(array_values($p['products'])), $p['date'], date('Y-m-d H:i')]);
            $id = (int)$d->lastInsertId();
            foreach (['az', 'ru'] as $l) {
                $t = $p[$l];
                $d->prepare('INSERT INTO post_i18n (post_id, lang, title, `lead`, body, meta_title, meta_desc) VALUES (?,?,?,?,?,?,?)')
                  ->execute([$id, $l, $t['title'], $t['lead'], sanitize_html($t['body']), $t['meta_title'], $t['meta_desc']]);
            }
            $d->commit();
            $n++;
        } catch (Throwable $e) {                     // məs. eyni slug artıq var (paralel sorğu) — həmin məqalə ötürülür
            if ($d->inTransaction()) $d->rollBack();
        }
    }
    return $n;
}

function blog_pack_data(): array {
    return [
        [
            'slug' => 'osb-3-lovh-qalinliq-secimi',
            'image' => 'products/osb-3-lovh-si-kronospan-9-12-15-18-mm-c395c7.jpg',
            'products' => ['osb-lovh-plit-kronospan', 'osb-lovh-12-mm', 'osb-lovh-15-mm', 'osb-3-lovh-si-kronospan-9-12-15-18-mm'],
            'date' => '2026-10-12',
            'az' => [
                'title' => 'OSB-3 lövhəsinin qalınlığını necə seçməli: 9, 12, 15 və ya 18 mm',
                'lead' => 'Döşəmə, dam, divar, karkas ev və beton qəlibi üçün Kronospan OSB-3 lövhəsinin hansı qalınlığı lazımdır? 9, 12, 15 və 18 mm arasındakı fərq və Bakıda topdan alış şərtləri.',
                'meta_title' => 'OSB lövhə qalınlığı və qiyməti — Kronospan OSB-3, Bakı',
                'meta_desc' => 'OSB lövhə: döşəmə, dam, divar və qəlib üçün hansı qalınlıq lazımdır? Kronospan rəsmi distribyutoru, Bakıda anbar, topdan şərtlər. Qiymət sorğusu göndərin.',
                'body' => '<p>OSB lövhə alarkən çoxları ilk növbədə qiyməti soruşur, amma işə qalınlığı düzgün seçməkdən başlamaq lazımdır. Nazik lövhə döşəmədə əyiləcək, lazım olduğundan qalın lövhə isə büdcəni boş yerə artıracaq. Bu yazıda Kronospan OSB-3 lövhələrinin 9, 12, 15 və 18 mm qalınlıqlarının harada istifadə olunduğunu, OSB-nin fanera və yonqar lövhədən (DSP) fərqini və Bakıda OSB lövhəni rəsmi distribyutordan almağın niyə sərfəli olduğunu izah edirik.</p>
<h2>OSB-3 nədir və niyə məhz bu sinif?</h2>
<p>OSB (Oriented Strand Board) iri taxta yonqarlarının qatlarla müəyyən istiqamətdə düzülərək bağlayıcı ilə preslənməsindən alınan lövhədir. Yonqarların istiqamətli düzülüşü lövhəyə yüksək möhkəmlik və sərtlik verir. OSB-3 sinfi isə nəmə qarşı artırılmış davamlılığı ilə seçilir və rütubətli mühitdə, eləcə də açıq havada aparılan işlərdə istifadə üçün uyğundur.</p>
<p>Təklif etdiyimiz Kronospan (UltraPly / UltraDekor) OSB-3 lövhələrinin əsas göstəriciləri:</p>
<ul>
<li>lövhənin ölçüsü: 1220 × 2440 mm;</li>
<li>qalınlıq: 9, 12, 15 və 18 mm;</li>
<li>EN 300 standartına uyğunluq və CE nişanı;</li>
<li>yüksək daşıma qabiliyyəti, dəqiq həndəsi ölçülər və forma sabitliyi;</li>
<li>adi ağac emalı alətləri ilə asanlıqla kəsilir və bərkidilir.</li>
</ul>
<h2>OSB lövhə qalınlığı: 9, 12, 15 və ya 18 mm?</h2>
<h3>9 mm — divar üzlüyü və arakəsmələr</h3>
<p><a href="/product/osb-lovh-plit-kronospan/">OSB lövhə 9 mm</a> ən yüngül variantdır. Divar və arakəsmələrin üzlənməsində, karkas tikintisində, modul və konteyner evlərdə, interyerin təbii üslubda bəzədilməsində və mebel istehsalında istifadə olunur. Üzərinə ciddi yük düşməyən səthlər üçün optimal seçimdir.</p>
<h3>12 mm — universal qalınlıq</h3>
<p><a href="/product/osb-lovh-12-mm/">OSB lövhə 12 mm</a> divar və dam üzlüyü, arakəsmələr, karkas evlər və kobud döşəmə üçün uyğundur. Yük orta səviyyədədirsə və hansı qalınlığı götürəcəyinizdən əmin deyilsinizsə, 12 mm balanslı seçimdir.</p>
<h3>15 mm — dam əsası, döşəmə və qəlib</h3>
<p><a href="/product/osb-lovh-15-mm/">OSB lövhə 15 mm</a> dam örtüyünün altında əsas, kobud döşəmə, yükdaşıyan divarlar, hasarlar və beton qəlibi (opalubka) üçün istifadə olunur. Yumşaq dam örtüyü (bitumlu kirəmit) altında bütöv və möhkəm əsas yaratmaq üçün yaxşı variantdır.</p>
<h3>18 mm — yüksək yüklər üçün</h3>
<p><a href="/product/osb-3-lovh-si-kronospan-9-12-15-18-mm/">OSB lövhə 18 mm</a> böyük yüklərə və rütubətə dözümlüdür, təzyiqə və əyilməyə qarşı yüksək müqavimət göstərir. Tirlər (laqlar) üzərində döşəmə, yüksək yüklü dam, karkas divarlar, modul tikililər və beton qəlibi üçün nəzərdə tutulub.</p>
<h2>Tətbiq sahəsinə görə qısa bələdçi</h2>
<ul>
<li><strong>Döşəmə:</strong> tirlər (laqlar) üzərində 15–18 mm; tirlər arasındakı məsafə nə qədər böyükdürsə, lövhə də bir o qədər qalın olmalıdır. Hazır beton əsasın üzərində düzləndirici qat üçün adətən 9–12 mm kifayət edir.</li>
<li><strong>Dam:</strong> 12–18 mm; örtük nə qədər ağır və yük nə qədər böyükdürsə, lövhə də bir o qədər qalın seçilir.</li>
<li><strong>Divar və arakəsmələr:</strong> 9–12 mm; yüksək yüklü divarlar üçün 15 mm.</li>
<li><strong>Karkas və modul evlər:</strong> divarlar üçün 9–12 mm, dam üçün 12–18 mm, döşəmə üçün 15–18 mm.</li>
<li><strong>Beton qəlibi:</strong> 15–18 mm; tökülən betonun hündürlüyü artdıqca daha qalın lövhə götürün və dayaqlar ilə bərkidicilər arasındakı məsafəni azaldın — qəlibin möhkəmliyini təkcə lövhənin qalınlığı təmin etmir.</li>
</ul>
<p>Bir lövhənin (1220 × 2440 mm) sahəsi təxminən 2,98 m²-dir. Lazım olan lövhə sayını hesablamaq üçün səthin sahəsini bu ədədə bölün və kəsim üçün kiçik ehtiyat əlavə edin (adətən 5–10%). Quraşdırma zamanı lövhələr arasında kiçik deformasiya boşluğu saxlayın, kəsilmiş kənarları isə nəmdən qoruyun. Karkas evdə istilik izolyasiyasını küləkdən və nəmdən qorumaq üçün <a href="/product-category/su-v-kul-k-izolyasiya/">su və külək izolyasiya membranlarından</a> da istifadə olunur.</p>
<h2>OSB-3, fanera və yonqar lövhə (DSP): fərq nədir?</h2>
<ul>
<li><strong>Yonqar lövhə (DSP)</strong> xırda yonqarlardan hazırlanır və əsasən mebel və quru daxili işlər üçün nəzərdə tutulub. Əyilməyə zəif müqavimət göstərir və nəmdən şişməyə meyllidir, ona görə də dam və döşəmə üçün tövsiyə edilmir.</li>
<li><strong>Fanera</strong> nazik şpon təbəqələrinin yapışdırılması ilə alınır, möhkəmdir, lakin eyni formatda adətən daha baha başa gəlir.</li>
<li><strong>OSB-3</strong> iri istiqamətli yonqarlar sayəsində möhkəmliyi və sərtliyi, artırılmış nəmə davamlılığını və adi ağac emalı alətləri ilə asan emalı birləşdirir. Bu, tikinti üçün qiymət və keyfiyyət arasında ağlabatan balans deməkdir.</li>
</ul>
<p>Məhz buna görə OSB-3 karkas tikintisində, dam örtüyünün altında və döşəmələrdə geniş istifadə olunur. Yenə də unutmayın: OSB-3 nəmə davamlıdır, amma dam örtüyü və ya fasad üzlüyü çəkilənə qədər lövhəni uzun müddət yağış altında açıq saxlamaq tövsiyə olunmur.</p>
<h2>Niyə OSB lövhəni Bakıda rəsmi distribyutordan almaq sərfəlidir?</h2>
<p>Global Procurement Group MMC Azərbaycanda Kronospan (UltraPly / UltraDekor) zavodunun rəsmi distribyutorudur. Lövhələr Rusiyadakı zavoddan birbaşa idxal olunur və bu, alıcıya bir sıra üstünlüklər verir:</p>
<ul>
<li><strong>orijinal məhsul</strong> — sertifikatlı, birbaşa zavoddan;</li>
<li><strong>vasitəçisiz qiymət</strong> — birbaşa idxal sayəsində;</li>
<li><strong>topdan və pərakəndə satış</strong> — istənilən həcmdə;</li>
<li><strong>Bakıda anbar</strong> — Dərnəgül, Baksol yolu (gəlməzdən əvvəl zəng etməyiniz tövsiyə olunur).</li>
</ul>
<h3>İnşaatçılar üçün topdan şərtlər</h3>
<p>Tikinti şirkətləri, podratçılar və dilerlər üçün həcmə görə xüsusi şərtlər mövcuddur. Lazım olan qalınlıqları və lövhə sayını göndərin — menecer kommersiya təklifi hazırlayacaq. Obyekt üçün bir neçə qalınlıq lazımdırsa (məsələn, divarlar üçün 12 mm, döşəmə üçün 18 mm), hamısını sorğu siyahısına əlavə edib bir sorğu ilə göndərə bilərsiniz. Onlayn ödəniş yoxdur, qiyməti menecer təsdiqləyir.</p>
<p>Bütün çeşid «<a href="/product-category/kronospan-osb-lovh-l-ri/">Kronospan OSB lövhələri</a>» bölməsindədir. OSB lövhə qiymətini və topdan şərtləri öyrənmək üçün saytda sorğu göndərin və ya WhatsApp-da bizə yazın. Aşağıda dörd qalınlığın hamısı qiymətləri ilə göstərilib.</p>',
            ],
            'ru' => [
                'title' => 'Как выбрать толщину плиты OSB-3: 9, 12, 15 или 18 мм',
                'lead' => 'Какая толщина плиты Kronospan OSB-3 нужна для пола, кровли, стен, каркасного дома и опалубки? Разбираем 9, 12, 15 и 18 мм и условия покупки оптом в Баку.',
                'meta_title' => 'OSB плита в Баку: какую толщину выбрать, цены и опт',
                'meta_desc' => 'Какая толщина OSB-3 нужна для пола, кровли, стен и опалубки? Официальный дистрибьютор Kronospan, склад в Баку, опт. Отправьте запрос на цену.',
                'body' => '<p>При покупке OSB плиты первым делом обычно спрашивают цену, но начинать стоит с толщины. Слишком тонкий лист прогнётся на полу, а слишком толстый — это лишние расходы без пользы. Разбираемся, где применять плиты Kronospan OSB-3 толщиной 9, 12, 15 и 18 мм, чем OSB отличается от фанеры и ДСП и почему OSB плиту в Баку выгодно покупать у официального дистрибьютора.</p>
<h2>Что такое OSB-3 и почему именно этот класс</h2>
<p>OSB (Oriented Strand Board, ОСБ) — ориентированно-стружечная плита из крупной древесной щепы, которую укладывают слоями в заданном направлении и прессуют со связующим. Ориентация щепы даёт плите высокую прочность и жёсткость. Класс OSB-3 отличается повышенной влагостойкостью и подходит для применения во влажной среде и для наружных работ.</p>
<p>Основные характеристики плит Kronospan (UltraPly / UltraDekor), которые мы поставляем:</p>
<ul>
<li>размер листа: 1220 × 2440 мм;</li>
<li>толщина: 9, 12, 15 и 18 мм;</li>
<li>соответствие стандарту EN 300, маркировка CE;</li>
<li>высокая несущая способность, точная геометрия и стабильность формы;</li>
<li>легко режется и крепится обычным столярным инструментом.</li>
</ul>
<h2>Какую толщину OSB выбрать: 9, 12, 15 или 18 мм</h2>
<h3>9 мм — обшивка стен и перегородок</h3>
<p><a href="/ru/product/osb-lovh-plit-kronospan/">Плита OSB-3 9 мм</a> — самый лёгкий вариант. Её используют для обшивки стен и перегородок, в каркасном строительстве, модульных и контейнерных домах, для отделки интерьеров в натуральном стиле и в мебельном производстве. Оптимальна там, где на лист не приходится серьёзной нагрузки.</p>
<h3>12 мм — универсальная толщина</h3>
<p><a href="/ru/product/osb-lovh-12-mm/">Плита OSB-3 12 мм</a> подходит для обшивки стен и кровли, перегородок, каркасных домов и чернового пола. Если нагрузка средняя и вы сомневаетесь, какой лист взять, 12 мм — сбалансированный выбор.</p>
<h3>15 мм — основание кровли, пол и опалубка</h3>
<p><a href="/ru/product/osb-lovh-15-mm/">Плита OSB-3 15 мм</a> применяется для основания под кровлю, чернового пола, несущих стен, заборов и опалубки. Хороший вариант, когда нужно сплошное жёсткое основание под мягкую кровлю (битумную черепицу).</p>
<h3>18 мм — для высоких нагрузок</h3>
<p><a href="/ru/product/osb-3-lovh-si-kronospan-9-12-15-18-mm/">Плита OSB-3 18 мм</a> выдерживает большие нагрузки и влажность, хорошо сопротивляется давлению и изгибу. Её выбирают для пола по лагам, кровли с высокой нагрузкой, каркасных стен, модульных зданий и опалубки.</p>
<h2>Шпаргалка: толщина OSB по области применения</h2>
<ul>
<li><strong>Пол:</strong> по лагам — 15–18 мм; чем больше расстояние между лагами, тем толще должна быть плита. Для выравнивания поверх готового бетонного основания обычно достаточно 9–12 мм.</li>
<li><strong>Кровля:</strong> 12–18 мм; чем тяжелее покрытие и выше нагрузка, тем толще лист.</li>
<li><strong>Стены и перегородки:</strong> 9–12 мм; для стен с повышенной нагрузкой — 15 мм.</li>
<li><strong>Каркасные и модульные дома:</strong> стены — 9–12 мм, кровля — 12–18 мм, пол — 15–18 мм.</li>
<li><strong>Опалубка:</strong> 15–18 мм; чем выше слой заливаемого бетона, тем толще щит и тем чаще должны стоять опоры и стяжки — прочность опалубки обеспечивает не только толщина плиты.</li>
</ul>
<p>Площадь одного листа 1220 × 2440 мм — около 2,98 м². Чтобы посчитать нужное количество листов, разделите площадь поверхности на это значение и добавьте небольшой запас на подрезку (обычно 5–10%). При монтаже оставляйте между листами небольшой компенсационный зазор, а срезы защищайте от влаги. В каркасном доме утеплитель дополнительно защищают <a href="/ru/product-category/su-v-kul-k-izolyasiya/">ветро-влагозащитными мембранами</a>.</p>
<h2>OSB-3, фанера и ДСП: в чём разница</h2>
<ul>
<li><strong>ДСП</strong> делают из мелкой стружки, это материал в основном для мебели и сухих внутренних работ. Она хуже работает на изгиб и склонна разбухать от влаги, поэтому для кровли и пола её не рекомендуют.</li>
<li><strong>Фанера</strong> склеивается из слоёв шпона, она прочная, но при том же формате обычно обходится дороже.</li>
<li><strong>OSB-3</strong> благодаря крупной ориентированной щепе сочетает прочность и жёсткость, повышенную влагостойкость и лёгкость обработки обычным столярным инструментом. Для стройки это разумный баланс цены и качества.</li>
</ul>
<p>Именно поэтому OSB-3 так широко используют в каркасном строительстве, под кровлю и на полы. И всё же помните: OSB-3 влагостойкая, но надолго оставлять плиту под дождём без кровельного покрытия или фасадной отделки не рекомендуется.</p>
<h2>Почему OSB плиты в Баку выгодно покупать у официального дистрибьютора</h2>
<p>Global Procurement Group MMC — официальный дистрибьютор завода Kronospan (UltraPly / UltraDekor) в Азербайджане. Плиты импортируются напрямую с завода в России, и это даёт покупателю ряд преимуществ:</p>
<ul>
<li><strong>оригинальная продукция</strong> — сертифицированная, с завода;</li>
<li><strong>цены без посредников</strong> — благодаря прямому импорту;</li>
<li><strong>опт и розница</strong> — любой объём;</li>
<li><strong>склад в Баку</strong> — Дярнягюль, Баксольская дорога (перед визитом рекомендуем позвонить).</li>
</ul>
<h3>Оптовые условия для строителей</h3>
<p>Для строительных компаний, подрядчиков и дилеров действуют специальные условия в зависимости от объёма. Пришлите список нужных толщин и количество листов, и менеджер подготовит коммерческое предложение. Если для объекта нужно несколько толщин, например 12 мм на стены и 18 мм на пол, добавьте их все в список и отправьте одним запросом. Онлайн-оплаты нет, цену подтверждает менеджер.</p>
<p>Весь ассортимент собран в разделе «<a href="/ru/product-category/kronospan-osb-lovh-l-ri/">Плиты OSB</a>». Чтобы узнать актуальную цену OSB плиты в Баку и оптовые условия, отправьте запрос на сайте или напишите нам в WhatsApp. Ниже показаны все четыре толщины с ценами.</p>',
            ],
        ],
        [
            'slug' => 'evin-istilik-izolyasiyasi-das-yunu-xps-secimi',
            'image' => 'products/thermo-plus-das-yunu-d7d0fc.webp',
            'products' => ['thermo-plus-das-yunu', 'thermo-plus-vent-fasad-das-yunu', 'penoplex-xps-lovh-l-r', 'texnonikol-mineral-izolyasiya', 'polynor-3d-izolyasiya-polinor', 'das-yunu-texnoakustik', 'fasad-setkasi-isomax-160-gr', 'fasad-izolyasiya-dubeli'],
            'date' => '2026-10-14',
            'az' => [
                'title' => 'Evin istilik izolyasiyası: daş yunu, XPS, yoxsa püskürtmə izolyasiya?',
                'lead' => 'Fasad, dam, döşəmə və təməl üçün fərqli izolyasiya lazımdır. Thermo Plus və Texnonikol daş yunu, Penoplex XPS lövhələri və Polynor 3D arasında seçimi necə etmək lazım olduğunu izah edirik.',
                'meta_title' => 'Daş yunu və XPS qiyməti Bakıda — evin istilik izolyasiyası',
                'meta_desc' => 'Daş yunu və XPS: fasad, dam, döşəmə və təməl üçün hansı izolyasiyanı seçməli. Thermo Plus, Penoplex, Polynor — Bakıda topdan qiymət, sorğu göndərin.',
                'body' => '<p>Azərbaycanda evin istilik izolyasiyası iki işi birlikdə görür: qışda istiliyi içəridə saxlayır, yayda isə istinin evə keçməsinin qarşısını alır. Bakının küləkli qışında və isti yayında düzgün seçilmiş izolyasiya isitmə və kondisioner xərclərini azaldır. Amma universal material yoxdur: fasad, dam, döşəmə və təməl fərqli şəraitdə işləyir və hər birinə öz istilik izolyasiya materialı lazımdır. Bu yazıda daş yunu, XPS lövhələri və püskürtmə izolyasiya arasında seçimi necə etmək lazım olduğunu izah edirik.</p><h2>Daş yunu və mineral yun: fasad və dam üçün yanmaz izolyasiya</h2><p>Daş yunu bazalt kimi vulkanik süxurlardan hazırlanır. O, yanmır və buxarı yaxşı keçirir, yəni divar «nəfəs alır» və nəm izolyasiyanın içində yığılıb qalmır. Üstəlik, daş yunu səsi yaxşı udur. Kataloqumuzda bu qrupdan müxtəlif işlər üçün dörd məhsul var: üç daş yunu lövhəsi və Texnonikol mineral yun rulonu.</p><h3>Thermo Plus Fasad Pro — suvaqlı fasad üçün</h3><p><a href="/product/thermo-plus-das-yunu/">Thermo Plus daş yunu</a> (Fasad Pro) nazik suvaqlı fasad sistemləri üçün nəzərdə tutulub. Sıxlığı 140 (±14) kq/m³ olan sərt lövhələr yapışqan və suvaq qatını etibarlı şəkildə saxlayır. Material NG sinfinə aiddir, yəni yanmazdır, həm də qələvilərə davamlıdır və buxar keçirir. Kərpic, beton və ya blok divarlı evin suvaqlı fasadı üçün ən məntiqli seçim budur.</p><h3>Thermo Plus Vent Pro — havalandırılan fasad üçün</h3><p>Fasad keramoqranit və ya kompozit panellərlə üzlənirsə və arxada hava boşluğu qalırsa, <a href="/product/thermo-plus-vent-fasad-das-yunu/">Thermo Plus Vent fasad daş yunu</a> seçin. Onun sıxlığı 80 (±8) kq/m³-dir. Lövhələr birqat izolyasiyada əsas qat, ikiqat izolyasiyada isə xarici qat kimi işləyir, kərpic və blokdan hörülən üçqat divarlarda da istifadə olunur. İzolyasiyanın üstündən onu küləkdən və nəmdən qoruyan membran çəkilir, məsələn, ISOMAX yanmaz membranı.</p><h3>Texnonikol mineral izolyasiya — dam və karkas üçün</h3><p><a href="/product/texnonikol-mineral-izolyasiya/">Texnonikol mineral izolyasiya</a> (MASTER «İsti Bağ Evi») rulon şəklində satılır. Maili damlar və mansardlar, taxta laglar üzərində döşəmələr, karkas divarlar və arakəsmələr üçün uyğundur. Rulon konstruksiyanı boşluqsuz doldurur və daşınma zamanı az yer tutur. Dam və karkasda izolyasiyanın xarici tərəfinə su və külək izolyasiya membranı (SOFIT Premium A70 və ya A120), daxili tərəfinə isə buxar izolyasiya membranı (SOFIT Premium B) qoyulur ki, kondensat yaranmasın.</p><h3>Texnoakustik — səs izolyasiyası üçün</h3><p>Mənzildə qonşuların səsindən qorunmaq istəyirsinizsə, <a href="/product/das-yunu-texnoakustik/">Texnoakustik daş yunu lövhələrinə</a> baxın: qalınlıq 50 mm, ölçü 1200 × 600 mm. Onlar arakəsmələrdə, asma tavanlarda və yük daşımayan örtüklərdə səs və istilik izolyasiyası kimi istifadə olunur.</p><h2>Penoplex XPS lövhələri: təməl, döşəmə və sokol</h2><p><a href="/product/penoplex-xps-lovh-l-r/">Penoplex XPS lövhələri</a> ekstruziya olunmuş polistiroldan hazırlanır. Onların əsas üstünlükləri sıfıra yaxın su udma, yüksək sıxılma möhkəmliyi və aşağı istilik keçiriciliyidir. Buna görə XPS nəm və yük olan yerlərdə istifadə olunur:</p><ul><li>təməlin və zirzəmi divarlarının xaricdən izolyasiyası;</li><li>fasadın torpağa yaxın sokol hissəsi;</li><li>beton döşəmə və şap altı izolyasiya;</li><li>düz damlar və balkonlar.</li></ul><p>Lövhənin ölçüsü 585 × 1185 mm, qalınlığı 20, 30, 50 və 100 mm-dir. Daş yunundan fərqli olaraq XPS buxarı çox az keçirir. Ona görə kərpic və ya blok divarlı evdə fasadın əsas hissəsi çox vaxt daş yunu ilə, sokol isə XPS ilə izolyasiya edilir.</p><h2>Polynor 3D: çətin yerlər üçün püskürtmə izolyasiya</h2><p>Bəzi yerlərə lövhə və ya rulon qoymaq çətindir: metal konstruksiyalar, borular, dam altındakı künclər, mürəkkəb formalı səthlər. Belə hallarda <a href="/product/polynor-3d-izolyasiya-polinor/">Polynor püskürtmə izolyasiyası</a> köməyə gəlir. Polynor 3D birkomponentli poliuretan köpükdür və balona birbaşa taxılan ucluqla tətbiq olunur. O, beton, taxta və metal səthlərə yapışır, tikişsiz örtük yaradır, su və hava keçirmir. Material həm istilik, həm də səs izolyasiyası üçün nəzərdə tutulub. Global Procurement Azərbaycanda Polinor zavodunun rəsmi distribyutorudur.</p><h2>Suvaqlı fasad sistemi: daş yunundan başqa nə lazımdır</h2><p>İzolyasiya lövhəsi sistemin yalnız bir hissəsidir. Suvaqlı fasadın uzun illər çatlamadan qalması üçün bütün qatlar düzgün quraşdırılmalıdır:</p><ol><li><strong>Astar.</strong> Divar astarlanır ki, səth möhkəmlənsin və yapışqan yaxşı tutsun.</li><li><strong>Yapışqan və lövhə.</strong> Yapışqan bütün lövhəyə 10–12 mm dişli şpatellə çəkilir, lövhə yapışqan çəkildikdən sonra ən geci 20 dəqiqə ərzində yerinə qoyulur. Montaj binanın künclərindən, pəncərə və qapı boşluqlarından başlayır.</li><li><strong>Fasad dübelləri.</strong> ISOMAX dübelləri termobaşlıqlı və polad mismarlıdır, uzunluğu 115 və 155 mm-dir (sifarişlə 200 mm-ə qədər). Daş yunu, XPS və EPS üçün uyğundur; kərpic, beton və qazbetonda möhkəm tutur. Uzunluq izolyasiyanın qalınlığına və divara girmə dərinliyinə görə seçilir.</li><li><strong>Armatur setkası.</strong> ISOMAX 160 q/m² fasad setkası izolyasiyanın üstündə suvaq qatına yerləşdirilir və suvağa möhkəmlik verir. Setka nəmə və qələvilərə davamlıdır, rulonu 50 m²-dir. Künclər setkalı künc profili ilə qorunur.</li><li><strong>Suvaq.</strong> Əsas qat və dekorativ son qat.</li></ol><p>Setka, dübel və digər köməkçi materialları <a href="/product-category/fasad-m-hsullari/">fasad məhsulları</a> bölməsində tapa bilərsiniz. Global Procurement Azərbaycanda ISOMAX RUS məhsullarının rəsmi distribyutorudur.</p><h2>Qısaca: hansı zona üçün hansı material</h2><ul><li><strong>Suvaqlı fasad:</strong> Thermo Plus Fasad Pro, ISOMAX setkası və dübelləri.</li><li><strong>Havalandırılan fasad:</strong> Thermo Plus Vent Pro və yanmaz membran.</li><li><strong>Maili dam və mansard:</strong> Texnonikol mineral izolyasiya və SOFIT membranları.</li><li><strong>Döşəmə:</strong> laglar üzərində mineral yun, beton döşəmədə və şap altında Penoplex XPS.</li><li><strong>Təməl və sokol:</strong> Penoplex XPS.</li><li><strong>Arakəsmə və tavan:</strong> Texnoakustik.</li><li><strong>Mürəkkəb səthlər və metal:</strong> Polynor 3D.</li></ul><p>Bakıda daş yunu, XPS və fasad materiallarının aktual qiymətlərini məqalənin altındakı məhsul kartlarında görə bilərsiniz. Lazım olan materialları sorğu siyahısına əlavə edib sorğu formasını göndərin və ya WhatsApp-da bizə yazın: menecer qiyməti təsdiqləyəcək və topdan alış üçün həcmə görə xüsusi şərtləri təklif edəcək. Anbarımız Bakıda, Dərnəgül, Baksol yolunda yerləşir.</p>',
            ],
            'ru' => [
                'title' => 'Утепление дома: каменная вата, XPS или напыляемая изоляция?',
                'lead' => 'Фасаду, кровле, полу и фундаменту нужен разный утеплитель. Разбираем, когда выбрать каменную вату Thermo Plus и ТЕХНОНИКОЛЬ, плиты Penoplex XPS или напыляемый Polynor 3D.',
                'meta_title' => 'Купить утеплитель в Баку: каменная вата и XPS для дома',
                'meta_desc' => 'Какой утеплитель выбрать для фасада, кровли, пола и фундамента: каменная вата Thermo Plus, Penoplex XPS, Polynor. Оптовые цены в Баку — по запросу.',
                'body' => '<p>Утепление дома в Азербайджане решает сразу две задачи: зимой сохраняет тепло, а летом не пускает жару внутрь. В условиях ветреной бакинской зимы и жаркого лета правильно подобранный утеплитель снижает расходы на отопление и кондиционирование. Но универсального материала не существует: фасад, кровля, пол и фундамент работают в разных условиях, и каждой зоне нужен свой теплоизоляционный материал. Разбираемся, как выбрать между каменной ватой, плитами XPS и напыляемой изоляцией.</p><h2>Каменная и минеральная вата: негорючий утеплитель для фасада и кровли</h2><p>Каменную вату делают из вулканических пород, например базальта. Она не горит и хорошо пропускает пар: стена «дышит», и влага не скапливается внутри утеплителя. Кроме того, вата хорошо гасит шум. В нашем каталоге четыре материала из этой группы под разные задачи: три вида каменной ваты и рулонная минеральная изоляция ТЕХНОНИКОЛЬ.</p><h3>Thermo Plus Fasad Pro — для штукатурного фасада</h3><p><a href="/ru/product/thermo-plus-das-yunu/">Каменная вата Thermo Plus</a> (Fasad Pro) предназначена для тонкослойных штукатурных фасадных систем. Жёсткие плиты плотностью 140 (±14) кг/м³ надёжно держат клей и штукатурный слой. Материал негорючий (класс НГ), стойкий к щелочам и паропроницаемый. Для дома с кирпичными, бетонными или блочными стенами это самый логичный выбор под штукатурный фасад.</p><h3>Thermo Plus Vent Pro — для вентилируемого фасада</h3><p>Если фасад облицовывают керамогранитом или композитными панелями с воздушным зазором, берите <a href="/ru/product/thermo-plus-vent-fasad-das-yunu/">каменную вату Thermo Plus Vent</a>. Её плотность — 80 (±8) кг/м³. Плиты служат основным слоем при однослойном утеплении и наружным слоем при двухслойном, а также подходят для трёхслойных стен из кирпича и блоков. Поверх утеплителя монтируют ветро-влагозащитную мембрану, например негорючую мембрану ISOMAX.</p><h3>Минеральная изоляция ТЕХНОНИКОЛЬ — для кровли и каркаса</h3><p><a href="/ru/product/texnonikol-mineral-izolyasiya/">Минеральная изоляция ТЕХНОНИКОЛЬ</a> (MASTER «Тёплая Дача») выпускается в рулонах. Она подходит для скатных кровель и мансард, полов по деревянным лагам, каркасных стен и перегородок. Рулон заполняет конструкцию без пустот и занимает мало места при перевозке. В кровле и каркасе снаружи утеплителя укладывают ветро-влагозащитную мембрану (SOFIT Premium A70 или A120), а изнутри — пароизоляцию (SOFIT Premium B), чтобы не образовывался конденсат.</p><h3>Техноакустик — для звукоизоляции</h3><p>Если в квартире мешает шум от соседей, обратите внимание на <a href="/ru/product/das-yunu-texnoakustik/">плиты каменной ваты Техноакустик</a>: толщина 50 мм, размер 1200 × 600 мм. Их применяют как звуко- и теплоизоляцию в перегородках, подвесных потолках и ненагружаемых перекрытиях.</p><h2>Плиты Penoplex XPS: фундамент, пол и цоколь</h2><p><a href="/ru/product/penoplex-xps-lovh-l-r/">Плиты Penoplex XPS</a> изготовлены из экструдированного пенополистирола. Их главные плюсы — практически нулевое водопоглощение, высокая прочность на сжатие и низкая теплопроводность. Поэтому XPS применяют там, где есть влага и нагрузка:</p><ul><li>наружное утепление фундамента и стен подвала;</li><li>цокольная часть фасада у самой земли;</li><li>бетонные полы и утепление под стяжку;</li><li>плоские кровли и балконы.</li></ul><p>Размер плиты — 585 × 1185 мм, толщина — 20, 30, 50 и 100 мм. В отличие от каменной ваты, XPS почти не пропускает пар. Поэтому в кирпичном или блочном доме основную площадь фасада чаще утепляют ватой, а цоколь — плитами XPS.</p><h2>Polynor 3D: напыляемый утеплитель для сложных мест</h2><p>Некоторые места трудно утеплить плитами или рулонами: металлоконструкции, трубы, углы под кровлей, поверхности сложной формы. Здесь выручает <a href="/ru/product/polynor-3d-izolyasiya-polinor/">напыляемая изоляция Polynor</a>. Polynor 3D — однокомпонентный полиуретановый утеплитель в баллоне; распылительная насадка надевается прямо на него. Утеплитель ложится на бетон, дерево и металл, образует бесшовное покрытие и не пропускает воду и воздух. Материал предназначен для тепло- и звукоизоляции. Global Procurement — официальный дистрибьютор завода Polinor в Азербайджане.</p><h2>Что нужно для штукатурного фасада, кроме утеплителя</h2><p>Утеплитель — лишь часть системы. Чтобы штукатурный фасад долго служил и не трескался, все слои должны быть собраны правильно:</p><ol><li><strong>Грунтовка.</strong> Стену грунтуют, чтобы укрепить основание и улучшить сцепление клея.</li><li><strong>Клей и плиты.</strong> Клей наносят на всю плиту зубчатым шпателем 10–12 мм, плиту устанавливают не позднее чем через 20 минут. Монтаж начинают от углов здания и проёмов.</li><li><strong>Фасадные дюбели.</strong> Дюбели ISOMAX — с термоголовкой и стальным гвоздём, длиной 115 и 155 мм (по запросу до 200 мм). Подходят для каменной ваты, XPS и EPS, надёжно держатся в кирпиче, бетоне и газобетоне. Длину подбирают по толщине утеплителя и глубине анкеровки в стене.</li><li><strong>Армирующая сетка.</strong> Фасадная сетка ISOMAX 160 г/м² утапливается в базовый штукатурный слой поверх утеплителя и придаёт штукатурке прочность. Сетка устойчива к влаге и щелочам, рулон — 50 м². Углы защищают угловым профилем с сеткой.</li><li><strong>Штукатурка.</strong> Базовый слой и декоративный финиш.</li></ol><p>Сетку, дюбели и другие комплектующие ищите в разделе «<a href="/ru/product-category/fasad-m-hsullari/">Фасадные материалы</a>». Global Procurement — официальный дистрибьютор ISOMAX RUS в Азербайджане.</p><h2>Коротко: какой утеплитель куда</h2><ul><li><strong>Штукатурный фасад:</strong> Thermo Plus Fasad Pro, сетка и дюбели ISOMAX.</li><li><strong>Вентилируемый фасад:</strong> Thermo Plus Vent Pro и негорючая мембрана.</li><li><strong>Скатная кровля и мансарда:</strong> минеральная изоляция ТЕХНОНИКОЛЬ и мембраны SOFIT.</li><li><strong>Пол:</strong> по лагам — минеральная вата, по бетону и под стяжку — Penoplex XPS.</li><li><strong>Фундамент и цоколь:</strong> Penoplex XPS.</li><li><strong>Перегородки и потолки:</strong> Техноакустик.</li><li><strong>Сложные поверхности и металл:</strong> Polynor 3D.</li></ul><p>Купить утеплитель в Баку просто: актуальные цены на каменную вату, XPS и фасадные материалы — в карточках товаров под статьёй. Добавьте нужные позиции в список запроса и отправьте форму или напишите нам в WhatsApp: менеджер подтвердит цену и предложит специальные оптовые условия в зависимости от объёма. Склад находится в Баку, Дярнягюль, Баксольская дорога.</p>',
            ],
        ],
        [
            'slug' => 'dam-ve-temel-hidroizolyasiya-materiallari',
            'image' => 'products/pvc-membran-logicbase-texnonikol-bcb311.webp',
            'products' => ['pvc-membran-logicbase-texnonikol', 'welder-pes-kar-el-qaynaq-feni', 'bitum-praymer-18-lt', 'texnonikol-stekloizol-tol', 'su-v-kul-k-izolyasiya-membrani-sofit-premium-a-70', 'buxar-izolyasiya-membrani-sofit-premium-b'],
            'date' => '2026-10-16',
            'az' => [
                'title' => 'Dam və təməlin hidroizolyasiyası: qatların ardıcıllığı və material seçimi',
                'lead' => 'PVC membran, bitumlu rulonlar və SOFIT membranları ilə dam və təməli sudan necə qorumalı: qatların düzgün ardıcıllığı, tipik səhvlər və material seçimi.',
                'meta_title' => 'Hidroizolyasiya materialları Bakıda: dam və təməl, qiymətlər',
                'meta_desc' => 'Hidroizolyasiya materialları Bakıda: PVC membran, praymer, Stekloizol və SOFIT. Qatların ardıcıllığı, səhvlər, qiymət və topdan alış — sorğu göndərin.',
                'body' => '<p>Dam və ya təməldə ilk sızma adətən tikinti bitəndən sonra, təmirin ən baha başa gəldiyi vaxtda üzə çıxır. Buna görə <strong>hidroizolyasiya materiallarını</strong> seçərkən təkcə qiymətə yox, bütöv sistemə baxmaq lazımdır: hansı material hansı səthə uyğundur, qatlar hansı ardıcıllıqla döşənir və tikişlər necə birləşdirilir. Bu yazıda kataloqumuzdakı materiallar əsasında üç əsas həlli izah edirik: PVC membran, bitumlu rulon sistemi və maili dam üçün SOFIT membranları.</p>
<h2>Hansı sistem hansı iş üçündür</h2>
<p>Hər yerə uyğun universal hidroizolyasiya yoxdur. Seçim obyektin növündən, suyun konstruksiyaya necə təsir etdiyindən və işi kimin görəcəyindən asılıdır.</p>
<ul>
<li><strong>PVC membran</strong> — təməl, zirzəmi, tunel və digər yeraltı konstruksiyalar, həmçinin damlar üçün. Zolaqlar isti hava ilə qaynaq edilərək vahid, monolit örtük yaradır.</li>
<li><strong>Bitumlu rulon sistemi</strong> (praymer + Stekloizol) — dam örtüyü və təməl üçün tanış və geniş yayılmış həll: səth praymerlə hazırlanır, rulon qaz yandırıcısı (qorelka) ilə əridilərək yapışdırılır.</li>
<li><strong>Maili dam üçün membranlar</strong> — SOFIT membranları dam örtüyünü əvəz etmir, istilik izolyasiyasını nəmdən, küləkdən və kondensatdan qoruyur.</li>
</ul>
<h2>Təməl hidroizolyasiyası: PVC membran, yoxsa bitumlu rulon</h2>
<h3>LOGICBASE PVC membranı və WELDER qaynaq feni</h3>
<p><a href="/product/pvc-membran-logicbase-texnonikol/">TechnoNICOL LOGICBASE PVC membranı</a> yüksək sıxlıqlı PVC əsaslı hidroizolyasiya membranıdır. 1,5 və 2,0 mm qalınlıqda təqdim olunur, su keçirmir, kimyəvi təsirlərə davamlıdır, çürümür və elastikliyini saxlayır. Membran betonla birbaşa təmas üçün uyğundur və −25 °C ilə +60 °C arasındakı temperaturda istifadə oluna bilər. Sarı-qara siqnal təbəqəli 1,5 mm-lik variant 43 m²-lik rulonlarda satılır: açıq rəngli siqnal təbəqəsi quraşdırma zamanı zədələri gözlə görməyə kömək edir.</p>
<p>Membranın etibarlılığı tikişlərin keyfiyyətindən asılıdır. Zolaqlar üst-üstə qoyulur və birləşmə yeri isti hava ilə qaynaq edilir. Bunun üçün <a href="/product/welder-pes-kar-el-qaynaq-feni/">WELDER peşəkar əl qaynaq feni</a> nəzərdə tutulub: PVC, HDPE və LDPE membranlarla işləyir, temperaturu tənzimlənir və müxtəlif başlıqlarla istifadə olunur. Bütün WELDER avadanlıqlarına 1 il rəsmi zəmanət verilir, servis və texniki dəstək Bakıda göstərilir.</p>
<h3>Bitum praymer və Stekloizol</h3>
<p>Bitumlu sistemdə ilk addım səthin hazırlanmasıdır. Beton tozdan, çirkdən və yağ ləkələrindən təmizlənir, quru səth <a href="/product/bitum-praymer-18-lt/">bitum praymer</a> ilə örtülür. Praymerin sərfiyyatı səthin növündən asılı olaraq 0,25–0,35 l/m² təşkil edir, yəni 18 litrlik qab təxminən 50–70 m² üçün kifayət edir. İstifadədən əvvəl praymeri yaxşıca qarışdırmaq lazımdır.</p>
<p>Praymer quruduqdan sonra <a href="/product/texnonikol-stekloizol-tol/">TechnoNICOL Stekloizol</a> rulonları qaz yandırıcısı ilə əridilərək yapışdırılır. Stekloizol 2,5 markası 9 m²-lik rulonlarda satılır; Bikrost, Unifleks və Texnoelast kimi digər markaların mövcudluğunu menecerdən dəqiqləşdirə bilərsiniz.</p>
<p><em>Hansını seçməli?</em> Qaynaq avadanlığınız və təcrübəli ustanız varsa, PVC membran bir rulonla daha böyük sahəni örtür və tikişlər qaynaqla birləşir. Bitumlu sistem isə qaz yandırıcısı ilə işləməyə öyrəşmiş briqadalar və kiçik həcmli işlər üçün rahatdır.</p>
<h2>Maili dam: qatların düzgün ardıcıllığı</h2>
<p>Maili damda əsas məqsəd istilik izolyasiyasını iki tərəfdən qorumaqdır: otaqdan gələn buxardan və çöldən gələn nəmdən. Qatlar otaq tərəfdən dam örtüyünə doğru belə düzülür:</p>
<ol>
<li><strong>Buxar izolyasiyası</strong> — istilik izolyasiyasının otağa baxan, isti tərəfində. <a href="/product/buxar-izolyasiya-membrani-sofit-premium-b/">SOFIT Premium B</a> və ya SOFIT PROFLINE B iki qatlı membranlardır, mineral yun, XPS və penoplastla birlikdə istifadə olunur, kondensatın və çürümənin qarşısını alır.</li>
<li><strong>İstilik izolyasiyası</strong> — tirlərin arasında, sıx və boşluqsuz döşənir.</li>
<li><strong>Su və külək izolyasiyası</strong> — istilik izolyasiyasının üzərində. <a href="/product-category/su-v-kul-k-izolyasiya/">SOFIT PREMIUM A70 və gücləndirilmiş A120</a> membranları izolyasiyanı kondensatdan, nəmdən, küləkdən və yağışdan qoruyur, eyni zamanda içindəki su buxarını çölə çıxarır.</li>
<li><strong>Havalandırma boşluğu və dam örtüyü</strong> — membranla örtük arasında havanın hərəkəti üçün boşluq saxlanılır.</li>
</ol>
<p>SOFIT membranlarının eni 1,6 m, rulonun sahəsi isə 70 m²-dir, bu da material hesablamasını asanlaşdırır.</p>
<h2>Tez-tez rast gəlinən səhvlər</h2>
<ul>
<li><strong>Praymer çəkmədən döşəmək.</strong> Tozlu, nəm və ya hazırlanmamış betona yapışdırılan bitumlu rulon zamanla qopur və su onun altına keçir.</li>
<li><strong>Membranların yerini qarışdırmaq.</strong> Buxar izolyasiyası çöl tərəfə qoyulduqda nəm istilik izolyasiyasının içində qalır. Buxar izolyasiyası içəri tərəfdə, su-külək membranı isə çöl tərəfdə olmalıdır.</li>
<li><strong>Yoxlanılmamış qaynaq tikişləri.</strong> PVC membranı adi tikinti feni ilə və ya uyğun olmayan temperaturda qaynaq etmək zəif tikiş verir. İşə başlamazdan əvvəl membranın artıq qalan parçasında sınaq qaynağı edib tikişi yoxlayın.</li>
<li><strong>PVC və bitumun birbaşa təması.</strong> PVC membran bitumlu materiallarla birbaşa təmasda olmamalıdır; iki sistem bir yerdə istifadə olunursa, aralarında ayırıcı qat nəzərdə tutulmalıdır.</li>
<li><strong>Birləşmə nöqtələrinə diqqətsizlik.</strong> Künclər, boruların keçdiyi yerlər və divarla birləşmələr sızmanın ən çox baş verdiyi nöqtələrdir və ayrıca diqqət tələb edir.</li>
<li><strong>Torpaq doldurularkən membranı qorumamaq.</strong> Təməlin ətrafı doldurulanda daşlar və alətlər membranı zədələyə bilər, ona görə hidroizolyasiya qatı əvvəlcədən qorunmalıdır.</li>
<li><strong>Ehtiyat payı olmadan hesablama.</strong> Sahəni rulonun sahəsinə (43, 9 və ya 70 m²) bölmək kifayət deyil: kənarların üst-üstə qoyulması və kəsim itkiləri üçün ehtiyat payı götürün.</li>
</ul>
<h2>Material seçimi və sifariş</h2>
<p>Seçim üçün qısa qayda belədir: yeraltı konstruksiyalar və qaynaqlı, uzunömürlü sistem üçün — LOGICBASE PVC membranı və WELDER feni; qaz yandırıcısı ilə əridilən tanış rulon sistemi üçün — bitum praymer və Stekloizol; maili dam və mansard üçün — SOFIT buxar izolyasiyası və su-külək membranı. Global Procurement Group MMC həmçinin düz damların PVC membranla hidroizolyasiyası xidmətini göstərir.</p>
<p>Aktual qiymətləri və topdan alış şərtlərini öyrənmək üçün məhsulları sorğu siyahısına əlavə edib saytda sorğu göndərin və ya bizə WhatsApp-da yazın — menecerimiz həcminizə uyğun təklif hazırlayacaq. Anbarımız Bakıda, Dərnəgül, Baksol yolunda yerləşir.</p>',
            ],
            'ru' => [
                'title' => 'Гидроизоляция кровли и фундамента: порядок слоёв и выбор материалов',
                'lead' => 'Как защитить фундамент и кровлю от воды с помощью ПВХ-мембраны, битумных рулонов и мембран SOFIT: порядок слоёв, типичные ошибки и выбор материалов.',
                'meta_title' => 'Гидроизоляция фундамента и кровли в Баку: материалы и цены',
                'meta_desc' => 'Гидроизоляционные материалы в Баку: ПВХ-мембрана LOGICBASE, праймер, Стеклоизол, SOFIT. Порядок слоёв, ошибки, цены и опт — отправьте запрос.',
                'body' => '<p>Протечка в подвале или под кровлей обычно обнаруживается уже после окончания стройки — когда ремонт обходится дороже всего. Поэтому при выборе <strong>гидроизоляционных материалов</strong> важно смотреть не только на цену, но и на систему целиком: какой материал подходит для какого основания, в каком порядке укладываются слои и как соединяются швы. Ниже разбираем три основных решения из нашего каталога: ПВХ-мембрану, битумную рулонную систему и мембраны SOFIT для скатной кровли.</p>
<h2>Какая система для какой задачи</h2>
<p>Универсальной гидроизоляции не существует. Выбор зависит от типа объекта, от того, как вода воздействует на конструкцию, и от того, кто будет выполнять монтаж.</p>
<ul>
<li><strong>ПВХ-мембрана</strong> — фундаменты, подвалы, тоннели и другие подземные конструкции, а также кровли. Полотна свариваются горячим воздухом в сплошной монолитный ковёр.</li>
<li><strong>Битумная рулонная система</strong> (праймер + Стеклоизол) — привычное и распространённое решение для кровли и фундамента: основание грунтуется праймером, рулон наплавляется горелкой.</li>
<li><strong>Мембраны для скатной кровли</strong> — плёнки SOFIT не заменяют кровельное покрытие, а защищают утеплитель от влаги, ветра и конденсата.</li>
</ul>
<h2>Гидроизоляция фундамента: ПВХ-мембрана или битумные рулоны</h2>
<h3>ПВХ-мембрана LOGICBASE и сварочный фен WELDER</h3>
<p><a href="/ru/product/pvc-membran-logicbase-texnonikol/">ПВХ-мембрана LOGICBASE ТЕХНОНИКОЛЬ</a> — гидроизоляционная мембрана высокой плотности на основе ПВХ. Выпускается толщиной 1,5 и 2,0 мм, не пропускает воду, стойка к химическим воздействиям, не гниёт и остаётся эластичной. Допускает прямой контакт с бетоном, диапазон рабочих температур — от −25 °C до +60 °C. Жёлто-чёрная мембрана 1,5 мм с сигнальным слоем поставляется в рулонах по 43 м²: светлый сигнальный слой помогает заметить повреждения прямо во время монтажа.</p>
<p>Надёжность мембраны определяется качеством швов. Полотна укладывают внахлёст, и нахлёст сваривают горячим воздухом. Для этого предназначен <a href="/ru/product/welder-pes-kar-el-qaynaq-feni/">профессиональный ручной сварочный фен WELDER</a>: он работает с мембранами ПВХ, HDPE и LDPE, имеет регулировку температуры и используется со сменными насадками. На всё оборудование WELDER действует официальная гарантия 1 год, сервис и техподдержка — в Баку.</p>
<h3>Битумный праймер и Стеклоизол</h3>
<p>В битумной системе первым делом готовят основание. Бетон очищают от пыли, грязи и масляных пятен, а по сухой поверхности наносят <a href="/ru/product/bitum-praymer-18-lt/">битумный праймер</a>. Расход — 0,25–0,35 л/м² в зависимости от типа поверхности, то есть ёмкости на 18 л хватает примерно на 50–70 м². Перед применением праймер нужно тщательно перемешать.</p>
<p>Когда праймер высохнет, наплавляют рулоны <a href="/ru/product/texnonikol-stekloizol-tol/">Стеклоизол ТЕХНОНИКОЛЬ</a>. Стеклоизол 2,5 поставляется в рулонах по 9 м²; наличие других марок — Бикрост, Унифлекс, Техноэласт — уточняйте у менеджера.</p>
<p><em>Что выбрать?</em> Если есть сварочное оборудование и опытный монтажник, ПВХ-мембрана закрывает одним рулоном большую площадь, а швы соединяются сваркой. Битумная система удобна для бригад, привыкших работать с горелкой, и для небольших объёмов.</p>
<h2>Скатная кровля: правильный порядок слоёв</h2>
<p>В скатной кровле утеплитель защищают с двух сторон: от пара, который идёт из помещения, и от влаги снаружи. Слои располагаются в таком порядке — от помещения к кровельному покрытию:</p>
<ol>
<li><strong>Пароизоляция</strong> — со стороны помещения, на тёплой стороне утеплителя. <a href="/ru/product/buxar-izolyasiya-membrani-sofit-premium-b/">SOFIT Premium B</a> или SOFIT PROFLINE B — двухслойные плёнки, которые применяются с минеральной ватой, XPS и пенопластом и предотвращают образование конденсата и гниение.</li>
<li><strong>Утеплитель</strong> — между стропилами, плотно и без пустот.</li>
<li><strong>Ветро-влагозащита</strong> — поверх утеплителя. <a href="/ru/product-category/su-v-kul-k-izolyasiya/">Мембрана SOFIT PREMIUM A70 и её усиленная версия A120</a> защищают утеплитель от конденсата, влаги, ветра и осадков и при этом выводят из него водяной пар.</li>
<li><strong>Вентиляционный зазор и кровельное покрытие</strong> — между мембраной и покрытием оставляют зазор для движения воздуха.</li>
</ol>
<p>Ширина мембран SOFIT — 1,6 м, площадь рулона — 70 м², поэтому материал легко рассчитать.</p>
<h2>Типичные ошибки</h2>
<ul>
<li><strong>Укладка без праймера.</strong> Битумный рулон, наплавленный на пыльный, влажный или неподготовленный бетон, со временем отслаивается, и вода затекает под него.</li>
<li><strong>Перепутанные мембраны.</strong> Если пароизоляцию поставить снаружи, влага останется в утеплителе. Пароизоляция — изнутри, ветро-влагозащита — снаружи.</li>
<li><strong>Непроверенные сварные швы.</strong> Сварка ПВХ обычным строительным феном или при неподходящей температуре даёт слабый шов. Перед началом работ сделайте пробную сварку на обрезке и проверьте шов.</li>
<li><strong>Прямой контакт ПВХ с битумом.</strong> ПВХ-мембрана не должна напрямую соприкасаться с битумными материалами; если две системы встречаются на одном объекте, между ними предусматривают разделительный слой.</li>
<li><strong>Невнимание к примыканиям.</strong> Углы, проходы труб и примыкания к стенам — места, где протечки появляются чаще всего, поэтому им нужно уделить отдельное внимание.</li>
<li><strong>Незащищённая мембрана при обратной засыпке.</strong> Камни и инструмент могут повредить гидроизоляцию, поэтому перед засыпкой её нужно защитить.</li>
<li><strong>Расчёт без запаса.</strong> Недостаточно разделить площадь объекта на площадь рулона (43, 9 или 70 м²): заложите запас на нахлёсты и подрезку.</li>
</ul>
<h2>Как выбрать материалы и сделать заказ</h2>
<p>Короткое правило выбора: для подземных конструкций и долговечной сварной системы — мембрана LOGICBASE и фен WELDER; для привычной технологии с горелкой — битумный праймер и Стеклоизол; для скатной кровли и мансарды — пароизоляция и ветро-влагозащитная мембрана SOFIT. Кроме поставки материалов, Global Procurement Group MMC выполняет гидроизоляцию плоских кровель ПВХ-мембранами.</p>
<p>Чтобы узнать актуальные цены и оптовые условия, добавьте товары в список запроса и отправьте его на сайте или напишите нам в WhatsApp — менеджер подготовит предложение под ваш объём. Склад находится в Баку: Дарнагюль, Баксольская дорога.</p>',
            ],
        ],
    ];
}
