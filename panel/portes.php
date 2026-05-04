<?php
// Protegemos la ruta: si no hay sesión, expulsa al login
require_once '../backend/conexion.php';
require_once '../backend/auth_guard.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Portes - Villalobos</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../public/assets/css/styles.css">
</head>
<body class="fondo-panel">
    <div class="contenedor-principal">
        <div class="cabecera-panel">
            <h2><i class="fas fa-truck"></i> Gestión de Portes</h2>
            <a href="dashboard.php" class="boton boton-contorno">&larr; Volver al Dashboard</a>
        </div>

        <!-- Mensajes Globales (para borrados, errores de fetch, etc) -->
        <div id="mensajeGlobal" class="mensaje" aria-live="polite"></div>

        <!-- Formulario para Añadir Nuevo Porte -->
        <div class="tarjeta">
            <h3>Añadir Nuevo Porte</h3>
            <div id="mensajeFormulario" class="mensaje" aria-live="polite"></div>
            
            <form id="formularioNuevoPorte">
                <div class="cuadricula-formulario">
                    <div>
                        <label for="fecha" class="etiqueta-formulario">Fecha Programada *</label>
                        <input type="date" id="fecha" class="control-formulario" required>
                    </div>
                    <div>
                        <label for="origen" class="etiqueta-formulario">Origen *</label>
                        <input type="text" id="origen" class="control-formulario" placeholder="Ej: Madrid" required>
                    </div>
                    <div>
                        <label for="destino" class="etiqueta-formulario">Destino *</label>
                        <input type="text" id="destino" class="control-formulario" placeholder="Ej: Barcelona" required>
                    </div>
                </div>
                <div class="cuadricula-formulario">
                    <div>
                        <label for="kms" class="etiqueta-formulario">Kilómetros</label>
                        <input type="number" step="0.01" id="kms" class="control-formulario" placeholder="Ej: 350.5">
                    </div>
                    <div>
                        <label for="precio" class="etiqueta-formulario">Precio (€)</label>
                        <input type="number" step="0.01" id="precio" class="control-formulario" placeholder="Ej: 400.00">
                    </div>
                </div>
                <div class="alineacion-derecha">
                    <button type="submit" class="boton boton-primario" id="botonGuardar" aria-label="Crear nuevo porte">
                        <i class="fas fa-save" aria-hidden="true"></i> Crear Porte
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabla Listado de Portes -->
        <div class="tarjeta contenedor-tabla">
            <div class="cabecera-tarjeta">
                <h3>Listado de Portes</h3>
                <button type="button" class="boton boton-contorno boton-pequeno" id="botonActualizar" aria-label="Actualizar lista de portes">
                    <i class="fas fa-sync-alt" aria-hidden="true"></i> Actualizar
                </button>
            </div>
            
            <table id="tablaPortes" class="tabla-datos">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTablaPortes">
                    <!-- Los datos se insertarán dinámicamente aquí vía JavaScript puro -->
                    <tr>
                        <td colspan="6" class="alineacion-centro texto-secundario">
                            Cargando portes...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>


    <!-- Modal de Edición de Porte -->
    <div id="modalEditar" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="tituloModalEditar" hidden>
        <div class="modal-contenido">
            <div class="modal-cabecera">
                <h3 id="tituloModalEditar"><i class="fas fa-edit"></i> Editar Porte <span id="modalPorteId"></span></h3>
                <button id="botonCerrarModal" class="boton-cerrar-modal" aria-label="Cerrar modal">&times;</button>
            </div>
            <div class="modal-cuerpo">
                <div id="mensajeModal" class="mensaje" aria-live="polite"></div>
                <form id="formularioEditarPorte">
                    <input type="hidden" id="editId">
                    <div class="cuadricula-formulario">
                        <div>
                            <label for="editFecha" class="etiqueta-formulario">Fecha Programada *</label>
                            <input type="date" id="editFecha" class="control-formulario" required>
                        </div>
                        <div>
                            <label for="editOrigen" class="etiqueta-formulario">Origen *</label>
                            <input type="text" id="editOrigen" class="control-formulario" required>
                        </div>
                        <div>
                            <label for="editDestino" class="etiqueta-formulario">Destino *</label>
                            <input type="text" id="editDestino" class="control-formulario" required>
                        </div>
                    </div>
                    <div class="cuadricula-formulario">
                        <div>
                            <label for="editKms" class="etiqueta-formulario">Kilómetros</label>
                            <input type="number" step="0.01" id="editKms" class="control-formulario">
                        </div>
                        <div>
                            <label for="editPrecio" class="etiqueta-formulario">Precio (€)</label>
                            <input type="number" step="0.01" id="editPrecio" class="control-formulario">
                        </div>
                        <div>
                            <label for="editEstado" class="etiqueta-formulario">Estado</label>
                            <select id="editEstado" class="control-formulario">
                                <option value="pendiente">Pendiente</option>
                                <option value="en_ruta">En Ruta</option>
                                <option value="entregado">Entregado</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>
                    </div>
                    <div class="alineacion-derecha">
                        <button type="button" id="botonCancelarEdicion" class="boton boton-contorno" style="margin-right: 0.5rem;">Cancelar</button>
                        <button type="submit" id="botonGuardarEdicion" class="boton boton-primario">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cargamos la lógica asíncrona -->
    <script src="../public/assets/js/utilidades.js"></script>
    <script src="../public/assets/js/panel_portes.js"></script>
</body>
</html>
