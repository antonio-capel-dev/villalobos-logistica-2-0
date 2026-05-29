// Activa los preloads de CSS sin necesitar onload inline (evita 'unsafe-inline' en CSP)
(function () {
    function activarPreloads() {
        document.querySelectorAll('link[rel="preload"][as="style"]').forEach(function (l) {
            l.rel = 'stylesheet';
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', activarPreloads);
    } else {
        activarPreloads();
    }
})();

document.addEventListener("DOMContentLoaded", () => {

    const btnMenu = document.querySelector('.mobile-menu-toggle');
    const navPrincipal = document.querySelector('.main-nav');
    const navOverlay = document.getElementById('navOverlay');

    function toggleNav() {
        const abierto = navPrincipal.classList.toggle('active');
        if (navOverlay) {
            navOverlay.classList.toggle('activo', abierto);
            navOverlay.setAttribute('aria-hidden', String(!abierto));
        }
        btnMenu.setAttribute('aria-expanded', String(abierto));
    }

    if (btnMenu && navPrincipal) {
        btnMenu.addEventListener('click', toggleNav);
        // Cerrar menú al pulsar el overlay
        if (navOverlay) {
            navOverlay.addEventListener('click', toggleNav);
        }
        // Cerrar menú al pulsar Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && navPrincipal.classList.contains('active')) {
                toggleNav();
                btnMenu.focus();
            }
        });
    }

    // ===== HEADER SCROLL-AWARE (rAF throttled) =====
    // No llamamos onScroll() en DOMContentLoaded: el estado inicial sin .scrolled
    // ya es correcto (header transparente sobre el hero). Llamarlo aquí fuerza un
    // reflow porque styles.min.css todavía puede estar cargándose de forma async.
    const header = document.querySelector('.main-header');
    if (header) {
        let rafPendiente = false;
        const onScroll = () => {
            if (rafPendiente) return;
            rafPendiente = true;
            requestAnimationFrame(() => {
                header.classList.toggle('scrolled', window.scrollY > 50);
                rafPendiente = false;
            });
        };
        window.addEventListener('scroll', onScroll, { passive: true });
    }
    // ================================================

    // ===== WIDGET WHATSAPP =====
    // localStorage con TTL 24h: si el usuario cerró el panel, no lo reabrimos
    // automáticamente hasta el día siguiente.

    const WA_CERRADO_KEY = "vll_waCerrado";
    const WA_TTL_MS      = 24 * 60 * 60 * 1000; // 24 horas

    function waCerradoReciente() {
        try {
            const ts = parseInt(localStorage.getItem(WA_CERRADO_KEY) || "0", 10);
            return ts && Date.now() - ts < WA_TTL_MS;
        } catch (_) { return false; }
    }

    const waBoton  = document.getElementById("waBoton");
    const waPanel  = document.getElementById("waPanel");
    const waCerrar = document.getElementById("waCerrar");
    const waEnviar = document.getElementById("waEnviar");

    if (waBoton && waPanel) {
        // Si el usuario cerró el panel recientemente, arrancamos con él oculto
        if (waCerradoReciente()) waPanel.hidden = true;

        waBoton.addEventListener("click", () => {
            waPanel.hidden = !waPanel.hidden;
            if (!waPanel.hidden) {
                // Abrió el panel → limpiar el flag de cerrado
                try { localStorage.removeItem(WA_CERRADO_KEY); } catch (_) {}
                const inp = document.getElementById("waMensaje");
                if (inp) setTimeout(() => inp.focus(), 100);
            }
        });
        if (waCerrar) {
            waCerrar.addEventListener("click", () => {
                waPanel.hidden = true;
                // Recordar que el usuario lo cerró durante 24h
                try { localStorage.setItem(WA_CERRADO_KEY, String(Date.now())); } catch (_) {}
            });
        }
        if (waEnviar) {
            waEnviar.addEventListener("click", () => {
                const inp = document.getElementById("waMensaje");
                const texto = inp ? inp.value.trim() : "";
                const msg = texto || "Hola, me gustaría solicitar información sobre vuestros servicios de transporte.";
                window.open("https://wa.me/34625038039?text=" + encodeURIComponent(msg), "_blank");
            });
        }
    }
    // ===========================

    const formulario = document.getElementById("formularioContacto");
    if (!formulario) return;

    // ─── Normalización de datos ───────────────────────────────────────────
    // Normalizar antes de validar y antes de enviar asegura consistencia
    // entre lo que el usuario ve y lo que llega al backend.

    function normalizarNombre(v) {
        // Colapsa espacios múltiples y capitaliza cada palabra
        return v.trim().replace(/\s+/g, ' ');
    }
    function normalizarTelefono(v) {
        // Elimina espacios, guiones y paréntesis → "612 34-56 78" → "612345678"
        return v.replace(/[\s\-().]/g, '');
    }
    function normalizarEmail(v) {
        return v.trim().toLowerCase();
    }

    // Validación de campos del formulario con Regex

    const reglas = {
        nombre:   { regex: /^[a-zA-ZÀ-ÿ\s]{3,}$/,      error: 'Mínimo 3 letras, sin números',         normalizar: normalizarNombre },
        telefono: { regex: /^[6789]\d{8}$/,              error: 'Teléfono español no válido (9 dígitos)', normalizar: normalizarTelefono },
        correo:   { regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, error: 'Formato de email no válido',            normalizar: normalizarEmail }
    };

    function validarCampo(input) {
        const regla = reglas[input.id];
        const grupo = input.closest('.grupo-formulario');
        const msg   = grupo ? grupo.querySelector('.mensaje') : null;
        if (!regla || !msg) return true;

        // Normalizar el valor antes de validar (sin modificar lo que el usuario ve)
        const valorNormalizado = regla.normalizar ? regla.normalizar(input.value) : input.value.trim();
        const valido = regla.regex.test(valorNormalizado);
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

    // Lógica para el formulario multi-paso (Siguiente/Atrás)

    let pasoActual = 1;

    function irAPaso(nuevo) {
        const actual  = document.getElementById('form-paso-' + pasoActual);
        const destino = document.getElementById('form-paso-' + nuevo);
        if (!destino) return;

        actual.classList.remove('activo');
        destino.classList.add('activo');
        pasoActual = nuevo;
        try { sessionStorage.setItem('vll_formPaso', String(nuevo)); } catch (_) {}

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

    // Restaurar paso guardado en sessionStorage al recargar la página
    // sessionStorage se borra al cerrar la pestaña, por lo que solo persiste recargas
    // (no reaparece el paso 2 en visitas nuevas — comportamiento esperado).
    try {
        const pasoGuardado = parseInt(sessionStorage.getItem('vll_formPaso') || '1', 10);
        if (pasoGuardado > 1) {
            const pasoDestino = document.getElementById('form-paso-' + pasoGuardado);
            const pasoBase    = document.getElementById('form-paso-1');
            if (pasoDestino && pasoBase) {
                pasoBase.classList.remove('activo');
                pasoDestino.classList.add('activo');
                pasoActual = pasoGuardado;
                actualizarProgreso();
            }
        }
    } catch (_) {}

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

    // Envío del formulario de contacto mediante Fetch API

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

        // Normalizamos cada campo antes de enviar al backend:
        // - teléfono: sin espacios/guiones → coincide con regex del backend
        // - email: minúsculas → evita duplicados en BD ("Juan@Gmail.com" = "juan@gmail.com")
        // - nombre: espacios colapsados → "  Juan   García  " → "Juan García"
        const datos = {
            nombre:   normalizarNombre(document.getElementById("nombre").value),
            telefono: normalizarTelefono(document.getElementById("telefono").value),
            email:    normalizarEmail(document.getElementById("correo").value),
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

    // Módulo para estimar distancia y precio usando el script de Python

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
        try { sessionStorage.removeItem('vll_formPaso'); } catch (_) {}
        formulario.style.display = 'none';
        const card = document.createElement('div');
        card.className = 'tarjeta-exito';
        card.innerHTML = `
            <i class="fas fa-circle-check tarjeta-exito-icono"></i>
            <h4>¡Solicitud recibida!</h4>
            <p>Le contactaremos con la mayor brevedad posible. Gracias por confiar en Villalobos Logística.</p>
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

    // Interceptar clics en los botones de WhatsApp para registrar el lead
    document.querySelectorAll('a[href*="wa.me"]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.href;
            // Registrar lead de forma silenciosa
            fetch('../backend/api/chat_lead.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    nombre: 'Click WhatsApp',
                    contacto: 'WhatsApp Redirección',
                    servicio: 'Contacto Rápido',
                    ruta: 'N/A',
                    origen: 'whatsapp'
                })
            }).finally(() => {
                // Redirigir a WhatsApp siempre, falle o no el tracking
                window.open(url, '_blank', 'noopener,noreferrer');
            });
        });
    });

});
