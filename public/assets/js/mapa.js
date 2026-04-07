document.addEventListener("DOMContentLoaded", () => {

    const contenedorMapa = document.getElementById('mapa');
    if (!contenedorMapa || typeof L === 'undefined') return;

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
    const popup = document.createElement("div");
    popup.style.textAlign = "center";

    const titulo = document.createElement("strong");
    titulo.textContent = "Villalobos Logística";

    const sede = document.createElement("p");
    sede.style.margin = "4px 0 0";
    sede.textContent = "Sede principal";

    const dir = document.createElement("p");
    dir.style.margin = "0";
    dir.style.color = "#64748b";
    dir.textContent = "Pol. Guadalhorce, s/n. Málaga";

    popup.appendChild(titulo);
    popup.appendChild(sede);
    popup.appendChild(dir);

    marcador.bindPopup(popup).openPopup();
});
