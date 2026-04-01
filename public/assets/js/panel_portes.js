// public/assets/js/panel_portes.js

document.addEventListener("DOMContentLoaded", () => {
    
    // ---------------------------------------------------------
    // 1. INICIALIZACIÓN DE NODOS DEL DOM
    // ---------------------------------------------------------
    const cuerpoTabla = document.getElementById("cuerpoTablaPortes");
    const formularioPorte = document.getElementById("formularioNuevoPorte");
    const botonActualizar = document.getElementById("botonActualizar");
    const mensajeFormulario = document.getElementById("mensajeFormulario");
    const mensajeCabecera = document.getElementById("mensajeGlobal");

    // Ruta unificada para las peticiones asíncronas
    const RUTA_API = '../backend/api/portes.php';

    // Disparamos la carga inicial
    cargarPortes();

    if(botonActualizar) {
        botonActualizar.addEventListener("click", cargarPortes);
    }

    // ---------------------------------------------------------
    // 2. LECTURA ASÍNCRONA (GET)
    // ---------------------------------------------------------
    function cargarPortes() {
        // Indicador de carga visual (seguro porque es un string literal nuestro)
        cuerpoTabla.innerHTML = '<tr><td colspan="6" class="alineacion-centro texto-secundario"><i class="fas fa-spinner fa-spin"></i> Cargando datos...</td></tr>';
        
        fetch(RUTA_API)
            .then(respuestaServidor => {
                if (!respuestaServidor.ok) throw new Error("Error HTTP: " + respuestaServidor.status);
                return respuestaServidor.json();
            })
            .then(datosRecibidos => {
                if (datosRecibidos.ok) {
                    renderizarTabla(datosRecibidos.data);
                } else {
                    mostrarErrorTabla(datosRecibidos.error || "Ocurrió un error al cargar la lista.");
                }
            })
            .catch(errorRed => {
                console.error("Fallo de conexión en Fetch GET:", errorRed);
                mostrarErrorTabla("Error de red. El servidor backend no responde.");
            });
    }

    // Constructor de DOM seguro contra XSS
    function renderizarTabla(listaPortes) {
        cuerpoTabla.innerHTML = ''; // Limpiamos la tabla primero

        if (!listaPortes || listaPortes.length === 0) {
            cuerpoTabla.innerHTML = '<tr><td colspan="6" class="alineacion-centro texto-secundario">No hay portes registrados en este momento.</td></tr>';
            return;
        }

        // Ensamblaje iterativo del DOM
        listaPortes.forEach(porte => {
            const fila = document.createElement("tr");

            // Función global desde utilidades.js para prevención XSS
            fila.appendChild(crearCelda(porte.id));
            fila.appendChild(crearCelda(porte.fecha_programada));
            fila.appendChild(crearCelda(porte.origen));
            fila.appendChild(crearCelda(porte.destino));

            // Celda de Estado (Insignia dinámica sin innerHTML)
            const celdaEstado = document.createElement("td");
            const insigniaEstatus = document.createElement("span");
            insigniaEstatus.textContent = porte.estado.toUpperCase();
            insigniaEstatus.className = `insignia-estado estado-${porte.estado}`; 
            celdaEstado.appendChild(insigniaEstatus);
            fila.appendChild(celdaEstado);

            // Celda de Acciones (Botones funcionales)
            const celdaAcciones = document.createElement("td");
            const botonEliminar = document.createElement("button");
            botonEliminar.className = "boton-pequeno boton-peligro";
            botonEliminar.textContent = "Borrar";
            
            // Asignación de evento local (closure seguro)
            botonEliminar.addEventListener('click', () => borrarPorte(porte.id));

            const botonEditar = document.createElement('button');
            botonEditar.className = 'boton-pequeno boton-editar';
            botonEditar.textContent = 'Editar';
            botonEditar.addEventListener('click', () => abrirModalEditar(porte));

            celdaAcciones.appendChild(botonEditar);
            celdaAcciones.appendChild(document.createTextNode(' '));
            celdaAcciones.appendChild(botonEliminar);
            fila.appendChild(celdaAcciones);

            // Inserción en el árbol principal
            cuerpoTabla.appendChild(fila);
        });
    }

    function mostrarErrorTabla(textoError) {
        cuerpoTabla.innerHTML = '';
        const fila = document.createElement("tr");
        const celdaError = document.createElement("td");
        celdaError.colSpan = 6;
        celdaError.className = "alineacion-centro mensaje-error";
        celdaError.textContent = textoError;
        fila.appendChild(celdaError);
        cuerpoTabla.appendChild(fila);
    }

    // ---------------------------------------------------------
    // 3. CREACIÓN ASÍNCRONA (POST)
    // ---------------------------------------------------------
    if(formularioPorte) {
        formularioPorte.addEventListener("submit", function(evento) {
            
            evento.preventDefault(); // Evitamos recarga de la web
            
            ocultarMensaje(mensajeFormulario);
            
            // Estado de UX (Bloqueo y Carga)
            const botonGuardar = document.getElementById("botonGuardar");
            const textoBotonOriginal = botonGuardar.innerHTML;
            botonGuardar.innerHTML = '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Guardando...';
            botonGuardar.disabled = true;

            // Formato JSON que exige nuestro backend PHP
            const datosEnvio = {
                fecha_programada: document.getElementById("fecha").value,
                origen: document.getElementById("origen").value.trim(),
                destino: document.getElementById("destino").value.trim(),
                kms: document.getElementById("kms").value || 0,
                precio: document.getElementById("precio").value || 0
            };

            fetch(RUTA_API, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json' 
                },
                body: JSON.stringify(datosEnvio)
            })
            .then(respuestaServidor => {
                // Validación explícita de protocolo HTTP
                if (!respuestaServidor.ok && respuestaServidor.status !== 400 && respuestaServidor.status !== 401) {
                    throw new Error("Respuesta no esperada del servidor");
                }
                return respuestaServidor.json();
            })
            .then(datosRecibidos => {
                // Liberar interfaz
                botonGuardar.innerHTML = textoBotonOriginal;
                botonGuardar.disabled = false;

                // Resolución lógica
                if (datosRecibidos.ok) {
                    mostrarMensaje(mensajeFormulario, "exito", datosRecibidos.mensaje || "Porte añadido correctamente al sistema.");
                    formularioPorte.reset(); // Vaciamos formulario
                    cargarPortes(); // Actualización transparente
                } else {
                    mostrarMensaje(mensajeFormulario, "error", datosRecibidos.error || "No se pudo registrar el porte.");
                }
            })
            .catch(errorRed => {
                botonGuardar.innerHTML = textoBotonOriginal;
                botonGuardar.disabled = false;
                console.error("Excepción en POST:", errorRed);
                mostrarMensaje(mensajeFormulario, "error", "Error de red. Verifique la conectividad con la API.");
            });
        });
    }

    // ---------------------------------------------------------
    // 4. ELIMINACIÓN ASÍNCRONA (DELETE)
    // ---------------------------------------------------------
    function borrarPorte(identificadorPorte) {
        // Doble validación nativa (Medida de prevención frente a borrados accidentales)
        if (!confirm(`Va a eliminar permanentemente el porte con ID #${identificadorPorte}. ¿Realmente desea continuar?`)) {
            return;
        }

        ocultarMensaje(mensajeCabecera);

        fetch(RUTA_API, {
            method: 'DELETE',
            // Enfoque REST: Enviamos cuerpo JSON indicando el ID a borrar
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json' 
            },
            body: JSON.stringify({ id: identificadorPorte })
        })
        .then(respuestaServidor => {
            if(!respuestaServidor.ok && respuestaServidor.status !== 404 && respuestaServidor.status !== 400) {
                throw new Error("Respuesta inesperada del gestor.");
            }
            return respuestaServidor.json();
        })
        .then(datosRecibidos => {
            if (datosRecibidos.ok) {
                mostrarMensaje(mensajeCabecera, "exito", `El porte #${identificadorPorte} ha sido eliminado con éxito.`);
                cargarPortes(); // Refrescar la vista
            } else {
                mostrarMensaje(mensajeCabecera, "error", datosRecibidos.error || "El gestor denegó la operación de borrado.");
            }
        })
        .catch(errorRed => {
            console.error("Excepción en DELETE:", errorRed);
            mostrarMensaje(mensajeCabecera, "error", "Imposible contactar con el endpoint para el borrado.");
        });
    }

    // ---------------------------------------------------------
    // 5. MODAL DE EDICIÓN (PUT)
    // ---------------------------------------------------------
    const modalEditar = document.getElementById('modalEditar');
    const formularioEditar = document.getElementById('formularioEditarPorte');
    const mensajeModal = document.getElementById('mensajeModal');

    function abrirModalEditar(porte) {
        // Rellenar el formulario con los datos actuales del porte
        document.getElementById('editId').value = porte.id;
        document.getElementById('modalPorteId').textContent = '#' + porte.id;
        document.getElementById('editFecha').value = porte.fecha_programada;
        document.getElementById('editOrigen').value = porte.origen;
        document.getElementById('editDestino').value = porte.destino;
        document.getElementById('editKms').value = porte.kms || '';
        document.getElementById('editPrecio').value = porte.precio || '';
        document.getElementById('editEstado').value = porte.estado;

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

    // Cerrar al hacer clic en el fondo oscuro
    modalEditar.addEventListener('click', function(e) {
        if (e.target === modalEditar) cerrarModal();
    });

    // Cerrar con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modalEditar.hidden) cerrarModal();
    });

    if (formularioEditar) {
        formularioEditar.addEventListener('submit', function(e) {
            e.preventDefault();

            ocultarMensaje(mensajeModal);

            const botonGuardar = document.getElementById('botonGuardarEdicion');
            const textoOriginal = botonGuardar.innerHTML;
            botonGuardar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
            botonGuardar.disabled = true;

            const datosActualizados = {
                id: document.getElementById('editId').value,
                fecha_programada: document.getElementById('editFecha').value,
                origen: document.getElementById('editOrigen').value.trim(),
                destino: document.getElementById('editDestino').value.trim(),
                kms: document.getElementById('editKms').value || 0,
                precio: document.getElementById('editPrecio').value || 0,
                estado: document.getElementById('editEstado').value
            };

            fetch(RUTA_API, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(datosActualizados)
            })
            .then(respuestaServidor => {
                if (!respuestaServidor.ok && respuestaServidor.status !== 400) {
                    throw new Error('Error HTTP: ' + respuestaServidor.status);
                }
                return respuestaServidor.json();
            })
            .then(datosRecibidos => {
                botonGuardar.innerHTML = textoOriginal;
                botonGuardar.disabled = false;

                if (datosRecibidos.ok) {
                    cerrarModal();
                    mostrarMensaje(mensajeCabecera, 'exito', 'Porte actualizado correctamente.');
                    cargarPortes();
                } else {
                    mostrarMensaje(mensajeModal, 'error', datosRecibidos.error || 'No se pudo actualizar el porte.');
                }
            })
            .catch(errorRed => {
                botonGuardar.innerHTML = textoOriginal;
                botonGuardar.disabled = false;
                console.error('Error en PUT:', errorRed);
                mostrarMensaje(mensajeModal, 'error', 'Error de red al intentar guardar los cambios.');
            });
        });
    }


});
