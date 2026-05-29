<?php
declare(strict_types=1);

require_once __DIR__ . '/../conexion.php';

$origenPermitido = env('CORS_ORIGIN', 'https://www.villaloboslogistica.com');
$origenSolicitud = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origenSolicitud === $origenPermitido || str_starts_with($origenSolicitud, 'http://localhost')) {
    header("Access-Control-Allow-Origin: $origenSolicitud");
    header("Vary: Origin");
}
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok' => true, 'mensaje' => 'No habia sesion activa.']);
    exit;
}

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

echo json_encode(['ok' => true, 'mensaje' => 'Sesion cerrada exitosamente.']);