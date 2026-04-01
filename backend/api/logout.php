<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require_once '../conexion.php'; 

$response = array();

// Si no hay sesión, simplemente devolvemos ok
if (!isset($_SESSION['user_id'])) {
    $response = array('ok' => true, 'mensaje' => 'No había sesión activa.');
    echo json_encode($response);
    exit;
}

// 1. Vaciar las variables de sesión
$_SESSION = array();

// 2. Destruir la cookie del navegador asociada a la sesión (PHP Session ID)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destruir la sesión en el servidor
session_destroy();

// 4. Devolver JSON de éxito
$response = array('ok' => true, 'mensaje' => 'Sesión cerrada exitosamente.');
echo json_encode($response);
?>
