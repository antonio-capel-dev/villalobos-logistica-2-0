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
            </div>
        </section>
    </div>

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
    </script>
</body>
</html>
