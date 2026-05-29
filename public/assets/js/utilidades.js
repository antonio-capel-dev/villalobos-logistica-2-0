function mostrarMensaje(contenedor, tipo, texto) {
    if (!contenedor) return;
    contenedor.textContent = texto;
    contenedor.className = (tipo === "exito" || tipo === "success")
        ? "mensaje mensaje-exito"
        : "mensaje mensaje-error";
    contenedor.style.display = "block";
    setTimeout(() => {
        if (contenedor) contenedor.style.display = "none";
    }, 5000);
}

function ocultarMensaje(contenedor) {
    if (contenedor) {
        contenedor.style.display = "none";
        contenedor.textContent = "";
    }
}

// Crea una celda td con textContent para evitar XSS
function crearCelda(texto) {
    const celda = document.createElement("td");
    celda.textContent = texto || '-';
    return celda;
}
