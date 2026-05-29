<?php
declare(strict_types=1);

require_once __DIR__ . '/../conexion.php';

// CORS restringido al dominio de produccion (configurable via .env)
$origenPermitido = env('CORS_ORIGIN', 'https://www.villaloboslogistica.com');
$origenSolicitud = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origenSolicitud === $origenPermitido || str_starts_with($origenSolicitud, 'http://localhost')) {
    header("Access-Control-Allow-Origin: $origenSolicitud");
    header("Vary: Origin");
}
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Metodo no permitido. Usa POST.']);
    exit;
}

// Rate limit basico: 5 intentos fallidos / 10 minutos por sesion
$ahora = time();
$_SESSION['intentos_login'] = $_SESSION['intentos_login'] ?? [];
$_SESSION['intentos_login'] = array_filter(
    $_SESSION['intentos_login'],
    fn($t) => $t > $ahora - 600
);
if (count($_SESSION['intentos_login']) >= 5) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'Demasiados intentos. Intentalo en unos minutos.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true) ?: [];
$email    = isset($data['email'])    ? trim((string) $data['email'])    : '';
$password = isset($data['password']) ? trim((string) $data['password']) : '';

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'El email y la contrasena son obligatorios.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, nombre, email, password_hash, rol FROM usuarios WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($password, $usuario['password_hash'])) {
        // Login correcto: regeneramos ID y limpiamos contador
        session_regenerate_id(true);
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['nombre']  = $usuario['nombre'];
        $_SESSION['email']   = $usuario['email'];
        $_SESSION['rol']     = $usuario['rol'];
        $_SESSION['intentos_login'] = [];

        echo json_encode([
            'ok'      => true,
            'mensaje' => 'Login correcto',
            'usuario' => [
                'id'     => $usuario['id'],
                'nombre' => $usuario['nombre'],
                'rol'    => $usuario['rol'],
            ],
        ]);
        exit;
    }

    // Credenciales invalidas: registramos intento (mismo mensaje siempre, sin filtrar)
    $_SESSION['intentos_login'][] = $ahora;
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Credenciales incorrectas.']);

} catch (PDOException $e) {
    error_log('auth: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error de servidor.']);
}