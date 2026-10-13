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
})();
