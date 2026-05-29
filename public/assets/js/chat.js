document.addEventListener("DOMContentLoaded", () => {

    const boton       = document.getElementById("chatBoton");
    const panel       = document.getElementById("chatPanel");
    const cerrarBtn   = document.getElementById("chatCerrar");
    const reiniciarBtn= document.getElementById("chatReiniciar");
    const mensajesDiv = document.getElementById("chatMensajes");
    const input       = document.getElementById("chatInput");
    const botonEnviar = document.getElementById("chatEnviar");
    const badge       = document.getElementById("chatBadge");
    const callout     = document.getElementById("chatCallout");
    const calloutX    = document.getElementById("chatCalloutCerrar");

    if (!boton || !panel) return;

    const PASOS = { MENU: 0, SERVICIO: 1, RUTA: 2, NOMBRE: 3, CONTACTO: 4, FIN: 5 };
    let paso  = PASOS.MENU;
    let datos = { nombre: "", contacto: "", servicio: "", ruta: "" };

    function resetConversacion() {
        paso  = PASOS.MENU;
        datos = { nombre: "", contacto: "", servicio: "", ruta: "" };
        mensajesDiv.innerHTML = "";
        desbloquearInput();
        input.placeholder = "Escribe aquí...";
        setTimeout(() => botDice("¡Hola! Soy el asistente de Villalobos Logística. ¿En qué te puedo ayudar?"), 400);
        setTimeout(() => mostrarChipsMenu(), 1100);
        setTimeout(() => input.focus(), 500);
    }

    // ─── Persistencia del callout ────────────────────────────────────────────
    // localStorage con TTL de 7 días: si el usuario ya vio/cerró el chat,
    // no le molestamos de nuevo durante una semana.
    // sessionStorage (anterior) se borraba al cerrar el tab — molestaba en cada visita.

    const CHAT_VISTO_KEY = "vll_chatVisto";
    const CHAT_TTL_MS    = 7 * 24 * 60 * 60 * 1000; // 7 días

    function marcarChatVisto() {
        try {
            localStorage.setItem(CHAT_VISTO_KEY, String(Date.now()));
        } catch (_) { /* modo incógnito con storage bloqueado: silenciar */ }
        sessionStorage.setItem("chatVisto", "1"); // mantener compatibilidad
    }

    function chatYaVisto() {
        try {
            const ts = parseInt(localStorage.getItem(CHAT_VISTO_KEY) || "0", 10);
            if (ts && Date.now() - ts < CHAT_TTL_MS) return true;
        } catch (_) {}
        return !!sessionStorage.getItem("chatVisto");
    }
    // ─────────────────────────────────────────────────────────────────────────

    function abrirChat() {
        panel.hidden = false;
        if (callout) callout.hidden = true;
        if (badge)   badge.classList.add("oculto");
        if (mensajesDiv.children.length === 0) resetConversacion();
        setTimeout(() => input.focus(), 500);
        marcarChatVisto();
    }

    function cerrarChat() { panel.hidden = true; }

    boton.addEventListener("click", () => panel.hidden ? abrirChat() : cerrarChat());
    if (cerrarBtn)    cerrarBtn.addEventListener("click", cerrarChat);
    if (reiniciarBtn) reiniciarBtn.addEventListener("click", resetConversacion);

    if (callout && !chatYaVisto()) {
        setTimeout(() => { if (panel.hidden) callout.hidden = false; }, 7000);
    }
    if (calloutX) calloutX.addEventListener("click", () => {
        callout.hidden = true;
        marcarChatVisto();
    });
    if (callout) callout.addEventListener("click", e => {
        if (e.target === calloutX) return;
        callout.hidden = true;
        abrirChat();
    });

    botonEnviar.addEventListener("click", procesar);
    input.addEventListener("keydown", e => { if (e.key === "Enter") procesar(); });

    function procesar() {
        const texto = input.value.trim();
        if (!texto || paso >= PASOS.FIN) return;
        input.value = "";
        manejarRespuesta(texto);
    }

    function manejarRespuesta(texto) {
        usuarioDice(texto);

        if (paso === PASOS.MENU) {
            const lower = texto.toLowerCase();
            if (lower.includes("asesor") || lower.includes("hablar") || lower.includes("llamar")) {
                paso = PASOS.FIN;
                bloquearInput();
                botDiceConEspera("Claro, puedes llamarnos ahora al 630 518 441 o al 625 038 039. Estaremos encantados de atenderte.");
            } else if (lower.includes("servicio") || lower.includes("info")) {
                botDiceConEspera("Ofrecemos transporte, almacenaje, distribución, mudanzas y servicio urgente en toda Andalucía. ¿Te hacemos un presupuesto?", mostrarChipsMenu);
            } else {
                // Presupuesto (opción principal)
                paso = PASOS.SERVICIO;
                botDiceConEspera("¿Qué tipo de servicio necesitas?", mostrarChipsServicio);
            }
            return;
        }

        if (paso === PASOS.SERVICIO) {
            datos.servicio = texto;
            paso = PASOS.RUTA;
            botDiceConEspera("¿De dónde a dónde va la mercancía? (ej: Málaga a Sevilla)");
            return;
        }

        if (paso === PASOS.RUTA) {
            datos.ruta = texto;
            paso = PASOS.NOMBRE;
            botDiceConEspera("Perfecto. ¿Cómo te llamas?");
            return;
        }

        if (paso === PASOS.NOMBRE) {
            datos.nombre = texto;
            paso = PASOS.CONTACTO;
            botDiceConEspera("¿A qué teléfono o email te enviamos el presupuesto, " + datos.nombre + "?");
            return;
        }

        if (paso === PASOS.CONTACTO) {
            if (!validarContacto(texto)) {
                botDiceConEspera("Ese dato no parece un teléfono ni un email válido. Inténtalo de nuevo.");
                return;
            }
            datos.contacto = texto;
            paso = PASOS.FIN;
            bloquearInput();
            botDiceConEspera("Un momento, registrando tu solicitud...", enviar);
        }
    }

    function mostrarChipsMenu() {
        const opciones = [
            { icono: "fas fa-file-invoice-dollar", label: "Solicitar presupuesto" },
            { icono: "fas fa-info-circle",         label: "Ver servicios"         },
            { icono: "fas fa-headset",             label: "Hablar con un asesor"  },
        ];
        const contenedor = document.createElement("div");
        contenedor.className = "chat-chips";
        opciones.forEach(function(op) {
            const chip = document.createElement("button");
            chip.className = "chat-chip";
            chip.type = "button";
            const i = document.createElement("i");
            i.className = op.icono;
            i.setAttribute("aria-hidden", "true");
            chip.appendChild(i);
            chip.appendChild(document.createTextNode(" " + op.label));
            chip.addEventListener("click", function() {
                contenedor.remove();
                manejarRespuesta(op.label);
            });
            contenedor.appendChild(chip);
        });
        mensajesDiv.appendChild(contenedor);
        mensajesDiv.scrollTop = mensajesDiv.scrollHeight;
    }

    const SERVICIOS = [
        { icono: "fas fa-truck-moving", label: "Transporte"   },
        { icono: "fas fa-warehouse",    label: "Almacenaje"   },
        { icono: "fas fa-box",          label: "Distribución" },
        { icono: "fas fa-dolly",        label: "Mudanza"      },
        { icono: "fas fa-bolt",         label: "Urgente"      },
        { icono: "fas fa-ellipsis-h",   label: "Otro"         },
    ];

    function mostrarChipsServicio() {
        const contenedor = document.createElement("div");
        contenedor.className = "chat-chips";
        SERVICIOS.forEach(function(s) {
            const chip = document.createElement("button");
            chip.className = "chat-chip";
            chip.type = "button";
            const i = document.createElement("i");
            i.className = s.icono;
            i.setAttribute("aria-hidden", "true");
            chip.appendChild(i);
            chip.appendChild(document.createTextNode(" " + s.label));
            chip.addEventListener("click", function() {
                contenedor.remove();
                manejarRespuesta(s.label);
            });
            contenedor.appendChild(chip);
        });
        mensajesDiv.appendChild(contenedor);
        mensajesDiv.scrollTop = mensajesDiv.scrollHeight;
    }

    function mostrarTyping() {
        const div = document.createElement("div");
        div.className = "chat-burbuja chat-burbuja-bot chat-typing";
        div.id = "chatTyping";
        div.innerHTML = "<span></span><span></span><span></span>";
        mensajesDiv.appendChild(div);
        mensajesDiv.scrollTop = mensajesDiv.scrollHeight;
        return div;
    }

    function botDiceConEspera(texto, callback) {
        input.disabled       = true;
        botonEnviar.disabled = true;
        const typing = mostrarTyping();
        const delay  = 600 + Math.min(texto.length * 12, 900);
        setTimeout(function() {
            typing.remove();
            botDice(texto);
            input.disabled       = false;
            botonEnviar.disabled = false;
            if (paso < PASOS.FIN) input.focus();
            if (callback) callback();
        }, delay);
    }

    function enviar() {
        fetch("../backend/api/chat_lead.php", {
            method:  "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                nombre:   datos.nombre,
                contacto: datos.contacto,
                servicio: datos.servicio,
                ruta:     datos.ruta,
                origen:   "chatbot"
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.ok) {
                botDice("¡Listo, " + datos.nombre + "! En breve te contactamos con tu presupuesto. Gracias por confiar en Villalobos Logística.");
            } else {
                botDice("Algo salió mal. Llámanos directamente al 630 518 441.");
                desbloquearInput();
            }
        })
        .catch(function() {
            botDice("Sin conexión. Llámanos al 630 518 441.");
            desbloquearInput();
        });
    }

    function validarContacto(valor) {
        const esEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor);
        const esTel   = /^[6789]\d{8}$/.test(valor.replace(/\s/g, ""));
        return esEmail || esTel;
    }

    function botDice(texto) {
        const b = document.createElement("div");
        b.className   = "chat-burbuja chat-burbuja-bot";
        b.textContent = texto;
        mensajesDiv.appendChild(b);
        mensajesDiv.scrollTop = mensajesDiv.scrollHeight;
    }

    function usuarioDice(texto) {
        const b = document.createElement("div");
        b.className   = "chat-burbuja chat-burbuja-usuario";
        b.textContent = texto;
        mensajesDiv.appendChild(b);
        mensajesDiv.scrollTop = mensajesDiv.scrollHeight;
    }

    function bloquearInput() {
        input.disabled       = true;
        botonEnviar.disabled = true;
        input.placeholder    = "Conversación finalizada";
    }

    function desbloquearInput() {
        input.disabled       = false;
        botonEnviar.disabled = false;
        input.placeholder    = "Escribe aquí...";
    }
});
