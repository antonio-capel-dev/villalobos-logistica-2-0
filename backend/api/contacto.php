<?php
declare(strict_types=1);

require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../mailer.php';

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
    echo json_encode(['ok' => false, 'error' => 'Metodo no soportado.']);
    exit;
}

// Rate limit basico: 5 envios / 10 minutos por sesion
$ahora = time();
$_SESSION['contacto_envios'] = array_filter(
    $_SESSION['contacto_envios'] ?? [],
    fn($t) => $t > $ahora - 600
);
if (count($_SESSION['contacto_envios']) >= 5) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'Demasiadas solicitudes. Inténtalo en unos minutos.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true) ?: [];

// Límites de longitud para evitar payloads masivos
$nombre   = mb_substr(trim((string) ($data['nombre']   ?? '')), 0, 100);
$servicio = mb_substr(trim((string) ($data['servicio'] ?? '')), 0, 60);
$origen   = mb_substr(trim((string) ($data['origen']   ?? '')), 0, 120);
$destino  = mb_substr(trim((string) ($data['destino']  ?? '')), 0, 120);
$mensaje  = mb_substr(trim((string) ($data['mensaje']  ?? '')), 0, 1000);

// El contacto puede llegar como email (formulario) o como campo libre del chat (telefono o email)
$contacto = mb_substr(trim((string) ($data['email']    ?? '')), 0, 120);
$telefono = mb_substr(trim((string) ($data['telefono'] ?? '')), 0, 20);

$esTelefono = preg_match('/^[6789]\d{8}$/', preg_replace('/\s/', '', $contacto));
$esEmail    = filter_var($contacto, FILTER_VALIDATE_EMAIL);

$email = '';
if ($esTelefono) {
    $telefono = $contacto;
} elseif ($esEmail) {
    $email = $contacto;
} else {
    $email = $contacto;
}

// Normalización backend (segunda línea de defensa por si el JS fue saltado)
$nombre   = preg_replace('/\s+/', ' ', $nombre);          // colapsar espacios múltiples
$email    = strtolower($email);                           // email siempre en minúsculas
$telefono = preg_replace('/[\s\-().]/', '', $telefono);   // limpiar teléfono

if (empty($nombre) || mb_strlen($nombre) < 2) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'El nombre debe tener al menos 2 caracteres.']);
    exit;
}

if (empty($contacto)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Faltan datos de contacto.']);
    exit;
}

// Registrar envio en sesion (rate limiting)
$_SESSION['contacto_envios'][] = $ahora;

$mensajeCompleto = "Servicio: $servicio\n";
if ($origen)  $mensajeCompleto .= "Origen: $origen\n";
if ($destino) $mensajeCompleto .= "Destino: $destino\n";
if ($mensaje) $mensajeCompleto .= "Detalles: $mensaje\n";
$mensajeCompleto .= "Telefono: $telefono";

try {
    $stmt = $pdo->prepare("INSERT INTO mensajes_contacto (nombre, email, mensaje) VALUES (:nombre, :email, :mensaje)");
    $stmt->execute([':nombre' => $nombre, ':email' => $email, ':mensaje' => $mensajeCompleto]);

    $cuerpo  = "NUEVA SOLICITUD DE PRESUPUESTO\n";
    $cuerpo .= "================================\n\n";
    $cuerpo .= "Nombre:   $nombre\n";
    $cuerpo .= "Telefono: $telefono\n";
    $cuerpo .= "Email:    $email\n\n";
    $cuerpo .= "Servicio: $servicio\n";
    $cuerpo .= "Origen:   " . ($origen ?: 'No indicado') . "\n";
    $cuerpo .= "Destino:  " . ($destino ?: 'No indicado') . "\n\n";
    $cuerpo .= "Detalles:\n" . ($mensaje ?: 'Sin detalles adicionales');

    enviarEmail("Nueva solicitud: $servicio - $nombre", $cuerpo);

    http_response_code(201);
    echo json_encode(['ok' => true, 'mensaje' => 'Solicitud enviada. Le contactaremos pronto.']);
} catch (PDOException $e) {
    error_log('contacto: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error del servidor.']);
}