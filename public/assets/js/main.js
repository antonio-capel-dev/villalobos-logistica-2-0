// public/assets/js/main.js

document.addEventListener("DOMContentLoaded", () => {
    
    // ---------------------------------------------------------
    // 1. NAVEGACIÓN MÓVIL (MENÚ HAMBURGUESA)
    // ---------------------------------------------------------
    const botonMenuMovil = document.querySelector('.mobile-menu-toggle');
    const navegacionPrincipal = document.querySelector('.main-nav');
    
    if (botonMenuMovil && navegacionPrincipal) {
        botonMenuMovil.addEventListener('click', () => {
            navegacionPrincipal.classList.toggle('active');
        });
    }

    // ---------------------------------------------------------
    // 2. FORMULARIO ASÍNCRONO DE CONTACTO
    // ---------------------------------------------------------
    const formularioContacto = document.getElementById("formularioContacto");
    
    if (formularioContacto) {
        // Obtenemos los nodos del DOM respetando los IDs originales (requisito)
        const contenedorMensaje = document.getElementById("mensajeContacto");
        const botonEnviar = document.getElementById("botonEnviarContacto");

        formularioContacto.addEventListener("submit", function(evento) {
            // Evitamos la recarga tradicional de la página
            evento.preventDefault(); 

            // Limpiamos alertas previas apoyándonos en utilidades.js
            ocultarMensaje(contenedorMensaje);

            // Obtenemos y limpiamos los valores de entrada
            const valorNombre = document.getElementById("nombre").value.trim();
            const valorCorreo = document.getElementById("correo").value.trim();
            const valorMensaje = document.getElementById("detalleMensaje").value.trim();

            // A) Validación de Cliente (Front-End)
            if (!valorNombre || !valorCorreo || !valorMensaje) {
                mostrarMensaje(contenedorMensaje, "error", "Por favor, indique todos los campos obligatorios.");
                return;
            }

            // Validación de formato de email con Expresión Regular
            const patronCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!patronCorreo.test(valorCorreo)) {
                mostrarMensaje(contenedorMensaje, "error", "El correo introducido no tiene un formato válido.");
                return;
            }

            // B) Interfaz de Usuario: Estado de carga
            const textoBotonOriginal = botonEnviar.innerHTML;
            botonEnviar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            botonEnviar.disabled = true;

            // C) Construcción del payload JSON
            const datosParaEnviar = {
                nombre: valorNombre,
                email: valorCorreo,
                mensaje: valorMensaje
            };

            // D) Petición Asíncrona (Fetch API)
            fetch('../backend/api/contacto.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datosParaEnviar)
            })
            .then(respuestaHTTP => {
                // Interceptamos fallos de protocolo no controlados por la API
                if (!respuestaHTTP.ok && respuestaHTTP.status !== 400 && respuestaHTTP.status !== 500) {
                    throw new Error("Respuesta inesperada del servidor: " + respuestaHTTP.status);
                }
                return respuestaHTTP.json();
            })
            .then(respuestaServidor => {
                // Restauramos el botón
                botonEnviar.innerHTML = textoBotonOriginal;
                botonEnviar.disabled = false;

                // Comprobamos la lógica de negocio (la propiedad "ok" definida en PHP)
                if (respuestaServidor.ok) {
                    mostrarMensaje(contenedorMensaje, "exito", "¡Gracias! Su mensaje fue recibido y le contactaremos pronto.");
                    formularioContacto.reset(); // Vaciamos el formulario
                } else {
                    mostrarMensaje(contenedorMensaje, "error", respuestaServidor.error || "Ocurrió un error en la validación.");
                }
            })
            .catch(errorRed => {
                // Restauramos el botón en caso de crash de red
                botonEnviar.innerHTML = textoBotonOriginal;
                botonEnviar.disabled = false;
                
                console.error("Fallo de conexión en Fetch:", errorRed);
                mostrarMensaje(contenedorMensaje, "error", "Error de conexión con el servidor. Inténtelo más tarde.");
            });
        });
    }
});
