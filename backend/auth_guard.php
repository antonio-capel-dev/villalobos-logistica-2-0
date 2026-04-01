<?php
// backend/auth_guard.php



// Comprobar si NO hay una sesión de usuario activa
if (!isset($_SESSION['user_id'])) {
    // Si no está logueado, redirigir al login
    // Como este archivo se incluirá desde /panel/archivo.php, la ruta relativa a login.php es en la misma carpeta
    header("Location: login.php");
    exit;
}

// Opcional: Función de ayuda para comprobar roles rápidamente en las vistas
// Ejemplo de uso: if(!tieneRol('admin')) { die("Acceso denegado"); }
function tieneRol($rolRequerido) {
    if (!isset($_SESSION['rol'])) return false;
    return $_SESSION['rol'] === $rolRequerido;
}
?>
