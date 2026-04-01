// public/assets/js/auth.js

document.addEventListener("DOMContentLoaded", () => {
    
    // ---------------------------------------------------------
    // 1. INICIALIZACIÓN DEL FORMULARIO DE ACCESO (LOGIN)
    // ---------------------------------------------------------
    const formularioLogin = document.getElementById("formularioLogin");
    
    // Si no estamos en la página de login, abortamos la ejecución
    if (!formularioLogin) return;

    // Obtenemos los nodos del DOM respetando los IDs originales (requisito)
    const botonEnviar = document.getElementById("botonAcceso");
    const contenedorError = document.getElementById("mensajeError");
    const contenedorExito = document.getElementById("mensajeExito");
    const entradaCorreo = document.getElementById("email");
    const entradaClave = document.getElementById("password");

    formularioLogin.addEventListener("submit", function(evento) {
        // Evitamos la recarga tradicional de la página
        evento.preventDefault(); 
        
        // Limpiamos alertas previas apoyándonos en utilidades.js
        ocultarMensaje(contenedorError);
        ocultarMensaje(contenedorExito);
        
        // Obtenemos y limpiamos los valores de entrada
        const valorCorreo = entradaCorreo.value.trim();
        const valorClave = entradaClave.value.trim();

        // A) Validación de Cliente (Front-End)
        if (!valorCorreo || !valorClave) {
            mostrarMensaje(contenedorError, "error", "Por favor, complete todos los campos de acceso.");
            return;
        }

        // B) Interfaz de Usuario: Estado de carga
        const textoBotonOriginal = botonEnviar.innerHTML;
        botonEnviar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Validando credenciales...';
        botonEnviar.disabled = true;

        // C) Construcción del payload JSON (las claves coinciden con lo esperado por PHP)
        const datosParaEnviar = {
            email: valorCorreo,
            password: valorClave
        };

        // D) Petición Asíncrona (Fetch API) a nuestro Backend
        fetch('../backend/api/auth.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json' 
            },
            body: JSON.stringify(datosParaEnviar)
        })
        .then(respuestaHTTP => {
            // Evaluamos la respuesta global, empaquetando el HTTP status con el payload
            return respuestaHTTP.json().then(datos => ({
                estadoHTTP: respuestaHTTP.status,
                datosAPI: datos
            }));
        })
        .then(respuestaServidor => {
            // Comprobamos la lógica de negocio ("ok" definido en la API)
            if (respuestaServidor.datosAPI.ok === true) {
                // Éxito: Mensaje de confirmación y redirección
                mostrarMensaje(contenedorExito, "exito", "¡Credenciales correctas! Accediendo al panel...");
                
                // Redirigir físicamente al dashboard tras una breve pausa (Mejora UX)
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 1000);
            } else {
                // Error: Credenciales incorrectas o fallo de validación
                mostrarMensaje(contenedorError, "error", respuestaServidor.datosAPI.error || "Se denegó el acceso por credenciales inválidas.");
                
                // Restauramos el botón interactivo
                botonEnviar.innerHTML = textoBotonOriginal;
                botonEnviar.disabled = false;
                
                // Por seguridad, limpiamos el campo de la contraseña y le damos el foco
                entradaClave.value = '';
                entradaClave.focus();
            }
        })
        .catch(errorRed => {
            // Restauramos el botón en caso de crash de red total
            botonEnviar.innerHTML = textoBotonOriginal;
            botonEnviar.disabled = false;
            
            console.error("Fallo de conexión en Fetch (Login):", errorRed);
            mostrarMensaje(contenedorError, "error", "Error de conexión con el servidor. Inténtelo más tarde.");
        });
    });
});
