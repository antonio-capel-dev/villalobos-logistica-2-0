document.addEventListener("DOMContentLoaded", () => {

    const formularioLogin = document.getElementById("formularioLogin");
    if (!formularioLogin) return;

    const botonEnviar     = document.getElementById("botonAcceso");
    const contenedorError = document.getElementById("mensajeError");
    const contenedorExito = document.getElementById("mensajeExito");
    const entradaCorreo   = document.getElementById("email");
    const entradaClave    = document.getElementById("password");

    formularioLogin.addEventListener("submit", function(evento) {
        evento.preventDefault();

        ocultarMensaje(contenedorError);
        ocultarMensaje(contenedorExito);

        const valorCorreo = entradaCorreo.value.trim();
        const valorClave  = entradaClave.value.trim();

        if (!valorCorreo || !valorClave) {
            mostrarMensaje(contenedorError, "error", "Rellena todos los campos.");
            return;
        }

        const textoBotonOriginal = botonEnviar.innerHTML;
        botonEnviar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Comprobando...';
        botonEnviar.disabled = true;

        fetch('../backend/api/auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: valorCorreo, password: valorClave })
        })
        .then(res => res.json().then(datos => ({ status: res.status, datos })))
        .then(({ datos }) => {
            if (datos.ok) {
                mostrarMensaje(contenedorExito, "exito", "Acceso correcto. Redirigiendo...");
                setTimeout(() => { window.location.href = 'dashboard.php'; }, 1000);
            } else {
                mostrarMensaje(contenedorError, "error", datos.error || "Credenciales incorrectas.");
                botonEnviar.innerHTML = textoBotonOriginal;
                botonEnviar.disabled = false;
                entradaClave.value = '';
                entradaClave.focus();
            }
        })
        .catch(() => {
            botonEnviar.innerHTML = textoBotonOriginal;
            botonEnviar.disabled = false;
            mostrarMensaje(contenedorError, "error", "Error de conexión. Inténtalo más tarde.");
        });
    });
});
