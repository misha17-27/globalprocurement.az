/*
 * Global Procurement — sayt skriptləri.
 * Məlumat <script type="application/json" id="gp-data"> blokundan oxunur
 * (inline JS yoxdur — CSP üçün).
 */
(function () {
  'use strict';

  var D = {};
  try { D = JSON.parse(document.getElementById('gp-data').textContent); } catch (e) {}
  var T = D.i18n || {};
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var esc = function (s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };

  /* ---------- header, menyu, animasiyalar ---------- */
  var hdr = $('#hdr');
  addEventListener('scroll', function () { if (hdr) hdr.classList.toggle('scrolled', scrollY > 20); }, { passive: true });

  var mob = $('#mob');
  $$('[data-mob-open]').forEach(function (b) { b.addEventListener('click', function () { mob.classList.add('open'); }); });
  $$('[data-mob-close]').forEach(function (b) { b.addEventListener('click', function () { mob.classList.remove('open'); }); });

  var countUp = function (el) {
    var to = +el.getAttribute('data-count'), suf = el.getAttribute('data-suf') || '', n = 0;
    if (!to) return;
    var st = setInterval(function () { n++; el.textContent = n + (n >= to ? suf : ''); if (n >= to) clearInterval(st); }, Math.max(20, 1200 / to));
  };
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) {
        if (!e.isIntersecting) return;
        e.target.classList.add('in'); io.unobserve(e.target);
        $$('[data-count]', e.target).forEach(countUp);
      });
    }, { threshold: 0.12 });
    $$('.reveal').forEach(function (el) { io.observe(el); });
  } else {
    $$('.reveal').forEach(function (el) { el.classList.add('in'); });
  }

  /* ---------- axtarış təklifləri ---------- */
  var hs = $('#hs'), sugg = $('#sugg');
  if (hs && sugg) {
    hs.addEventListener('input', function () {
      var s = hs.value.trim().toLowerCase();
      if (!s) { sugg.classList.remove('open'); return; }
      var r = (D.products || []).filter(function (p) { return (p.t + ' ' + p.b).toLowerCase().indexOf(s) > -1; }).slice(0, 6);
      sugg.innerHTML = r.length
        ? r.map(function (p) { return '<a href="' + esc(p.u) + '"><img src="' + esc(p.i) + '" alt=""><span>' + esc(p.t) + '<small>' + esc(p.b) + '</small></span></a>'; }).join('')
        : '<p style="padding:10px;color:var(--muted);font-size:14px">' + esc(T.none) + '</p>';
      sugg.classList.add('open');
    });
    document.addEventListener('click', function (e) { if (!e.target.closest('.hsearch')) sugg.classList.remove('open'); });
  }

  /* ---------- FAQ ---------- */
  $$('.qa button').forEach(function (b) { b.addEventListener('click', function () { b.parentElement.classList.toggle('open'); }); });

  /* ---------- material bələdçisi ---------- */
  var zone = function (k) {
    $$('[data-z]').forEach(function (e) { e.classList.toggle('on', e.getAttribute('data-z') === k); });
    $$('[data-zone]').forEach(function (e) { e.hidden = e.getAttribute('data-zone') !== k; });
  };
  $$('[data-z]').forEach(function (e) { e.addEventListener('click', function () { zone(e.getAttribute('data-z')); }); });

  /* ---------- kataloq görünüşü ---------- */
  $$('.view button').forEach(function (b) {
    b.addEventListener('click', function () {
      $$('.view button').forEach(function (x) { x.classList.remove('on'); });
      b.classList.add('on');
      var g = $('#pgrid'); if (g) g.classList.toggle('list', b.getAttribute('data-v') === 'list');
    });
  });

  /* =================== SORĞU SİYAHISI =================== */
  var KEY = 'gp_request_v1';
  var cart = [];
  try { cart = JSON.parse(localStorage.getItem(KEY) || '[]') || []; } catch (e) { cart = []; }
  var save = function () { try { localStorage.setItem(KEY, JSON.stringify(cart)); } catch (e) {} render(); };

  var drawer = $('#drawer'), list = $('#cartList'), badge = $('#cartN');
  var render = function () {
    var n = cart.reduce(function (s, i) { return s + 1; }, 0);
    if (badge) { badge.textContent = n; badge.parentElement.classList.toggle('has', n > 0); }
    if (!list) return;
    if (!cart.length) { list.innerHTML = '<div class="cempty">' + esc(T.empty) + '</div>'; return; }
    list.innerHTML = cart.map(function (it, i) {
      return '<div class="cart-item"><img src="' + esc(it.img) + '" alt=""><div><a href="' + esc(it.u) + '"><b>' + esc(it.t) + '</b></a>'
        + (it.opt ? '<small>' + esc(T.opt) + ': ' + esc(it.opt) + '</small>' : '')
        + '<div class="q"><button type="button" data-ci="' + i + '" data-d="-1">−</button><input type="number" min="1" value="' + it.qty + '" data-ci="' + i + '" aria-label="' + esc(T.qty) + '"><button type="button" data-ci="' + i + '" data-d="1">+</button> <small>' + esc(T.pcs) + '</small></div></div>'
        + '<button type="button" class="rm" data-rm="' + i + '" aria-label="✕">✕</button></div>';
    }).join('');
  };

  var add = function (p, qty, opt) {
    qty = Math.max(1, parseInt(qty, 10) || 1); opt = opt || '';
    var ex = cart.filter(function (i) { return i.id === p.id && (i.opt || '') === opt; })[0];
    if (ex) ex.qty += qty; else cart.push({ id: p.id, slug: p.slug, t: p.t, img: p.img, u: p.u, qty: qty, opt: opt });
    save(); toast(T.added);
  };

  var openCart = function () { if (drawer) { drawer.classList.add('open'); drawer.setAttribute('aria-hidden', 'false'); } };
  var closeCart = function () { if (drawer) { drawer.classList.remove('open'); drawer.setAttribute('aria-hidden', 'true'); } };
  document.addEventListener('click', function (e) {
    if (e.target.closest('[data-cart-open]')) { openCart(); hideToast(); }
    if (e.target.closest('[data-cart-close]')) closeCart();
    var a = e.target.closest('[data-add]');
    if (a) {
      try { add(JSON.parse(a.getAttribute('data-add')), 1, ''); a.classList.add('added'); setTimeout(function () { a.classList.remove('added'); }, 1200); } catch (x) {}
    }
    var rm = e.target.closest('[data-rm]');
    if (rm) { cart.splice(+rm.getAttribute('data-rm'), 1); save(); }
    var d = e.target.closest('button[data-ci]');
    if (d) { var it = cart[+d.getAttribute('data-ci')]; it.qty = Math.max(1, it.qty + (+d.getAttribute('data-d'))); save(); }
  });
  document.addEventListener('change', function (e) {
    var inp = e.target.closest('input[data-ci]');
    if (inp) { cart[+inp.getAttribute('data-ci')].qty = Math.max(1, parseInt(inp.value, 10) || 1); save(); }
  });
  addEventListener('keydown', function (e) { if (e.key === 'Escape') { closeCart(); closeDone(); if (mob) mob.classList.remove('open'); } });

  var toastEl = $('#toast'), tt;
  var toast = function (msg) {
    if (!toastEl) return;
    toastEl.querySelector('span').textContent = msg;
    toastEl.classList.add('show'); clearTimeout(tt); tt = setTimeout(hideToast, 3500);
  };
  var hideToast = function () { if (toastEl) toastEl.classList.remove('show'); };

  /* ---------- göndərmə ---------- */
  var done = $('#done');
  var openDone = function (wa) { var a = $('#doneWa'); if (a && wa) a.href = wa; if (done) done.classList.add('open'); };
  var closeDone = function () { if (done) done.classList.remove('open'); };
  $$('[data-done-close]').forEach(function (b) { b.addEventListener('click', closeDone); });
  if (done) done.addEventListener('click', function (e) { if (e.target === done) closeDone(); });

  var send = function (form, items, errBox, onOk) {
    var fd = new FormData(form), data = { items: items, lang: D.lang, page: location.pathname };
    fd.forEach(function (v, k) { data[k] = v; });
    if (!String(data.name || '').trim() || !String(data.phone || '').trim()) { showErr(errBox, T.need); return; }
    var btn = form.querySelector('button[type=submit]'), label = btn.textContent;
    btn.disabled = true; btn.textContent = T.sending; showErr(errBox, '');
    fetch(D.api, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data), credentials: 'same-origin' })
      .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
      .then(function (r) {
        if (!r.ok || !r.j.ok) throw new Error(r.j.error || T.err);
        var stamp = form.querySelector('[name=stamp]'), sv = stamp ? stamp.value : '';
        form.reset(); if (stamp) stamp.value = sv; onOk && onOk(); openDone(r.j.wa);
      })
      .catch(function (e) { showErr(errBox, e.message || T.err); })
      .then(function () {
        btn.disabled = false; btn.textContent = label;
        var ts = form.querySelector('.cf-turnstile');
        if (ts && window.turnstile) { try { window.turnstile.reset(ts); } catch (x) {} }
      });
  };
  var showErr = function (box, msg) { if (!box) return; box.textContent = msg; box.hidden = !msg; };

  var cartForm = $('#cartForm');
  if (cartForm) cartForm.addEventListener('submit', function (e) {
    e.preventDefault();
    if (!cart.length) { showErr($('#cartErr'), T.empty); return; }
    send(cartForm, cart.map(function (i) { return { id: i.id, qty: i.qty, opt: i.opt }; }), $('#cartErr'), function () { cart = []; save(); closeCart(); });
  });

  /* ---------- məhsul səhifəsi ---------- */
  var order = $('#order'), cur = null;
  if (order) {
    try { cur = JSON.parse(order.getAttribute('data-product')); } catch (e) {}
    var qty = $('#qty'), opt = $('#opt'), wa = $('#waBtn');
    var waText = function () {
      return T.hello + '\n' + cur.t + (opt ? ' (' + opt.value + ')' : '') + ' — ' + (parseInt(qty.value, 10) || 1) + ' ' + T.pcs + '\n' + location.origin + cur.u;
    };
    var syncWa = function () { if (wa) wa.href = 'https://wa.me/' + D.wa + '?text=' + encodeURIComponent(waText()); };
    $$('[data-q]', order).forEach(function (b) {
      b.addEventListener('click', function () { qty.value = Math.max(1, (parseInt(qty.value, 10) || 1) + (+b.getAttribute('data-q'))); syncWa(); });
    });
    qty.addEventListener('input', syncWa); if (opt) opt.addEventListener('change', syncWa); syncWa();
    $('#addBtn').addEventListener('click', function () { add(cur, qty.value, opt ? opt.value : ''); });

  }

  /* ---------- məhsul qalereyası: oxlar, miniatürlər, sürüşdürmə, tam ekran ---------- */
  var gal = $('#gal');
  if (gal) {
    var imgs = [];
    try { imgs = JSON.parse(gal.getAttribute('data-images')) || []; } catch (e) {}
    var n = imgs.length, gi = 0;
    var gMain = $('#galMain'), gCount = $('#galCount'), gThumbs = $$('#galThumbs button'), opener = $('#galOpen');
    var lb = $('#lb'), lbImg = $('#lbImg'), lbCount = $('#lbCount'), lbThumbs = $$('#lbThumbs button'), lbStage = $('#lbStage');
    var lbOpen = function () { return !!lb && lb.classList.contains('open'); };

    var preload = function (i) { if (n > 1) { var im = new Image(); im.src = imgs[(i % n + n) % n]; } };
    var centerThumb = function (list) {
      var b = list[gi]; if (!b) return;
      var box = b.parentNode;
      box.scrollTo({ left: b.offsetLeft - box.clientWidth / 2 + b.clientWidth / 2, behavior: 'smooth' });
    };
    var show = function (i) {
      if (!n) return;
      gi = (i % n + n) % n;
      gMain.src = imgs[gi];
      var label = (gi + 1) + ' / ' + n;
      if (gCount) gCount.textContent = label;
      if (lbCount) lbCount.textContent = n > 1 ? label : '';
      if (lbOpen()) lbImg.src = imgs[gi];
      gThumbs.forEach(function (b, k) { b.classList.toggle('on', k === gi); });
      lbThumbs.forEach(function (b, k) { b.classList.toggle('on', k === gi); });
      centerThumb(gThumbs);
      if (lbOpen()) centerThumb(lbThumbs);
      preload(gi + 1); preload(gi - 1);
    };

    $$('[data-gal]', gal).forEach(function (b) {
      b.addEventListener('click', function () { show(gi + (+b.getAttribute('data-gal'))); });
    });
    gThumbs.forEach(function (b, k) { b.addEventListener('click', function () { show(k); }); });

    /* Barmaqla sürüşdürmə (telefon) */
    var swipe = function (el) {
      if (!el || n < 2) return;
      var x0 = null, y0 = 0;
      el.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; y0 = e.touches[0].clientY; }, { passive: true });
      el.addEventListener('touchend', function (e) {
        if (x0 === null) return;
        var dx = e.changedTouches[0].clientX - x0, dy = e.changedTouches[0].clientY - y0;
        x0 = null;
        if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy) * 1.2) {
          el.setAttribute('data-swiped', '1');
          setTimeout(function () { el.removeAttribute('data-swiped'); }, 350);
          show(gi + (dx < 0 ? 1 : -1));
        }
      }, { passive: true });
    };
    swipe(gal.querySelector('.main'));
    swipe(lbStage);

    /* Tam ekran baxış */
    if (lb && opener) {
      var openLb = function () {
        if (gal.querySelector('.main').hasAttribute('data-swiped')) return;   // sürüşdürmə klik sayılmasın
        lb.classList.add('open');
        lb.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        lbImg.src = imgs[gi];
        show(gi);
        var x = lb.querySelector('.lb-x'); if (x) x.focus();
      };
      /* Fokus yalnız klaviatura ilə bağlananda şəklə qaytarılır (siçan istifadəçisində çərçivə qalmasın) */
      var closeLb = function (byKeyboard) {
        if (!lbOpen()) return;
        lb.classList.remove('open');
        lb.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        if (byKeyboard === true) opener.focus(); else if (document.activeElement && document.activeElement.blur) document.activeElement.blur();
      };
      opener.addEventListener('click', openLb);
      $$('[data-lb]', lb).forEach(function (b) {
        b.addEventListener('click', function (e) { e.stopPropagation(); show(gi + (+b.getAttribute('data-lb'))); });
      });
      lbThumbs.forEach(function (b, k) { b.addEventListener('click', function (e) { e.stopPropagation(); show(k); }); });
      $$('[data-lb-close]', lb).forEach(function (b) { b.addEventListener('click', function (e) { closeLb(e.detail === 0); }); });
      /* Şəklin kənarına klik — bağla */
      lb.addEventListener('click', function (e) {
        if ((e.target === lb || e.target === lbStage) && !lbStage.hasAttribute('data-swiped')) closeLb();
      });
      document.addEventListener('keydown', function (e) {
        if (!lbOpen()) return;
        if (e.key === 'Escape') { closeLb(true); }
        else if (e.key === 'ArrowLeft') { e.preventDefault(); show(gi - 1); }
        else if (e.key === 'ArrowRight') { e.preventDefault(); show(gi + 1); }
        else if (e.key === 'Tab') {                         // fokus pəncərədən çıxmasın
          var f = $$('button', lb).filter(function (el) { return el.offsetParent !== null; });
          if (!f.length) return;
          var first = f[0], last = f[f.length - 1];
          if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
          else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        }
      });
    }
  }
  $$('.ptabs [data-tab]').forEach(function (b) {
    b.addEventListener('click', function () {
      var k = b.getAttribute('data-tab');
      $$('.ptabs [data-tab]').forEach(function (x) { x.classList.toggle('on', x === b); });
      $$('.ptabs [data-pane]').forEach(function (p) { p.classList.toggle('on', p.getAttribute('data-pane') === k); });
    });
  });

  /* Ümumi sorğu formaları (əlaqə, ana səhifə, məhsul) */
  $$('[data-request-form]').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      e.preventDefault();
      var items = [];
      if (cur) { var q = $('#qty'), o = $('#opt'); items.push({ id: cur.id, qty: q ? q.value : 1, opt: o ? o.value : '' }); }
      send(f, items, f.querySelector('.fmsg'));
    });
  });

  /* WhatsApp düymələri: keçid admin → «Заявки»-yə də yazılır (forma sonrası #doneWa artıq yazılıb) */
  var waLog = function (e) {
    if (e.type === 'auxclick' && e.button !== 1) return;
    var a = e.target.closest && e.target.closest('a[href*="wa.me/"]');
    if (!a || a.id === 'doneWa' || !window.fetch) return;
    var text = '', st = $('[name=stamp]'), items = [];
    try { text = new URL(a.href).searchParams.get('text') || ''; } catch (x) {}
    if (a.id === 'waBtn' && cur) { var q = $('#qty'), o = $('#opt'); items.push({ id: cur.id, qty: q ? q.value : 1, opt: o ? o.value : '' }); }
    try {
      fetch(D.api, {
        method: 'POST', keepalive: true, credentials: 'same-origin', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ kind: 'wa', lang: D.lang, page: location.pathname, stamp: st ? st.value : '', text: text.slice(0, 1000), items: items })
      }).catch(function () {});
    } catch (x) {}
  };
  document.addEventListener('click', waLog);
  document.addEventListener('auxclick', waLog);

  /* ---------- OSB kalkulyatoru ---------- */
  var ct = $('#ct'), ca = $('#ca'), cn = $('#cn'), cAdd = $('#cAdd');
  if (ct && ca && cn) {
    var SHEET = 1.22 * 2.44, COL = { 9: 1, 12: 2, 15: 3, 18: 4 }, map = {};
    try { map = JSON.parse(cAdd.getAttribute('data-osb')) || {}; } catch (e) {}
    var calc = function () {
      var n = Math.ceil((parseFloat(ca.value) || 0) * 1.1 / SHEET);
      cn.textContent = n;
      var c = COL[ct.value];
      $$('#cmp tr').forEach(function (tr) { Array.prototype.forEach.call(tr.children, function (td, i) { td.classList.toggle('col-on', i === c); }); });
      return n;
    };
    ct.addEventListener('change', calc); ca.addEventListener('input', calc);
    $$('#cmp th[data-c]').forEach(function (th) {
      th.style.cursor = 'pointer';
      th.addEventListener('click', function () { ct.value = [9, 12, 15, 18][th.getAttribute('data-c') - 1]; calc(); });
    });
    cAdd.addEventListener('click', function () {
      var n = calc(), p = map[ct.value];
      if (p && n > 0) add(p, n, ''); else location.href = D.shop;
    });
    calc();
  }

  render();
})();
