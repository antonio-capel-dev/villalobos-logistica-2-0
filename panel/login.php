<?php
// panel/login.php

// Si el usuario ya está logueado, lo mandamos al dashboard directamente para evitar que vea el login
require_once '../backend/conexion.php';
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Privado - Villalobos Logística</title>
    <!-- Mismas fuentes y estilos que el resto del proyecto para coherencia visual -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../public/assets/css/styles.css">
</head>
<body class="fondo-panel contenedor-login">

    <div class="tarjeta-login">
        <!-- Añadido aria-label y limpiado el alt -->
        <img src="../public/assets/img/logo.png" alt="Logotipo de Villalobos Logística" aria-hidden="true" onerror="this.onerror=null; this.src=''; this.alt='Villalobos Logística';">
        <h2>Panel de Gestión</h2>
        <p class="texto-secundario margen-inferior-doble">Acceso exclusivo para empleados y clientes</p>

        <!-- Contenedores para mensajes AJAX con IDs castellanizados -->
        <div id="mensajeError" class="mensaje mensaje-error" aria-live="polite"></div>
        <div id="mensajeExito" class="mensaje mensaje-exito" aria-live="polite"></div>

        <form id="formularioLogin">
            <div class="grupo-formulario">
                <label for="email" class="etiqueta-formulario">Correo Electrónico</label>
                <!-- Evitamos autocompletados molestos e invalidamos HTML genérico -->
                <input type="email" id="email" name="email" class="control-formulario" placeholder="ejemplo@villalobos.local" required autocomplete="email">
            </div>
            
            <div class="grupo-formulario">
                <label for="password" class="etiqueta-formulario">Contraseña</label>
                <input type="password" id="password" name="password" class="control-formulario" placeholder="••••••••" required autocomplete="current-password">
            </div>
            
            <button type="submit" class="boton boton-primario boton-ancho" id="botonAcceso" aria-label="Iniciar sesión en el panel">
                <i class="fas fa-sign-in-alt" aria-hidden="true"></i> Entrar al Panel
            </button>
        </form>
        
        <p class="margen-superior-simple texto-pequeno">
            <a href="../public/index.html" class="enlace-primario" title="Volver a la portada web">&larr; Volver a la web pública</a>
        </p>
    </div>

    <!-- Cargamos nuestro script JavaScript puro -->
    <script src="../public/assets/js/utilidades.js"></script>
    <script src="../public/assets/js/auth.js"></script>
</body>
</html>
