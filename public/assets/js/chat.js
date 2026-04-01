// public/assets/js/chat.js
// Widget de chat flotante para captar leads en la web pública.
// Flujo: nombre → contacto → servicio → envía a contacto.php

document.addEventListener("DOMContentLoaded", () => {

    // ---------------------------------------------------------
    // 1. NODOS DEL DOM
    // ---------------------------------------------------------
    const boton       = document.getElementById("chatBoton");
    const panel       = document.getElementById("chatPanel");
    const mensajesDiv = document.getElementById("chatMensajes");
    const input       = document.getElementById("chatInput");
    const botonEnviar = document.getElementById("chatEnviar");
    const iconoAbrir  = document.getElementById("chatIconoAbierto");
    const iconoCerrar = document.getElementById("chatIconoCerrado");

    if (!boton || !panel) return; // Seguridad: si no estamos en index.html, salimos

    // ---------------------------------------------------------
    // 2. ESTADO DE LA CONVERSACIÓN
    // Estado es simplemente un número que indica en qué paso estamos.
    // 0 = esperando nombre, 1 = esperando contacto, 2 = esperando servicio, 3 = enviado
    // ---------------------------------------------------------
    let estado = 0;
    let datos  = { nombre: "", contacto: "", servicio: "" };

    // Preguntas en orden — el estado es el índice de este array
    const preguntas = [
        "¡Hola! 👋 ¿Cómo te llamas?",
        "¿Cuál es tu email o teléfono para contactarte?",
        "¿Qué servicio necesitas? (transporte, almacenaje, urgente...)"
    ];

    // ---------------------------------------------------------
    // 3. ABRIR / CERRAR EL PANEL
    // ---------------------------------------------------------
    boton.addEventListener("click", () => {
        const estaAbierto = !panel.hidden;

        if (estaAbierto) {
            // Cerrar
            panel.hidden = true;
            iconoAbrir.hidden  = false;
            iconoCerrar.hidden = true;
        } else {
            // Abrir — si es la primera vez, lanzamos la primera pregunta
            panel.hidden = false;
            iconoAbrir.hidden  = true;
            iconoCerrar.hidden = false;

            if (mensajesDiv.children.length === 0) {
                setTimeout(() => agregarMensajeBot(preguntas[0]), 400);
            }

            // Poner el foco en el input para que el usuario pueda escribir ya
            setTimeout(() => input.focus(), 500);
        }
    });

    // ---------------------------------------------------------
    // 4. ENVIAR RESPUESTA (botón o tecla Enter)
    // ---------------------------------------------------------
    botonEnviar.addEventListener("click", procesarRespuesta);

    input.addEventListener("keydown", (e) => {
        if (e.key === "Enter") procesarRespuesta();
    });

    // ---------------------------------------------------------
    // 5. LÓGICA PRINCIPAL — procesar lo que escribe el usuario
    // Esta función se llama cada vez que el usuario envía algo.
    // Dependiendo del estado, guarda el dato y hace la siguiente pregunta.
    // ---------------------------------------------------------
    function procesarRespuesta() {
        const texto = input.value.trim();
        if (!texto || estado >= 3) return; // No procesar si está vacío o ya terminamos

        // Mostrar lo que escribió el usuario en el chat (burbuja derecha)
        agregarMensajeUsuario(texto);
        input.value = "";

        // Guardar el dato según en qué estado estamos
        if (estado === 0) {
            datos.nombre   = texto;
        } else if (estado === 1) {
            datos.contacto = texto;
        } else if (estado === 2) {
            datos.servicio = texto;
        }

        // Avanzar al siguiente estado
        estado++;

        if (estado < preguntas.length) {
            // Todavía hay preguntas — mostrar la siguiente con un pequeño retraso (parece más natural)
            setTimeout(() => agregarMensajeBot(preguntas[estado]), 600);
        } else {
            // Ya tenemos todos los datos → enviar
            setTimeout(() => {
                agregarMensajeBot("Un momento, estoy registrando tu solicitud... ⏳");
                enviarAlServidor();
            }, 600);
        }
    }

    // ---------------------------------------------------------
    // 6. ENVIAR DATOS AL SERVIDOR (fetch a contacto.php)
    // Reutilizamos el mismo endpoint del formulario de contacto.
    // Adaptamos los campos: nombre, email (usamos contacto), mensaje (usamos servicio)
    // ---------------------------------------------------------
    function enviarAlServidor() {
        estado = 3; // Bloqueamos más envíos
        input.disabled       = true;
        botonEnviar.disabled = true;

        const payload = {
            nombre:  datos.nombre,
            email:   datos.contacto,   // contacto.php espera "email"
            mensaje: "CHAT WIDGET — Servicio solicitado: " + datos.servicio
        };

        fetch("../backend/api/contacto.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(respuesta => {
            if (respuesta.ok) {
                agregarMensajeBot(
                    "✅ ¡Gracias, " + datos.nombre + "! Hemos recibido tu solicitud. " +
                    "Nos pondremos en contacto contigo pronto. 🚛"
                );
            } else {
                agregarMensajeBot("❌ Hubo un problema al enviar. Puedes llamarnos al 630 518 441.");
                estado = 2; // Permitir reintentar
                input.disabled       = false;
                botonEnviar.disabled = false;
            }
        })
        .catch(() => {
            agregarMensajeBot("❌ Error de conexión. Puedes llamarnos al 630 518 441.");
            estado = 2;
            input.disabled       = false;
            botonEnviar.disabled = false;
        });
    }

    // ---------------------------------------------------------
    // 7. HELPERS — añadir burbujas al chat
    // Dos funciones simples: una para mensajes del bot (izquierda), otra del usuario (derecha)
    // Usamos textContent para evitar XSS (nunca innerHTML con datos del usuario)
    // ---------------------------------------------------------
    function agregarMensajeBot(texto) {
        const burbuja = document.createElement("div");
        burbuja.className = "chat-burbuja chat-burbuja-bot";
        burbuja.textContent = texto;
        mensajesDiv.appendChild(burbuja);
        mensajesDiv.scrollTop = mensajesDiv.scrollHeight; // scroll automático
    }

    function agregarMensajeUsuario(texto) {
        const burbuja = document.createElement("div");
        burbuja.className = "chat-burbuja chat-burbuja-usuario";
        burbuja.textContent = texto;
        mensajesDiv.appendChild(burbuja);
        mensajesDiv.scrollTop = mensajesDiv.scrollHeight;
    }

});
