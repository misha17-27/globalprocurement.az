/*
 * Admin panelinin kiçik köməkçisi.
 * Ayrıca fayldadır ki, CSP-də script-src üçün 'unsafe-inline' lazım olmasın.
 */
(function () {
  'use strict';

  // Silmə formaları: data-confirm ilə təsdiq
  document.addEventListener('submit', function (ev) {
    var f = ev.target;
    if (!(f instanceof HTMLFormElement)) return;
    var msg = f.getAttribute('data-confirm');
    if (msg && !window.confirm(msg)) ev.preventDefault();
  });

  // Saxlayandan sonra eyni yerə qayıtmaq üçün: sürüşmə mövqeyi və açıq dil tabı (AZ/RU) yadda saxlanılır
  var RET = 'gpadm-return';
  document.addEventListener('submit', function (ev) {
    var f = ev.target;
    if (!(f instanceof HTMLFormElement) || ev.defaultPrevented || (f.method || '').toLowerCase() !== 'post') return;
    var tabs = Array.prototype.map.call(document.querySelectorAll('[data-ltab]:not(.ghost)'), function (b) { return b.getAttribute('data-ltab'); });
    try { sessionStorage.setItem(RET, JSON.stringify({ url: location.pathname + location.search, y: Math.round(window.scrollY), tabs: tabs, t: Date.now() })); } catch (e) {}
  });

  // Silmə düymələri
  document.addEventListener('click', function (ev) {
    var el = ev.target.closest('[data-confirm]');
    if (!el || el.tagName === 'FORM') return;
    var msg = el.getAttribute('data-confirm');
    if (msg && !window.confirm(msg)) { ev.preventDefault(); ev.stopPropagation(); }
  });

  // Mətni bir kliklə seçib buferə köçürən sahələr
  document.addEventListener('click', function (ev) {
    var el = ev.target;
    if (!(el instanceof HTMLInputElement) || !el.hasAttribute('data-copy')) return;
    el.select();
    if (navigator.clipboard) navigator.clipboard.writeText(el.value);
  });

  // Dil tabları (AZ / RU) — formada bütün dillər birdən göndərilir
  document.addEventListener('click', function (ev) {
    var b = ev.target.closest('[data-ltab]');
    if (!b) return;
    var key = b.getAttribute('data-ltab'), grp = key.slice(0, -2);
    document.querySelectorAll('[data-ltab^="' + grp + '"]').forEach(function (x) { x.classList.toggle('ghost', x !== b); });
    document.querySelectorAll('[data-lpane^="' + grp + '"]').forEach(function (p) { p.classList.toggle('on', p.getAttribute('data-lpane') === key); });
  });

  // SEO sahələrində simvol sayğacı (data-len="min-max")
  function lenTick(el) {
    var out = document.querySelector('[data-len-for="' + el.id + '"]');
    if (!out) return;
    var r = el.getAttribute('data-len').split('-'), n = el.value.trim().length;
    out.textContent = n + ' / ' + r[0] + '–' + r[1];
    out.style.color = n === 0 ? '#94a3b8' : (n >= +r[0] && n <= +r[1] ? '#16a34a' : '#d97706');
  }
  document.querySelectorAll('[data-len]').forEach(function (el) { lenTick(el); el.addEventListener('input', function () { lenTick(el); }); });

  // Parolu göstər / gizlət + Caps Lock və klaviatura dili xəbərdarlığı — hər parol sahəsinin yanında
  document.querySelectorAll('input[type="password"]').forEach(function (inp) {
    var b = document.createElement('button');
    b.type = 'button';
    b.textContent = 'показать';
    b.className = 'btn sm ghost';
    b.style.cssText = 'margin-top:6px;padding:3px 10px;font-size:12px';
    b.addEventListener('click', function () {
      var show = inp.type === 'password';
      inp.type = show ? 'text' : 'password';
      b.textContent = show ? 'скрыть' : 'показать';
    });
    inp.insertAdjacentElement('afterend', b);

    var warn = document.createElement('div'), caps = false;
    warn.className = 'pw-warn';
    warn.hidden = true;
    b.insertAdjacentElement('afterend', warn);
    function check() {
      var m = [];
      if (caps) m.push('Включён Caps Lock.');
      if (/[^\x20-\x7E]/.test(inp.value)) m.push('В пароле есть не латинские символы — проверьте раскладку клавиатуры.');
      warn.textContent = m.join(' ');
      warn.hidden = !m.length;
    }
    function key(e) { if (e.getModifierState) { caps = e.getModifierState('CapsLock'); check(); } }
    inp.addEventListener('keydown', key);
    inp.addEventListener('keyup', key);
    inp.addEventListener('input', check);
    inp.addEventListener('blur', function () { caps = false; check(); });
  });

  /* ================= Şəkillər =================
     ✕ — plitə dərhal gizlənir, silinmə formanı saxlayanda tətbiq olunur («Вернуть» — hamısını qaytarır).
     Yeni seçilmiş fayllar yükləmədən əvvəl önizlənir; ✕ faylı seçimdən çıxarır. */
  function saveLabel(form) {
    var b = form && form.querySelector('button:not([type="button"]):not([name="action"])');
    return '«' + (b ? b.textContent.trim() : 'Сохранить') + '»';
  }
  function galNote(after, cls, undo) {
    var note = document.createElement('div'), txt = document.createElement('span');
    note.className = 'gal-note' + (cls ? ' ' + cls : '');
    note.hidden = true;
    note.setAttribute('role', 'status');
    note.appendChild(txt);
    if (undo) {
      var b = document.createElement('button');
      b.type = 'button'; b.className = 'btn sm ghost'; b.textContent = 'Вернуть';
      b.addEventListener('click', undo);
      note.appendChild(b);
    }
    after.insertAdjacentElement('afterend', note);
    return function (msg) { txt.textContent = msg || ''; note.hidden = !msg; };
  }

  document.querySelectorAll('.gal:not(.gal-new)').forEach(function (gal) {
    var tiles = Array.prototype.slice.call(gal.querySelectorAll('.gi'));
    if (!tiles.length) return;
    var form = gal.closest('form');
    var field = function (t) { var n = t.getAttribute('data-clear'); return n && form ? form.querySelector('[name="' + n + '"]') : null; };
    var say = galNote(gal, '', function () {
      tiles.forEach(function (t) {
        var cb = t.querySelector('input[type="checkbox"]'), inp = field(t);
        if (cb) cb.checked = false;
        if (inp && t.hasAttribute('data-was')) { inp.value = t.getAttribute('data-was'); t.removeAttribute('data-was'); }
        t.hidden = false;
      });
      upd();
    });
    function upd() {
      var n = tiles.filter(function (t) { return t.hidden; }).length;
      say(n ? (n === 1 ? 'Фото удалено' : 'Удалено фото: ' + n) + ' — изменения применятся после нажатия ' + saveLabel(form) + '.' : '');
    }
    tiles.forEach(function (t) {
      var cb = t.querySelector('input[type="checkbox"]');
      if (cb && cb.checked) t.hidden = true;               // brauzer formu bərpa edibsə
      var x = t.querySelector('.gal-x');
      if (!x) return;
      x.addEventListener('click', function () {
        var inp = field(t);
        if (cb) cb.checked = true;
        if (inp) { t.setAttribute('data-was', inp.value); inp.value = ''; }
        t.hidden = true;
        upd();
        var next = tiles.filter(function (o) { return !o.hidden; })[0];   // fokus itməsin
        if (next) next.querySelector('.gal-x').focus();
      });
    });
    upd();
  });

  document.querySelectorAll('input[type="file"][accept^="image"]').forEach(function (inp) {
    var box = document.createElement('div');
    box.className = 'gal gal-new';
    box.hidden = true;
    inp.insertAdjacentElement('afterend', box);
    var say = galNote(box, 'new', null);
    function render() {
      var files = Array.prototype.slice.call(inp.files || []);
      box.textContent = '';
      files.forEach(function (file, i) {
        var t = document.createElement('div'), img = document.createElement('img'), x = document.createElement('button');
        t.className = 'gi'; t.title = file.name;
        img.alt = '';
        x.type = 'button'; x.className = 'gal-x'; x.textContent = '✕'; x.title = 'Убрать'; x.setAttribute('aria-label', 'Убрать ' + file.name);
        x.addEventListener('click', function () { drop(i); });
        t.appendChild(img); t.appendChild(x); box.appendChild(t);
        if (/^image\//.test(file.type) && file.size < 15e6 && window.FileReader) {
          var r = new FileReader();
          r.onload = function () { img.src = String(r.result); };
          r.readAsDataURL(file);
        }
      });
      box.hidden = !files.length;
      say(files.length ? (files.length === 1 ? 'Новое фото — загрузится' : 'Новых фото: ' + files.length + ' — загрузятся') + ' после нажатия ' + saveLabel(inp.form) + '.' : '');
    }
    function drop(i) {
      try {
        var dt = new DataTransfer();
        Array.prototype.forEach.call(inp.files, function (f, j) { if (j !== i) dt.items.add(f); });
        inp.files = dt.files;
      } catch (e) { inp.value = ''; }                      // köhnə brauzer: seçimi tam sıfırla
      render();
    }
    inp.addEventListener('change', render);
    render();
  });

  /* ================= Vizual redaktor =================
     <textarea data-editor> → alətlər paneli + contenteditable sahə.
     Mətn hər dəyişiklikdə textarea-ya yazılır; "HTML" düyməsi kodu göstərir.
     Yapışdırılan (Word, sayt) məzmun ağ siyahı ilə təmizlənir. Serverdə əlavə olaraq sanitize_html. */
  var KEEP = { P: 1, BR: 1, STRONG: 1, B: 1, EM: 1, I: 1, U: 1, H2: 1, H3: 1, H4: 1, UL: 1, OL: 1, LI: 1, A: 1,
               TABLE: 1, THEAD: 1, TBODY: 1, TR: 1, TH: 1, TD: 1, BLOCKQUOTE: 1, IMG: 1 };
  var DROP = { SCRIPT: 1, STYLE: 1, META: 1, LINK: 1, TITLE: 1, IFRAME: 1, OBJECT: 1, EMBED: 1, SVG: 1, FORM: 1,
               INPUT: 1, BUTTON: 1, SELECT: 1, TEXTAREA: 1, NOSCRIPT: 1, TEMPLATE: 1, HEAD: 1 };
  var RENAME = { H1: 'H2', H5: 'H4', H6: 'H4', DIV: 'P', SECTION: 'P', ARTICLE: 'P' };
  function safeUrl(u) { u = (u || '').trim(); return /^(https?:|mailto:|tel:|\/|#)/i.test(u) ? u : ''; }
  function cleanTree(node, doc) {
    Array.prototype.slice.call(node.childNodes).forEach(function (c) {
      if (c.nodeType === 8) { c.remove(); return; }                       // şərh
      if (c.nodeType !== 1) return;                                       // mətn qalır
      var tag = c.tagName.toUpperCase();
      if (DROP[tag] || /^(O|W|V|M):/i.test(c.tagName)) { c.remove(); return; }   // Word-un xüsusi teqləri
      if (RENAME[tag]) {
        var n = doc.createElement(RENAME[tag]);
        while (c.firstChild) n.appendChild(c.firstChild);
        c.parentNode.replaceChild(n, c); c = n; tag = n.tagName;
      }
      cleanTree(c, doc);
      if (!KEEP[tag]) {                                                   // span, font... — açılır
        while (c.firstChild) c.parentNode.insertBefore(c.firstChild, c);
        c.remove(); return;
      }
      Array.prototype.slice.call(c.attributes).forEach(function (a) {
        var name = a.name.toLowerCase();
        if (tag === 'A' && name === 'href') { var u = safeUrl(a.value); if (u) c.setAttribute('href', u); else c.removeAttribute('href'); return; }
        if (tag === 'IMG' && (name === 'src' || name === 'alt')) { if (name === 'src' && !safeUrl(a.value)) c.removeAttribute('src'); return; }
        if ((tag === 'TD' || tag === 'TH') && (name === 'colspan' || name === 'rowspan')) return;
        c.removeAttribute(a.name);
      });
      if (tag === 'IMG' && !c.getAttribute('src')) c.remove();
    });
  }
  function cleanHTML(html) {
    var doc = new DOMParser().parseFromString('<body>' + html + '</body>', 'text/html');
    cleanTree(doc.body, doc);
    return doc.body.innerHTML.replace(/<p>(\s|&nbsp;|<br>)*<\/p>/g, '').trim();
  }
  function textToHTML(t) {
    var esc = function (s) { return s.replace(/[&<>"]/g, function (ch) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[ch]; }); };
    return t.replace(/\r/g, '').split(/\n{2,}/).map(function (b) { b = b.trim(); return b ? '<p>' + esc(b).replace(/\n/g, '<br>') + '</p>' : ''; }).join('');
  }

  var BUTTONS = [
    ['B', 'bold', 'Жирный (Ctrl+B)', 'font-weight:800'],
    ['I', 'italic', 'Курсив (Ctrl+I)', 'font-style:italic;font-weight:600'],
    '|',
    ['H2', 'h2', 'Заголовок'], ['H3', 'h3', 'Подзаголовок'], ['¶', 'p', 'Обычный абзац'],
    '|',
    ['• список', 'ul', 'Маркированный список'], ['1. список', 'ol', 'Нумерованный список'],
    '|',
    ['🔗', 'link', 'Ссылка'], ['убрать ссылку', 'unlink', 'Убрать ссылку'], ['очистить', 'clear', 'Убрать форматирование'],
    ['HTML', 'src', 'Показать / скрыть HTML-код']
  ];

  document.querySelectorAll('textarea[data-editor]').forEach(function (ta) {
    var wrap = document.createElement('div'); wrap.className = 'rte';
    var bar = document.createElement('div'); bar.className = 'rte-bar';
    var area = document.createElement('div'); area.className = 'rte-area';
    area.contentEditable = 'true';
    area.setAttribute('data-placeholder', 'Начните вводить текст…');
    ta.parentNode.insertBefore(wrap, ta);
    wrap.appendChild(bar); wrap.appendChild(area); wrap.appendChild(ta);
    area.innerHTML = cleanHTML(ta.value);   // DOMParser-dəki sənəd "ölüdür" — təmizlənməmiş HTML canlı səhifəyə düşmür

    var sync = function () { if (!wrap.classList.contains('source')) ta.value = area.innerHTML.replace(/<p><br><\/p>$/, ''); };
    var cmd = function (name, val) {
      area.focus();
      try { document.execCommand('styleWithCSS', false, false); document.execCommand('defaultParagraphSeparator', false, 'p'); } catch (e) {}
      document.execCommand(name, false, val);
      sync(); refresh();
    };

    BUTTONS.forEach(function (b) {
      if (b === '|') { var s = document.createElement('span'); s.className = 'sep'; bar.appendChild(s); return; }
      var btn = document.createElement('button');
      btn.type = 'button'; btn.textContent = b[0]; btn.title = b[2]; btn.setAttribute('data-cmd', b[1]);
      if (b[3]) btn.style.cssText = b[3];
      if (b[1] === 'src') btn.className = 'src';
      btn.addEventListener('mousedown', function (e) { e.preventDefault(); });   // seçim itməsin
      btn.addEventListener('click', function () {
        var c = b[1];
        if (c === 'bold' || c === 'italic') cmd(c);
        else if (c === 'h2' || c === 'h3' || c === 'p') cmd('formatBlock', '<' + c.toUpperCase() + '>');
        else if (c === 'ul') cmd('insertUnorderedList');
        else if (c === 'ol') cmd('insertOrderedList');
        else if (c === 'unlink') cmd('unlink');
        else if (c === 'clear') { cmd('removeFormat'); cmd('formatBlock', '<P>'); }
        else if (c === 'link') {
          var url = window.prompt('Адрес ссылки (https://… или /product/…):', 'https://');
          if (url === null) return;
          url = safeUrl(url);
          if (!url) { window.alert('Ссылка должна начинаться с https://, http://, / или mailto:'); return; }
          cmd('createLink', url);
        }
        else if (c === 'src') {
          if (wrap.classList.contains('source')) { area.innerHTML = cleanHTML(ta.value); wrap.classList.remove('source'); sync(); area.focus(); }
          else { sync(); wrap.classList.add('source'); ta.focus(); }
          btn.classList.toggle('on', wrap.classList.contains('source'));
        }
      });
      bar.appendChild(btn);
    });

    /* Aktiv düymələr (qalın, kursiv, başlıq, siyahı) */
    var refresh = function () {
      if (document.activeElement !== area) return;
      var block = '';
      try { block = (document.queryCommandValue('formatBlock') || '').toLowerCase(); } catch (e) {}
      bar.querySelectorAll('button[data-cmd]').forEach(function (btn) {
        var c = btn.getAttribute('data-cmd'), on = false;
        try {
          if (c === 'bold' || c === 'italic') on = document.queryCommandState(c);
          else if (c === 'ul') on = document.queryCommandState('insertUnorderedList');
          else if (c === 'ol') on = document.queryCommandState('insertOrderedList');
          else if (c === 'h2' || c === 'h3') on = block === c;
        } catch (e) {}
        if (c !== 'src') btn.classList.toggle('on', !!on);
      });
    };
    document.addEventListener('selectionchange', refresh);
    area.addEventListener('keyup', refresh);
    area.addEventListener('input', sync);
    area.addEventListener('blur', sync);

    /* Yapışdırma: HTML təmizlənir, sadə mətn abzaslara bölünür */
    area.addEventListener('paste', function (e) {
      var cd = e.clipboardData || window.clipboardData; if (!cd) return;
      e.preventDefault();
      var html = cd.getData('text/html'), txt = cd.getData('text/plain');
      var out = html ? cleanHTML(html) : textToHTML(txt || '');
      if (out) document.execCommand('insertHTML', false, out);
      sync();
    });
    /* Enter boş sahədə <div> yox, <p> yaratsın */
    area.addEventListener('focus', function () {
      try { document.execCommand('defaultParagraphSeparator', false, 'p'); } catch (e) {}
      if (!area.innerHTML.trim()) area.innerHTML = '<p><br></p>';
    });

    var form = ta.form;
    if (form) form.addEventListener('submit', function () {
      if (wrap.classList.contains('source')) area.innerHTML = cleanHTML(ta.value);
      ta.value = cleanHTML(area.innerHTML);
    });
  });

  /* Saxlamadan sonra həmin səhifə: həmin dil tabı, həmin yer; mesaj ekranın yuxarısında görünür.
     Redaktorlar qurulandan sonra işləyir ki, hündürlüklər artıq son halında olsun. */
  (function () {
    var ret = null;
    try { ret = JSON.parse(sessionStorage.getItem(RET) || 'null'); sessionStorage.removeItem(RET); } catch (e) { return; }
    if (!ret || Date.now() - ret.t > 120000 || ret.url !== location.pathname + location.search) return;
    var btns = document.querySelectorAll('[data-ltab]');
    (ret.tabs || []).forEach(function (k) {
      Array.prototype.forEach.call(btns, function (b) { if (b.getAttribute('data-ltab') === k) b.click(); });
    });
    if (ret.y > 0) window.scrollTo(0, ret.y);
    var fl = document.querySelector('.flash');
    if (fl && ret.y > 120) {
      var t = fl.cloneNode(true);
      t.classList.add('toast');
      t.setAttribute('role', 'status');
      document.body.appendChild(t);
      setTimeout(function () { t.classList.add('out'); }, 2800);
      setTimeout(function () { t.remove(); }, 3300);
    }
  })();
})();
