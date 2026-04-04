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

    if (!formularioContacto) return;

    const contenedorMensaje = document.getElementById("mensajeContacto");
    const botonEnviar       = document.getElementById("botonEnviarContacto");

    // --- Utilidades de validación por campo ---

    const REGEX_EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const REGEX_TEL   = /^[+\d\s\-().]{7,}$/;

    function marcarError(campo, mensaje) {
        campo.classList.remove('campo-ok');
        campo.classList.add('campo-error');
        let helper = campo.parentElement.querySelector('.campo-helper');
        if (!helper) {
            helper = document.createElement('span');
            helper.className = 'campo-helper';
            campo.parentElement.appendChild(helper);
        }
        helper.textContent = mensaje;
        helper.classList.remove('helper-ok');
        helper.classList.add('helper-error');
    }

    function marcarOk(campo) {
        campo.classList.remove('campo-error');
        campo.classList.add('campo-ok');
        let helper = campo.parentElement.querySelector('.campo-helper');
        if (!helper) {
            helper = document.createElement('span');
            helper.className = 'campo-helper';
            campo.parentElement.appendChild(helper);
        }
        helper.textContent = '✓ Correcto';
        helper.classList.remove('helper-error');
        helper.classList.add('helper-ok');
    }

    function limpiarEstado(campo) {
        campo.classList.remove('campo-error', 'campo-ok');
        const helper = campo.parentElement.querySelector('.campo-helper');
        if (helper) helper.textContent = '';
    }

    function validarCampo(campo) {
        const val = campo.value.trim();
        const id  = campo.id;

        if (campo.required && !val) {
            marcarError(campo, 'Este campo es obligatorio');
            return false;
        }
        if (id === 'correo' && val && !REGEX_EMAIL.test(val)) {
            marcarError(campo, 'Formato de email no válido');
            return false;
        }
        if (id === 'telefono' && val && !REGEX_TEL.test(val)) {
            marcarError(campo, 'Introduce un teléfono válido');
            return false;
        }
        if (val) marcarOk(campo);
        return true;
    }

    // Validación en tiempo real al salir de cada campo (blur)
    const camposObligatorios = ['nombre', 'telefono', 'correo', 'tipoServicio'];
    const camposOpcionales   = ['origen', 'destino', 'detalleMensaje'];
    const todosCampos = [...camposObligatorios, ...camposOpcionales];

    todosCampos.forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('blur', () => validarCampo(el));
        el.addEventListener('input', () => {
            if (el.classList.contains('campo-error')) validarCampo(el);
        });
    });

    // Shake animation al intentar enviar con errores
    function sacudirFormulario() {
        formularioContacto.classList.add('formulario-shake');
        formularioContacto.addEventListener('animationend', () => {
            formularioContacto.classList.remove('formulario-shake');
        }, { once: true });
    }

    // Estado de éxito: oculta el form y muestra card de gracias
    function mostrarExito() {
        formularioContacto.style.display = 'none';
        let card = document.getElementById('tarjeta-exito');
        if (!card) {
            card = document.createElement('div');
            card.id = 'tarjeta-exito';
            card.className = 'tarjeta-exito';
            card.innerHTML = `
                <i class="fas fa-circle-check tarjeta-exito-icono"></i>
                <h4>¡Mensaje recibido!</h4>
                <p>Le contactaremos en menos de 24 horas. Gracias por confiar en Villalobos Logística.</p>
                <button class="btn btn-primary" id="btnNuevaMensaje">Enviar otro mensaje</button>
            `;
            formularioContacto.parentElement.appendChild(card);
            document.getElementById('btnNuevaMensaje').addEventListener('click', () => {
                card.remove();
                formularioContacto.style.display = '';
                formularioContacto.reset();
                todosCampos.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) limpiarEstado(el);
                });
            });
        }
    }

    // Submit
    formularioContacto.addEventListener("submit", function(evento) {
        evento.preventDefault();
        ocultarMensaje(contenedorMensaje);

        // Validar todos los campos obligatorios
        let hayErrores = false;
        camposObligatorios.forEach(id => {
            const el = document.getElementById(id);
            if (el && !validarCampo(el)) hayErrores = true;
        });

        if (hayErrores) {
            sacudirFormulario();
            // Scroll al primer campo con error
            const primerError = formularioContacto.querySelector('.campo-error');
            if (primerError) primerError.focus();
            return;
        }

        // Estado de carga en el botón
        const textoOriginal = botonEnviar.innerHTML;
        botonEnviar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
        botonEnviar.disabled = true;

        const datosParaEnviar = {
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
            body: JSON.stringify(datosParaEnviar)
        })
        .then(res => {
            if (!res.ok && res.status !== 400 && res.status !== 500)
                throw new Error("Error servidor: " + res.status);
            return res.json();
        })
        .then(data => {
            botonEnviar.innerHTML = textoOriginal;
            botonEnviar.disabled = false;
            if (data.ok) {
                mostrarExito();
            } else {
                mostrarMensaje(contenedorMensaje, "error", data.error || "Error en la validación.");
                sacudirFormulario();
            }
        })
        .catch(err => {
            botonEnviar.innerHTML = textoOriginal;
            botonEnviar.disabled = false;
            console.error("Fetch error:", err);
            mostrarMensaje(contenedorMensaje, "error", "Error de conexión. Inténtelo más tarde.");
            sacudirFormulario();
        });
    });
});
