<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require_once '../conexion.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok' => true, 'mensaje' => 'No había sesión activa.']);
    exit;
}

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

echo json_encode(['ok' => true, 'mensaje' => 'Sesión cerrada exitosamente.']);
?>
