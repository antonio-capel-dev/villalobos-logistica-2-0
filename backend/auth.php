<?php
// backend/auth.php

// Iniciar sesión de forma segura si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    // Configuración básica de seguridad para cookies de sesión
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

/**
 * Verifica si el usuario está logueado.
 * Si no lo está, retorna 401 y detiene la ejecución (opcional).
 */
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'No autorizado']);
        exit;
    }
}

/**
 * Verifica si el usuario tiene rol de admin.
 */
function requireAdmin() {
    requireLogin();
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Acceso denegado']);
        exit;
    }
}

/**
 * Devuelve el ID del usuario actual o null
 */
function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}
?>