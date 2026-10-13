/* Google Analytics 4 — ID admin paneldən gəlir (data-id) */
(function () {
  var s = document.currentScript, id = s && s.getAttribute('data-id');
  if (!id) return;
  window.dataLayer = window.dataLayer || [];
  function gtag() { dataLayer.push(arguments); }
  gtag('js', new Date());
  gtag('config', id);
})();
