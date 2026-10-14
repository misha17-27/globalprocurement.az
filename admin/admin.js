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

  // Parolu göstər / gizlət — hər parol sahəsinin yanında
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
  });
})();
