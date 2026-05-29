/**
 * mapa.js - Leaflet cargado lazy con IntersectionObserver
 * Solo descarga los 24KB de Leaflet cuando el mapa entra en viewport.
 * Concepto DAW 2º: IntersectionObserver API + carga dinámica de recursos.
 */
(function () {
    const contenedorMapa = document.getElementById('mapa');
    if (!contenedorMapa) return;

    let mapaIniciado = false;

    function cargarMapa() {
        if (mapaIniciado) return;
        mapaIniciado = true;

        // 1. CSS de Leaflet de forma dinámica
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        link.integrity = 'sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=';
        link.crossOrigin = '';
        document.head.appendChild(link);

        // 2. JS de Leaflet de forma dinámica
        const script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.integrity = 'sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=';
        script.crossOrigin = '';
        script.onload = iniciarMapa;
        document.body.appendChild(script);
    }

    function iniciarMapa() {
        const lat  = 36.6961;
        const lng  = -4.4699;
        const zoom = 15;

        const mapa = L.map('mapa').setView([lat, lng], zoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(mapa);

        const marcador = L.marker([lat, lng]).addTo(mapa);

        // Popup con createElement para evitar XSS
        const popup = document.createElement('div');
        popup.style.textAlign = 'center';

        const titulo = document.createElement('strong');
        titulo.textContent = 'Villalobos Logística';

        const dir = document.createElement('p');
        dir.style.cssText = 'margin:4px 0 0;color:#64748b';
        dir.textContent = 'Pol. Guadalhorce, s/n. Málaga';

        popup.appendChild(titulo);
        popup.appendChild(dir);

        marcador.bindPopup(popup).openPopup();
    }

    // IntersectionObserver: carga Leaflet solo cuando el mapa llega a viewport
    // rootMargin 300px = empieza a cargar 300px antes de que sea visible
    const observer = new IntersectionObserver(function (entries) {
        if (entries[0].isIntersecting) {
            observer.disconnect();
            cargarMapa();
        }
    }, { rootMargin: '300px' });

    observer.observe(contenedorMapa);
})();
