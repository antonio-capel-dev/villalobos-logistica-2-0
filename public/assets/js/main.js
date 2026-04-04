document.addEventListener("DOMContentLoaded", () => {

    const btnMenu = document.querySelector('.mobile-menu-toggle');
    const navPrincipal = document.querySelector('.main-nav');
    if (btnMenu && navPrincipal) {
        btnMenu.addEventListener('click', () => navPrincipal.classList.toggle('active'));
    }

    const formulario = document.getElementById("formularioContacto");
    if (!formulario) return;

    const reglas = {
        nombre:       { regex: /^[a-zA-ZÀ-ÿ\s]{3,}$/,          error: 'Mínimo 3 letras, sin números' },
        telefono:     { regex: /^[6789]\d{8}$/,                  error: 'Teléfono español no válido (9 dígitos)' },
        correo:       { regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,     error: 'Formato de email no válido' },
        tipoServicio: { regex: /.+/,                              error: 'Selecciona un tipo de servicio' }
    };

    function validarCampo(input) {
        const regla = reglas[input.id];
        const mensaje = input.closest('.grupo-formulario').querySelector('.mensaje');
        if (!regla) return true;

        const valido = regla.regex.test(input.value.trim());
        input.classList.toggle('campo-error', !valido);
        input.classList.toggle('campo-ok', valido);
        mensaje.textContent = valido ? '✓ Correcto' : regla.error;
        mensaje.className = 'mensaje ' + (valido ? 'helper-ok' : 'helper-error');
        return valido;
    }

    const camposObligatorios = ['nombre', 'telefono', 'correo', 'tipoServicio'];

    camposObligatorios.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', () => validarCampo(el));
    });

    const contenedorMensaje = document.getElementById("mensajeContacto");
    const botonEnviar = document.getElementById("botonEnviarContacto");

    formulario.addEventListener("submit", function(e) {
        e.preventDefault();
        ocultarMensaje(contenedorMensaje);

        const errores = camposObligatorios
            .map(id => document.getElementById(id))
            .filter(el => el && !validarCampo(el));

        if (errores.length > 0) {
            formulario.classList.add('formulario-shake');
            formulario.addEventListener('animationend', () => {
                formulario.classList.remove('formulario-shake');
            }, { once: true });
            errores[0].focus();
            return;
        }

        const textoOriginal = botonEnviar.innerHTML;
        botonEnviar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
        botonEnviar.disabled = true;

        const datos = {
            nombre:   document.getElementById("nombre").value.trim(),
            telefono: document.getElementById("telefono").value.trim(),
            email:    document.getElementById("correo").value.trim(),
            servicio: document.getElementById("tipoServicio").value.trim(),
            origen:   document.getElementById("origen").value.trim(),
            destino:  document.getElementById("destino").value.trim(),
            mensaje:  document.getElementById("detalleMensaje").value.trim()
        };

        fetch('../backend/api/contacto.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datos)
        })
        .then(res => res.json())
        .then(data => {
            botonEnviar.innerHTML = textoOriginal;
            botonEnviar.disabled = false;
            if (data.ok) {
                mostrarExito();
            } else {
                mostrarMensaje(contenedorMensaje, "error", data.error || "Error en la validación.");
            }
        })
        .catch(() => {
            botonEnviar.innerHTML = textoOriginal;
            botonEnviar.disabled = false;
            mostrarMensaje(contenedorMensaje, "error", "Error de conexión. Inténtelo más tarde.");
        });
    });

    function mostrarExito() {
        formulario.style.display = 'none';
        const card = document.createElement('div');
        card.className = 'tarjeta-exito';
        card.innerHTML = `
            <i class="fas fa-circle-check tarjeta-exito-icono"></i>
            <h4>¡Mensaje recibido!</h4>
            <p>Le contactaremos en menos de 24 horas. Gracias por confiar en Villalobos Logística.</p>
            <button class="btn btn-primary" id="btnNuevoMensaje">Enviar otro mensaje</button>
        `;
        formulario.parentElement.appendChild(card);
        document.getElementById('btnNuevoMensaje').addEventListener('click', () => {
            card.remove();
            formulario.style.display = '';
            formulario.reset();
            camposObligatorios.forEach(id => {
                const el = document.getElementById(id);
                if (!el) return;
                el.classList.remove('campo-ok', 'campo-error');
                const msg = el.closest('.grupo-formulario')?.querySelector('.mensaje');
                if (msg) msg.textContent = '';
            });
        });
    }

});
