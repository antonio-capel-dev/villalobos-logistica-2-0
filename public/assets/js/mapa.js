// public/assets/js/mapa.js

document.addEventListener("DOMContentLoaded", () => {
    
    // ---------------------------------------------------------
    // 1. VERIFICACIÓN DEL CONTENEDOR DOM
    // ---------------------------------------------------------
    const contenedorMapa = document.getElementById('mapa');
    
    // Solo ejecutamos la lógica si existe el div y hemos cargado la librería L (Leaflet)
    if (contenedorMapa && typeof L !== 'undefined') {
        
        // Coordenadas reales: Polígono Guadalhorce, Málaga (aproximadas para sede logística)
        const latitud = 36.6961;
        const longitud = -4.4699;
        const nivelZoom = 15;

        // ---------------------------------------------------------
        // 2. INICIALIZACIÓN DE LA INSTANCIA DE MAPA
        // ---------------------------------------------------------
        // L.map('mapa') requiere un identificador válido en el HTML
        const instanciaMapa = L.map('mapa').setView([latitud, longitud], nivelZoom);

        // ---------------------------------------------------------
        // 3. CAPA BASE (TILES) - OPENSTREETMAP
        // ---------------------------------------------------------
        // Consumimos el servicio de renderizado de teselas público
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(instanciaMapa);

        // ---------------------------------------------------------
        // 4. MARCADOR VISUAL (CHINCHETA)
        // ---------------------------------------------------------
        const chincheta = L.marker([latitud, longitud]).addTo(instanciaMapa);

        // ---------------------------------------------------------
        // 5. VENTANA EMERGENTE INFORMATIVA (POPUP)
        // ---------------------------------------------------------
        // Evitamos innerHTML y optamos por creación segura de nodos DOM
        const contenedorVentana = document.createElement("div");
        contenedorVentana.style.textAlign = "center";
        
        const tituloNegrita = document.createElement("strong");
        tituloNegrita.textContent = "Villalobos Logística 2.0";
        
        const parrafoSede = document.createElement("p");
        parrafoSede.style.margin = "5px 0 0 0";
        parrafoSede.textContent = "Sede Principal";

        const parrafoDireccion = document.createElement("p");
        parrafoDireccion.style.margin = "0";
        parrafoDireccion.style.color = "#64748b";
        parrafoDireccion.textContent = "Pol. Guadalhorce, s/n. Málaga";

        // Ensamblaje de los nodos en la ventana
        contenedorVentana.appendChild(tituloNegrita);
        contenedorVentana.appendChild(parrafoSede);
        contenedorVentana.appendChild(parrafoDireccion);

        // Asignamos el HTML Node Tree y abrimos por defecto
        chincheta.bindPopup(contenedorVentana).openPopup();

    } else {
        console.warn("Leaflet no cargado o contenedor id='map' no encontrado en el DOM de esta URL.");
    }
});
