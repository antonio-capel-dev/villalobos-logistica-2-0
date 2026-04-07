document.addEventListener("DOMContentLoaded", () => {

    const boton       = document.getElementById("chatBoton");
    const panel       = document.getElementById("chatPanel");
    const mensajesDiv = document.getElementById("chatMensajes");
    const input       = document.getElementById("chatInput");
    const botonEnviar = document.getElementById("chatEnviar");
    const iconoAbrir  = document.getElementById("chatIconoAbierto");
    const iconoCerrar = document.getElementById("chatIconoCerrado");

    if (!boton || !panel) return;

    // Estado: 0=nombre, 1=contacto, 2=servicio, 3=enviado
    let estado = 0;
    let datos  = { nombre: "", contacto: "", servicio: "" };

    const preguntas = [
        "¡Hola! 👋 ¿Cómo te llamas?",
        "¿Cuál es tu email o teléfono para contactarte?",
        "¿Qué servicio necesitas? (transporte, almacenaje, urgente...)"
    ];

    boton.addEventListener("click", () => {
        const abierto = !panel.hidden;

        if (abierto) {
            panel.hidden = true;
            iconoAbrir.hidden  = false;
            iconoCerrar.hidden = true;
        } else {
            panel.hidden = false;
            iconoAbrir.hidden  = true;
            iconoCerrar.hidden = false;

            if (mensajesDiv.children.length === 0) {
                setTimeout(() => botDice(preguntas[0]), 400);
            }
            setTimeout(() => input.focus(), 500);
        }
    });

    botonEnviar.addEventListener("click", procesar);
    input.addEventListener("keydown", e => { if (e.key === "Enter") procesar(); });

    function procesar() {
        const texto = input.value.trim();
        if (!texto || estado >= 3) return;

        usuarioDice(texto);
        input.value = "";

        if (estado === 0)      datos.nombre   = texto;
        else if (estado === 1) datos.contacto = texto;
        else if (estado === 2) datos.servicio = texto;

        estado++;

        if (estado < preguntas.length) {
            setTimeout(() => botDice(preguntas[estado]), 600);
        } else {
            setTimeout(() => {
                botDice("Un momento, estoy registrando tu solicitud... ⏳");
                enviar();
            }, 600);
        }
    }

    function enviar() {
        estado = 3;
        input.disabled       = true;
        botonEnviar.disabled = true;

        fetch("../backend/api/contacto.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                nombre:  datos.nombre,
                email:   datos.contacto,
                mensaje: "CHAT — Servicio: " + datos.servicio
            })
        })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                botDice("✅ ¡Gracias, " + datos.nombre + "! Nos pondremos en contacto pronto. 🚛");
            } else {
                botDice("❌ Hubo un problema. Puedes llamarnos al 630 518 441.");
                estado = 2;
                input.disabled       = false;
                botonEnviar.disabled = false;
            }
        })
        .catch(() => {
            botDice("❌ Error de conexión. Llámanos al 630 518 441.");
            estado = 2;
            input.disabled       = false;
            botonEnviar.disabled = false;
        });
    }

    function botDice(texto) {
        const burbuja = document.createElement("div");
        burbuja.className = "chat-burbuja chat-burbuja-bot";
        burbuja.textContent = texto;
        mensajesDiv.appendChild(burbuja);
        mensajesDiv.scrollTop = mensajesDiv.scrollHeight;
    }

    function usuarioDice(texto) {
        const burbuja = document.createElement("div");
        burbuja.className = "chat-burbuja chat-burbuja-usuario";
        burbuja.textContent = texto;
        mensajesDiv.appendChild(burbuja);
        mensajesDiv.scrollTop = mensajesDiv.scrollHeight;
    }

});
