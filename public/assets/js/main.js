document.addEventListener("DOMContentLoaded", () => {

    const btnMenu = document.querySelector('.mobile-menu-toggle');
    const navPrincipal = document.querySelector('.main-nav');
    if (btnMenu && navPrincipal) {
        btnMenu.addEventListener('click', () => navPrincipal.classList.toggle('active'));
    }

    const formulario = document.getElementById("formularioContacto");
    if (!formulario) return;

    // ── Validación de campos ──────────────────────────────────────────────────

    const reglas = {
        nombre:   { regex: /^[a-zA-ZÀ-ÿ\s]{3,}$/,      error: 'Mínimo 3 letras, sin números' },
        telefono: { regex: /^[6789]\d{8}$/,              error: 'Teléfono español no válido (9 dígitos)' },
        correo:   { regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, error: 'Formato de email no válido' }
    };

    function validarCampo(input) {
        const regla = reglas[input.id];
        const grupo = input.closest('.grupo-formulario');
        const msg   = grupo ? grupo.querySelector('.mensaje') : null;
        if (!regla || !msg) return true;

        const valido = regla.regex.test(input.value.trim());
        input.classList.toggle('campo-error', !valido);
        input.classList.toggle('campo-ok', valido);
        msg.textContent = valido ? '✓ Correcto' : regla.error;
        msg.className   = 'mensaje ' + (valido ? 'helper-ok' : 'helper-error');
        return valido;
    }

    ['nombre', 'telefono', 'correo'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', () => validarCampo(el));
    });

    // ── Multi-paso ────────────────────────────────────────────────────────────

    let pasoActual = 1;

    function irAPaso(nuevo) {
        const actual  = document.getElementById('form-paso-' + pasoActual);
        const destino = document.getElementById('form-paso-' + nuevo);
        if (!destino) return;

        actual.classList.remove('activo');
        destino.classList.add('activo');
        pasoActual = nuevo;

        actualizarProgreso();
        formulario.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function actualizarProgreso() {
        document.querySelectorAll('.form-progreso-paso').forEach(el => {
            const n = parseInt(el.dataset.paso);
            el.classList.toggle('activo',    n === pasoActual);
            el.classList.toggle('completado', n < pasoActual);
        });
        document.querySelectorAll('.form-progreso-linea').forEach((el, i) => {
            el.classList.toggle('completada', i + 1 < pasoActual);
        });
    }

    // Selección de servicio con cards
    const inputServicio = document.getElementById('tipoServicio');
    document.querySelectorAll('.servicio-opcion').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.servicio-opcion').forEach(b => b.classList.remove('seleccionado'));
            btn.classList.add('seleccionado');
            if (inputServicio) inputServicio.value = btn.dataset.valor;
            const err = document.getElementById('errorServicio');
            if (err) err.style.display = 'none';
        });
    });

    // Botones Siguiente
    formulario.querySelectorAll('.btn-siguiente').forEach(btn => {
        btn.addEventListener('click', () => {
            const destino = parseInt(btn.dataset.siguiente);

            if (pasoActual === 1) {
                if (!inputServicio || !inputServicio.value) {
                    const err = document.getElementById('errorServicio');
                    if (err) err.style.display = 'block';
                    return;
                }
            }
            irAPaso(destino);
        });
    });

    // Botones Atrás
    formulario.querySelectorAll('.btn-anterior').forEach(btn => {
        btn.addEventListener('click', () => irAPaso(parseInt(btn.dataset.anterior)));
    });

    // ── Envío ─────────────────────────────────────────────────────────────────

    const contenedorMensaje = document.getElementById("mensajeContacto");
    const botonEnviar       = document.getElementById("botonEnviarContacto");

    formulario.addEventListener("submit", function(e) {
        e.preventDefault();
        ocultarMensaje(contenedorMensaje);

        const camposContacto = ['nombre', 'telefono', 'correo'].map(id => document.getElementById(id));
        const errores = camposContacto.filter(el => el && !validarCampo(el));

        const rgpd = document.getElementById('rgpd');
        const rgpdError = document.getElementById('rgpd-error');
        if (rgpd && !rgpd.checked) {
            if (rgpdError) rgpdError.style.display = 'block';
            errores.push(rgpd);
        } else if (rgpdError) {
            rgpdError.style.display = 'none';
        }

        if (errores.length > 0) {
            formulario.classList.add('formulario-shake');
            formulario.addEventListener('animationend', () => formulario.classList.remove('formulario-shake'), { once: true });
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
            servicio: inputServicio ? inputServicio.value : '',
            origen:   (document.getElementById("origen")         || {value:''}).value.trim(),
            destino:  (document.getElementById("destino")        || {value:''}).value.trim(),
            mensaje:  (document.getElementById("detalleMensaje") || {value:''}).value.trim()
        };

        fetch('../backend/api/contacto.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(datos)
        })
        .then(res => res.json().then(data => ({ ok: res.ok, data })))
        .then(({ data }) => {
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

    // ── Estimador de distancia y precio (Módulo Python) ─────────────────────

    const campoOrigen  = document.getElementById('origen');
    const campoDestino = document.getElementById('destino');
    const boxEstimado  = document.getElementById('precio-estimado');
    const boxCargando  = document.getElementById('precio-cargando');
    const spanKm       = document.getElementById('precioKm');
    const spanPrecio   = document.getElementById('precioValor');

    let timerEstimador = null;

    function lanzarEstimacion() {
        const origen  = (campoOrigen  ? campoOrigen.value.trim()  : '');
        const destino = (campoDestino ? campoDestino.value.trim() : '');
        if (!origen || !destino || origen.length < 3 || destino.length < 3) return;

        if (boxEstimado)  boxEstimado.hidden  = true;
        if (boxCargando)  boxCargando.hidden  = false;

        clearTimeout(timerEstimador);
        timerEstimador = setTimeout(() => {
            fetch('../backend/api/calcular_distancia.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ origen, destino })
            })
            .then(r => r.json())
            .then(d => {
                if (boxCargando) boxCargando.hidden = true;
                if (!d.ok || !boxEstimado) return;
                if (spanKm)     spanKm.textContent     = d.km + ' km aprox.';
                if (spanPrecio) spanPrecio.textContent = d.precio_estimado.toFixed(2) + ' € est.';
                boxEstimado.hidden = false;
            })
            .catch(() => {
                if (boxCargando) boxCargando.hidden = true;
            });
        }, 800);  // espera 800 ms tras el último cambio para no spamear
    }

    if (campoOrigen)  campoOrigen.addEventListener('blur',  lanzarEstimacion);
    if (campoDestino) campoDestino.addEventListener('blur',  lanzarEstimacion);
    if (campoOrigen)  campoOrigen.addEventListener('change', lanzarEstimacion);
    if (campoDestino) campoDestino.addEventListener('change', lanzarEstimacion);

    function mostrarExito() {
        formulario.style.display = 'none';
        const card = document.createElement('div');
        card.className = 'tarjeta-exito';
        card.innerHTML = `
            <i class="fas fa-circle-check tarjeta-exito-icono"></i>
            <h4>¡Solicitud recibida!</h4>
            <p>Le contactaremos en menos de 24 horas. Gracias por confiar en Villalobos Logística.</p>
            <button class="btn btn-primary" id="btnNuevoMensaje">Enviar otra solicitud</button>
        `;
        formulario.parentElement.appendChild(card);
        document.getElementById('btnNuevoMensaje').addEventListener('click', () => {
            card.remove();
            formulario.style.display = '';
            formulario.reset();
            document.querySelectorAll('.servicio-opcion').forEach(b => b.classList.remove('seleccionado'));
            if (inputServicio) inputServicio.value = '';
            ['nombre', 'telefono', 'correo'].forEach(id => {
                const el = document.getElementById(id);
                if (!el) return;
                el.classList.remove('campo-ok', 'campo-error');
                const msg = el.closest('.grupo-formulario')?.querySelector('.mensaje');
                if (msg) { msg.textContent = ''; msg.className = 'mensaje'; }
            });
            irAPaso(1);
        });
    }

});
