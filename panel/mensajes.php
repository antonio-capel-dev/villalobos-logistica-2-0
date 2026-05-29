<?php
// panel/mensajes.php — Vista de mensajes de contacto recibidos
require_once '../backend/conexion.php';
require_once '../backend/auth_guard.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes - Villalobos Logística</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../public/assets/css/styles.css">
    <link rel="stylesheet" href="../public/assets/css/styles-panel.css">
</head>
<body class="fondo-panel">
    <div class="contenedor-principal">

        <div class="cabecera-panel">
            <h2><i class="fas fa-envelope"></i> Mensajes de Contacto</h2>
            <a href="dashboard.php" class="boton boton-contorno">&larr; Volver al Dashboard</a>
        </div>

        <!-- Mensaje de feedback global -->
        <div id="mensajeGlobal" class="mensaje" aria-live="polite"></div>

        <!-- Tabla de mensajes -->
        <div class="tarjeta contenedor-tabla">
            <div class="cabecera-tarjeta">
                <h3>Bandeja de entrada</h3>
                <button id="botonActualizar" class="boton boton-contorno boton-pequeno">
                    <i class="fas fa-sync-alt"></i> Actualizar
                </button>
            </div>

            <table class="tabla-datos">
                <thead>
                    <tr>
                        <th>Estado</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Mensaje</th>
                        <th>Fecha</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTablaMensajes">
                    <tr>
                        <td colspan="6" class="alineacion-centro texto-secundario">Cargando mensajes...</td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

    <script src="../public/assets/js/utilidades.js"></script>
    <script>
    // -------------------------------------------------------
    // LÓGICA DE MENSAJES
    // La ruta de la API es relativa a panel/ → subimos con ../
    // -------------------------------------------------------
    const API = '../backend/api/mensajes.php';
    const cuerpo = document.getElementById('cuerpoTablaMensajes');
    const mensajeGlobal = document.getElementById('mensajeGlobal');

    // Carga inicial y botón actualizar
    cargarMensajes();
    document.getElementById('botonActualizar').addEventListener('click', cargarMensajes);

    function cargarMensajes() {
        cuerpo.innerHTML = '<tr><td colspan="6" class="alineacion-centro texto-secundario"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>';

        fetch(API)
            .then(r => r.json())
            .then(respuesta => {
                if (respuesta.ok) {
                    renderizarMensajes(respuesta.data);
                } else {
                    cuerpo.innerHTML = '<tr><td colspan="6" class="alineacion-centro mensaje-error">Error al cargar los mensajes.</td></tr>';
                }
            })
            .catch(() => {
                cuerpo.innerHTML = '<tr><td colspan="6" class="alineacion-centro mensaje-error">Error de red.</td></tr>';
            });
    }

    function renderizarMensajes(lista) {
        cuerpo.innerHTML = '';

        if (!lista || lista.length === 0) {
            cuerpo.innerHTML = '<tr><td colspan="6" class="alineacion-centro texto-secundario">No hay mensajes todavía.</td></tr>';
            return;
        }

        lista.forEach(msg => {
            const fila = document.createElement('tr');

            // Si no está leído, destacamos la fila
            if (msg.leido == 0) {
                fila.style.fontWeight = '600';
                fila.style.background = '#f0fdf4';
            }

            // Celda estado
            const tdEstado = document.createElement('td');
            const badge = document.createElement('span');
            badge.className = 'insignia-estado';
            if (msg.leido == 0) {
                badge.textContent = 'NUEVO';
                badge.style.background = '#dcfce7';
                badge.style.color = '#16a34a';
            } else {
                badge.textContent = 'LEÍDO';
                badge.style.background = '#f1f5f9';
                badge.style.color = '#64748b'; /* #94a3b8 = 2.45:1 sobre #f1f5f9 — falla WCAG AA. #64748b ✓ */
            }
            tdEstado.appendChild(badge);

            // Celdas de texto — usamos textContent para evitar XSS
            const tdNombre  = document.createElement('td');
            tdNombre.textContent = msg.nombre;

            const tdEmail   = document.createElement('td');
            tdEmail.textContent = msg.email;

            const tdMensaje = document.createElement('td');
            tdMensaje.textContent = msg.mensaje;
            tdMensaje.style.maxWidth = '300px';

            const tdFecha   = document.createElement('td');
            tdFecha.textContent = msg.creado_en;

            // Botón marcar como leído (solo si no está leído)
            const tdAccion  = document.createElement('td');
            if (msg.leido == 0) {
                const boton = document.createElement('button');
                boton.className = 'boton-pequeno boton-editar';
                boton.textContent = 'Marcar leído';
                boton.addEventListener('click', () => marcarLeido(msg.id));
                tdAccion.appendChild(boton);
            } else {
                tdAccion.textContent = '—';
            }

            fila.appendChild(tdEstado);
            fila.appendChild(tdNombre);
            fila.appendChild(tdEmail);
            fila.appendChild(tdMensaje);
            fila.appendChild(tdFecha);
            fila.appendChild(tdAccion);

            cuerpo.appendChild(fila);
        });
    }

    function marcarLeido(id) {
        fetch(API, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(r => r.json())
        .then(respuesta => {
            if (respuesta.ok) {
                mostrarMensaje(mensajeGlobal, 'exito', 'Mensaje marcado como leído.');
                cargarMensajes(); // recarga la tabla
            } else {
                mostrarMensaje(mensajeGlobal, 'error', respuesta.error);
            }
        })
        .catch(() => mostrarMensaje(mensajeGlobal, 'error', 'Error de red.'));
    }
    </script>
</body>
</html>
