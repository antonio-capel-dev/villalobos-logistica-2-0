// public/assets/js/utilidades.js

/**
 * Muestra un mensaje en pantalla de forma segura y temporal.
 * @param {HTMLElement} contenedor - Elemento DOM donde mostrar el mensaje.
 * @param {string} tipo - "exito" o "error".
 * @param {string} texto - El contenido del mensaje limpo (XSS prevent).
 */
function mostrarMensaje(contenedor, tipo, texto) {
    if (!contenedor) return;
    
    // Asignación segura frente a XSS
    contenedor.textContent = texto; 
    
    // Mapeo a las clases CSS refactorizadas en castellano
    if (tipo === "exito" || tipo === "success") {
        contenedor.className = "mensaje mensaje-exito";
    } else {
        contenedor.className = "mensaje mensaje-error";
    }
    
    contenedor.style.display = "block";

    // Auto-colapso tras 5 segundos
    setTimeout(() => { 
        if (contenedor) contenedor.style.display = "none"; 
    }, 5000); 
}

/**
 * Oculta y limpia un contenedor de mensajes.
 * @param {HTMLElement} contenedor - Elemento DOM a limpiar.
 */
function ocultarMensaje(contenedor) {
    if(contenedor) {
        contenedor.style.display = "none";
        contenedor.textContent = "";
    }
}

/**
 * Crea una celda TD de tabla de forma segura.
 * Extraída para evitar cierres (closures) ineficientes en bucles.
 * @param {string|number} texto - Contenido de la celda.
 * @returns {HTMLElement} - Nodo TD sanitizado.
 */
function crearCelda(texto) {
    const celda = document.createElement("td");
    celda.textContent = texto || '-'; 
    return celda;
}
