<?php
require_once '../backend/conexion.php';
require_once '../backend/auth_guard.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Villalobos Logística 2.0</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../public/assets/css/styles.css">
</head>
<body class="fondo-panel">

    <div class="contenedor-principal">
        <header class="cabecera-panel">
            <h2>Panel de Control</h2>
            <div>
                <span title="Usuario conectado"><i class="fas fa-user" aria-hidden="true"></i> <?php echo htmlspecialchars($_SESSION['nombre']); ?> <span class="insignia-rol"><?php echo htmlspecialchars($_SESSION['rol']); ?></span></span>
                <button id="botonCerrarSesion" class="boton boton-contorno boton-pequeno" style="margin-left: 1rem;" aria-label="Cerrar sesión de usuario">Cerrar Sesión</button>
            </div>
        </header>

        <!-- Tarjetas KPI -->
        <div class="grid-kpi" id="gridKpi">
            <div class="tarjeta-kpi" id="kpiTotalPortes">
                <div class="kpi-icono" style="background:#dbeafe;"><i class="fas fa-truck" style="color:#2563eb;"></i></div>
                <div>
                    <p class="kpi-etiqueta">Total Portes</p>
                    <p class="kpi-valor" id="valTotalPortes">...</p>
                </div>
            </div>
            <div class="tarjeta-kpi" id="kpiPendientes">
                <div class="kpi-icono" style="background:#fef3c7;"><i class="fas fa-clock" style="color:#d97706;"></i></div>
                <div>
                    <p class="kpi-etiqueta">Pendientes</p>
                    <p class="kpi-valor" id="valPendientes">...</p>
                </div>
            </div>
            <div class="tarjeta-kpi" id="kpiEntregados">
                <div class="kpi-icono" style="background:#d1fae5;"><i class="fas fa-check-circle" style="color:#059669;"></i></div>
                <div>
                    <p class="kpi-etiqueta">Entregados</p>
                    <p class="kpi-valor" id="valEntregados">...</p>
                </div>
            </div>
            <div class="tarjeta-kpi" id="kpiIngresos">
                <div class="kpi-icono" style="background:#f0fdf4;"><i class="fas fa-euro-sign" style="color:#16a34a;"></i></div>
                <div>
                    <p class="kpi-etiqueta">Ingresos Facturados</p>
                    <p class="kpi-valor" id="valIngresos">...</p>
                </div>
            </div>
            <div class="tarjeta-kpi" id="kpiMensajes">
                <div class="kpi-icono" style="background:#fee2e2;"><i class="fas fa-envelope" style="color:#dc2626;"></i></div>
                <div>
                    <p class="kpi-etiqueta">Mensajes sin leer</p>
                    <p class="kpi-valor" id="valMensajes">...</p>
                </div>
            </div>
        </div>

        <section class="tarjeta">
            <h3>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h3>
            <p>Sesión activa como <strong><?php echo htmlspecialchars($_SESSION['rol']); ?></strong> · ID <?php echo $_SESSION['user_id']; ?></p>
            
            <hr class="separador-panel">

            <div id="ultimoPorte" style="margin-bottom: 1.5rem; color: #64748b; font-size: 0.95rem;"></div>
            
            <div class="grupo-botones">
                <a href="portes.php" class="boton boton-primario" title="Ir a la gestión de portes">Gestionar Portes</a>
                <a href="mensajes.php" class="boton boton-secundario" title="Ver mensajes de contacto"><i class="fas fa-envelope"></i> Mensajes</a>
                <a href="../public/index.html" class="boton boton-secundario" title="Salir a la página web pública">Ir a la Web Pública</a>
                <?php if ($_SESSION['rol'] === 'admin'): ?>
                <button id="btnCierreEjercicio" class="boton boton-java" title="Módulo Java: estadísticas de rentabilidad mensual">
                    <i class="fas fa-chart-line"></i> Cierre de Ejercicio <span class="badge-java">Java</span>
                </button>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- ── Modal Cierre de Ejercicio (Módulo Java) ──────────────────────── -->
    <div id="modalJava" class="modal-java" hidden>
        <div class="modal-java-caja">
            <div class="modal-java-cabecera">
                <h3><i class="fas fa-chart-line"></i> Cierre de Ejercicio &mdash; Módulo Java</h3>
                <button id="modalJavaCerrar" class="modal-java-cerrar" aria-label="Cerrar">&times;</button>
            </div>
            <div id="modalJavaContenido" class="modal-java-contenido">
                <div class="java-cargando">
                    <i class="fas fa-spinner fa-spin"></i> Ejecutando módulo Java&hellip;
                </div>
            </div>
        </div>
    </div>

    <style>
    /* Botón Java */
    .boton-java {
        background: #0f172a;
        color: #fff;
        border: none;
        padding: .55rem 1.1rem;
        border-radius: 8px;
        font-size: .9rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        transition: background .2s;
    }
    .boton-java:hover { background: #1e3a5f; }
    .badge-java {
        background: #e63946;
        color: #fff;
        font-size: .65rem;
        padding: .1rem .4rem;
        border-radius: 999px;
        font-weight: 700;
        letter-spacing: .04em;
    }
    /* Modal */
    .modal-java {
        position: fixed; inset: 0;
        background: rgba(0,0,0,.55);
        display: flex; align-items: center; justify-content: center;
        z-index: 9999;
    }
    .modal-java[hidden] { display: none; }
    .modal-java-caja {
        background: #fff;
        border-radius: 16px;
        width: min(92vw, 560px);
        box-shadow: 0 24px 60px rgba(0,0,0,.25);
        overflow: hidden;
    }
    .modal-java-cabecera {
        background: #0f172a;
        color: #fff;
        padding: 1.1rem 1.4rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .modal-java-cabecera h3 { margin: 0; font-size: 1rem; }
    .modal-java-cerrar {
        background: none; border: none; color: #fff;
        font-size: 1.4rem; cursor: pointer; line-height: 1;
    }
    .modal-java-contenido { padding: 1.5rem; }
    .java-cargando { text-align: center; color: #64748b; padding: 2rem 0; font-size: 1rem; }
    .java-kpis {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .9rem;
        margin-bottom: 1.2rem;
    }
    .java-kpi {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: .9rem 1rem;
    }
    .java-kpi-label { font-size: .75rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; }
    .java-kpi-valor { font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-top: .2rem; }
    .java-estados { margin-top: .5rem; }
    .java-estados h4 { font-size: .85rem; color: #64748b; margin-bottom: .5rem; }
    .java-estado-fila { display: flex; justify-content: space-between; font-size: .9rem; padding: .25rem 0; border-bottom: 1px solid #f1f5f9; }
    .java-footer { margin-top: 1.2rem; font-size: .75rem; color: #94a3b8; text-align: center; }
    .java-error { color: #dc2626; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 1rem; font-size: .9rem; }
    </style>

    <script>

        // Cerrar sesión
        document.getElementById('botonCerrarSesion').addEventListener('click', function() {
            fetch('../backend/api/logout.php')
                .then(r => r.json())
                .then(d => { if (d.ok) window.location.href = 'login.php'; })
                .catch(e => console.error('Error al cerrar sesión:', e));
        });

        // Cargar estadísticas reales
        fetch('../backend/api/estadisticas.php')
            .then(r => r.json())
            .then(respuesta => {
                if (!respuesta.ok) return;
                const d = respuesta.data;

                document.getElementById('valTotalPortes').textContent = d.total_portes;
                document.getElementById('valIngresos').textContent = d.ingresos_entregados + ' €';
                document.getElementById('valMensajes').textContent = d.mensajes_sin_leer;

                // Extraer pendientes y entregados del array por_estado
                let pendientes = 0, entregados = 0;
                d.por_estado.forEach(item => {
                    if (item.estado === 'pendiente') pendientes = item.total;
                    if (item.estado === 'entregado') entregados = item.total;
                });
                document.getElementById('valPendientes').textContent = pendientes;
                document.getElementById('valEntregados').textContent = entregados;

                // Último porte
                if (d.ultimo_porte) {
                    const p = d.ultimo_porte;
                    const contenedor = document.getElementById('ultimoPorte');
                    const texto = document.createElement('p');
                    texto.textContent = 'Último porte registrado: ' + p.origen + ' → ' + p.destino + ' (' + p.fecha_programada + ')';
                    contenedor.appendChild(texto);
                }
            })
            .catch(e => console.warn('No se pudieron cargar las estadísticas:', e));
        // ── Módulo Java: Cierre de Ejercicio ─────────────────────────────────
        const btnJava   = document.getElementById('btnCierreEjercicio');
        const modal     = document.getElementById('modalJava');
        const modalBody = document.getElementById('modalJavaContenido');
        const btnCerrar = document.getElementById('modalJavaCerrar');

        if (btnJava) {
            btnJava.addEventListener('click', () => {
                modal.hidden = false;
                modalBody.innerHTML = '<div class="java-cargando"><i class="fas fa-spinner fa-spin"></i> Ejecutando módulo Java&hellip;</div>';

                fetch('../backend/api/cierre_ejercicio.php')
                    .then(r => r.json())
                    .then(d => {
                        if (!d.ok) {
                            modalBody.innerHTML = `<div class="java-error"><i class="fas fa-exclamation-triangle"></i> ${d.error}<br><small>${d.instruccion ?? d.raw ?? ''}</small></div>`;
                            return;
                        }
                        const estados = Object.entries(d.por_estado)
                            .map(([k,v]) => `<div class="java-estado-fila"><span>${k}</span><strong>${v}</strong></div>`)
                            .join('');
                        modalBody.innerHTML = `
                            <div class="java-kpis">
                                <div class="java-kpi">
                                    <div class="java-kpi-label">Ingresos del mes</div>
                                    <div class="java-kpi-valor">${parseFloat(d.ingresos_mes).toFixed(2)} &euro;</div>
                                </div>
                                <div class="java-kpi">
                                    <div class="java-kpi-label">Total portes</div>
                                    <div class="java-kpi-valor">${d.total_portes}</div>
                                </div>
                                <div class="java-kpi">
                                    <div class="java-kpi-label">Km totales</div>
                                    <div class="java-kpi-valor">${parseFloat(d.km_totales).toFixed(0)}</div>
                                </div>
                                <div class="java-kpi">
                                    <div class="java-kpi-label">Media por porte</div>
                                    <div class="java-kpi-valor">${parseFloat(d.media_porte).toFixed(2)} &euro;</div>
                                </div>
                            </div>
                            <div class="java-estados">
                                <h4>Desglose por estado</h4>
                                ${estados || '<p style="color:#94a3b8">Sin datos este mes.</p>'}
                            </div>
                            <p style="margin-top:.8rem;font-size:.9rem;">
                                <i class="fas fa-user"></i> Conductor más activo: <strong>${d.conductor_top}</strong>
                                (${d.portes_conductor} portes)
                            </p>
                            <div class="java-footer">Análisis generado por el módulo Java &mdash; Mes: ${d.mes}</div>
                        `;
                    })
                    .catch(() => {
                        modalBody.innerHTML = '<div class="java-error">Error de conexión al ejecutar el módulo Java.</div>';
                    });
            });
        }

        if (btnCerrar) btnCerrar.addEventListener('click', () => { modal.hidden = true; });
        if (modal)     modal.addEventListener('click', e => { if (e.target === modal) modal.hidden = true; });

    </script>
</body>
</html>
