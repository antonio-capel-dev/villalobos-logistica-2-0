document.addEventListener("DOMContentLoaded", () => {

    const boton       = document.getElementById("chatBoton");
    const panel       = document.getElementById("chatPanel");
    const cerrarBtn   = document.getElementById("chatCerrar");
    const mensajesDiv = document.getElementById("chatMensajes");
    const input       = document.getElementById("chatInput");
    const botonEnviar = document.getElementById("chatEnviar");
    const badge       = document.getElementById("chatBadge");
    const callout     = document.getElementById("chatCallout");
    const calloutX    = document.getElementById("chatCalloutCerrar");

    if (!boton || !panel) return;

    const PASOS = { NOMBRE: 0, CONTACTO: 1, SERVICIO: 2, RUTA: 3, FIN: 4 };
    let paso  = PASOS.NOMBRE;
    let datos = { nombre: "", contacto: "", servicio: "", ruta: "" };

    function abrirChat() {
        panel.hidden = false;
        if (callout) callout.hidden = true;
        if (badge)   badge.classList.add("oculto");
        if (mensajesDiv.children.length === 0) {
            setTimeout(() => botDice("Hola! Soy el asistente de Villalobos Logistica. Como te llamas?"), 400);
        }
        setTimeout(() => input.focus(), 500);
        sessionStorage.setItem("chatVisto", "1");
    }

    function cerrarChat() { panel.hidden = true; }

    boton.addEventListener("click", () => panel.hidden ? abrirChat() : cerrarChat());
    if (cerrarBtn) cerrarBtn.addEventListener("click", cerrarChat);

    if (callout && !sessionStorage.getItem("chatVisto")) {
        setTimeout(() => { if (panel.hidden) callout.hidden = false; }, 7000);
    }
    if (calloutX) calloutX.addEventListener("click", () => {
        callout.hidden = true;
        sessionStorage.setItem("chatVisto", "1");
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

        if (paso === PASOS.NOMBRE) {
            datos.nombre = texto;
            paso++;
            botDiceConEspera("Encantado, " + datos.nombre + "! Cual es tu telefono o email para enviarte el presupuesto?");

        } else if (paso === PASOS.CONTACTO) {
            if (!validarContacto(texto)) {
                botDiceConEspera("Ese dato no parece un telefono ni un email valido. Intentalo de nuevo.");
                return;
            }
            datos.contacto = texto;
            paso++;
            botDiceConEspera("Que servicio necesitas?", mostrarChipsServicio);

        } else if (paso === PASOS.SERVICIO) {
            datos.servicio = texto;
            paso++;
            botDiceConEspera("De donde a donde va la mercancia? (ej: Malaga a Sevilla)");

        } else if (paso === PASOS.RUTA) {
            datos.ruta = texto;
            paso = PASOS.FIN;
            bloquearInput();
            botDiceConEspera("Un momento, registrando tu solicitud...", enviar);
        }
    }

    const SERVICIOS = [
        { icono: "fas fa-truck-moving", label: "Transporte"   },
        { icono: "fas fa-warehouse",    label: "Almacenaje"   },
        { icono: "fas fa-box",          label: "Distribucion" },
        { icono: "fas fa-dolly",        label: "Mudanza"      },
        { icono: "fas fa-bolt",         label: "Urgente"      },
        { icono: "fas fa-ellipsis-h",   label: "Otro"         },
    ];

    function mostrarChipsServicio() {
        const contenedor = document.createElement("div");
        contenedor.className = "chat-chips";
        SERVICIOS.forEach(function(s) {
            const chip = document.createElement("button");
            chip.className   = "chat-chip";
            chip.type        = "button";
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
            body: JSON.stringify({ nombre: datos.nombre, contacto: datos.contacto, servicio: datos.servicio, ruta: datos.ruta, origen: 'chatbot' })
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.ok) {
                botDice("Perfecto, " + datos.nombre + "! Te contactamos con la mayor brevedad posible.");
            } else {
                botDice("Algo salio mal. Llamanos directamente al 630 518 441.");
                desbloquearInput();
            }
        })
        .catch(function() {
            botDice("Sin conexion. Llamanos al 630 518 441.");
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
        input.placeholder    = "Conversacion finalizada";
    }

    function desbloquearInput() {
        input.disabled       = false;
        botonEnviar.disabled = false;
    }
});
