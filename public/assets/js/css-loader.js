// css-loader.js — Activa hojas de estilo precargadas con rel="preload".
// Al ser un archivo externo, cumple con la CSP (script-src 'self').
// Se ejecuta síncronamente en el <head> para activar los CSS lo antes posible.
(function () {
    var links = document.querySelectorAll('link[rel="preload"][as="style"]');
    for (var i = 0; i < links.length; i++) {
        links[i].rel = 'stylesheet';
    }
})();
