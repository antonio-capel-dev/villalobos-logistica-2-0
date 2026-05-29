document.addEventListener("DOMContentLoaded", () => {

    const cuerpoTabla       = document.getElementById("cuerpoTablaPortes");
    const formularioPorte   = document.getElementById("formularioNuevoPorte");
    const botonActualizar   = document.getElementById("botonActualizar");
    const mensajeFormulario = document.getElementById("mensajeFormulario");
    const mensajeCabecera   = document.getElementById("mensajeGlobal");

    const API = '../backend/api/portes.php';

    cargarPortes();

    if (botonActualizar) {
        botonActualizar.addEventListener("click", cargarPortes);
    }

    function cargarPortes() {
        cuerpoTabla.innerHTML = '<tr><td colspan="6" class="alineacion-centro texto-secundario"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>';

        fetch(API)
            .then(res => {
                if (!res.ok) throw new Error("HTTP " + res.status);
                return res.json();
            })
            .then(data => {
                if (data.ok) {
                    pintarTabla(data.data);
                } else {
                    errorEnTabla(data.error || "Error al cargar los portes.");
                }
            })
            .catch(() => errorEnTabla("No se puede conectar con el servidor."));
    }

    function pintarTabla(portes) {
        cuerpoTabla.innerHTML = '';

        if (!portes || portes.length === 0) {
            cuerpoTabla.innerHTML = '<tr><td colspan="6" class="alineacion-centro texto-secundario">No hay portes registrados.</td></tr>';
            return;
        }

        // Ordenar por fecha más reciente primero
        const portesOrdenados = portes.slice().sort((a, b) =>
            new Date(b.fecha_programada) - new Date(a.fecha_programada)
        );

        portesOrdenados.forEach(porte => {
            const fila = document.createElement("tr");

            fila.appendChild(crearCelda(porte.id));
            fila.appendChild(crearCelda(porte.fecha_programada));
            fila.appendChild(crearCelda(porte.origen));
            fila.appendChild(crearCelda(porte.destino));

            const celdaEstado = document.createElement("td");
            const badge = document.createElement("span");
            badge.textContent = porte.estado.toUpperCase();
            badge.className = `insignia-estado estado-${porte.estado}`;
            celdaEstado.appendChild(badge);
            fila.appendChild(celdaEstado);

            const celdaAcciones = document.createElement("td");

            const btnEditar = document.createElement('button');
            btnEditar.className = 'boton-pequeno boton-editar';
            btnEditar.textContent = 'Editar';
            btnEditar.addEventListener('click', () => abrirModal(porte));

            const btnBorrar = document.createElement("button");
            btnBorrar.className = "boton-pequeno boton-peligro";
            btnBorrar.textContent = "Borrar";
            btnBorrar.addEventListener('click', () => borrarPorte(porte.id));

            celdaAcciones.appendChild(btnEditar);
            celdaAcciones.appendChild(document.createTextNode(' '));
            celdaAcciones.appendChild(btnBorrar);
            fila.appendChild(celdaAcciones);

            cuerpoTabla.appendChild(fila);
        });
    }

    function errorEnTabla(msg) {
        cuerpoTabla.innerHTML = '';
        const fila = document.createElement("tr");
        const celda = document.createElement("td");
        celda.colSpan = 6;
        celda.className = "alineacion-centro mensaje-error";
        celda.textContent = msg;
        fila.appendChild(celda);
        cuerpoTabla.appendChild(fila);
    }

    if (formularioPorte) {
        formularioPorte.addEventListener("submit", function(e) {
            e.preventDefault();
            ocultarMensaje(mensajeFormulario);

            const btnGuardar = document.getElementById("botonGuardar");
            const textoOriginal = btnGuardar.innerHTML;
            btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            btnGuardar.disabled = true;

            const datos = {
                fecha_programada: document.getElementById("fecha").value,
                origen:  document.getElementById("origen").value.trim(),
                destino: document.getElementById("destino").value.trim(),
                kms:     document.getElementById("kms").value || 0,
                precio:  document.getElementById("precio").value || 0
            };

            fetch(API, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            })
            .then(res => res.json())
            .then(data => {
                btnGuardar.innerHTML = textoOriginal;
                btnGuardar.disabled = false;

                if (data.ok) {
                    mostrarMensaje(mensajeFormulario, "exito", "Porte añadido correctamente.");
                    formularioPorte.reset();
                    cargarPortes();
                } else {
                    mostrarMensaje(mensajeFormulario, "error", data.error || "No se pudo registrar el porte.");
                }
            })
            .catch(() => {
                btnGuardar.innerHTML = textoOriginal;
                btnGuardar.disabled = false;
                mostrarMensaje(mensajeFormulario, "error", "Error de red al guardar.");
            });
        });
    }

    function borrarPorte(id) {
        if (!confirm(`¿Eliminar el porte #${id}?`)) return;
        ocultarMensaje(mensajeCabecera);

        fetch(API, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.ok) {
                mostrarMensaje(mensajeCabecera, "exito", `Porte #${id} eliminado.`);
                cargarPortes();
            } else {
                mostrarMensaje(mensajeCabecera, "error", data.error || "No se pudo eliminar.");
            }
        })
        .catch(() => mostrarMensaje(mensajeCabecera, "error", "Error de red al eliminar."));
    }

    const modalEditar       = document.getElementById('modalEditar');
    const formularioEditar  = document.getElementById('formularioEditarPorte');
    const mensajeModal      = document.getElementById('mensajeModal');

    function abrirModal(porte) {
        document.getElementById('editId').value        = porte.id;
        document.getElementById('modalPorteId').textContent = '#' + porte.id;
        document.getElementById('editFecha').value     = porte.fecha_programada;
        document.getElementById('editOrigen').value    = porte.origen;
        document.getElementById('editDestino').value   = porte.destino;
        document.getElementById('editKms').value       = porte.kms || '';
        document.getElementById('editPrecio').value    = porte.precio || '';
        document.getElementById('editEstado').value    = porte.estado;

        ocultarMensaje(mensajeModal);
        modalEditar.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function cerrarModal() {
        modalEditar.hidden = true;
        document.body.style.overflow = '';
    }

    document.getElementById('botonCerrarModal').addEventListener('click', cerrarModal);
    document.getElementById('botonCancelarEdicion').addEventListener('click', cerrarModal);
    modalEditar.addEventListener('click', e => { if (e.target === modalEditar) cerrarModal(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && !modalEditar.hidden) cerrarModal(); });

    if (formularioEditar) {
        formularioEditar.addEventListener('submit', function(e) {
            e.preventDefault();
            ocultarMensaje(mensajeModal);

            const btn = document.getElementById('botonGuardarEdicion');
            const textoOriginal = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            btn.disabled = true;

            const datos = {
                id:               document.getElementById('editId').value,
                fecha_programada: document.getElementById('editFecha').value,
                origen:           document.getElementById('editOrigen').value.trim(),
                destino:          document.getElementById('editDestino').value.trim(),
                kms:              document.getElementById('editKms').value || 0,
                precio:           document.getElementById('editPrecio').value || 0,
                estado:           document.getElementById('editEstado').value
            };

            fetch(API, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            })
            .then(res => res.json())
            .then(data => {
                btn.innerHTML = textoOriginal;
                btn.disabled = false;

                if (data.ok) {
                    cerrarModal();
                    mostrarMensaje(mensajeCabecera, 'exito', 'Porte actualizado.');
                    cargarPortes();
                } else {
                    mostrarMensaje(mensajeModal, 'error', data.error || 'No se pudo actualizar.');
                }
            })
            .catch(() => {
                btn.innerHTML = textoOriginal;
                btn.disabled = false;
                mostrarMensaje(mensajeModal, 'error', 'Error de red al guardar.');
            });
        });
    }

});
