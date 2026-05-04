<?php
declare(strict_types=1);

header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../conexion.php';
require_once __DIR__ . '/../mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Metodo no soportado.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true) ?: [];

$nombre   = isset($data['nombre'])   ? trim((string) $data['nombre'])   : '';
$servicio = isset($data['servicio']) ? trim((string) $data['servicio']) : '';
$origen   = isset($data['origen'])   ? trim((string) $data['origen'])   : '';
$destino  = isset($data['destino'])  ? trim((string) $data['destino'])  : '';
$mensaje  = isset($data['mensaje'])  ? trim((string) $data['mensaje'])  : '';

// El contacto puede llegar como email (formulario) o como campo libre del chat (telefono o email)
$contacto = isset($data['email'])    ? trim((string) $data['email'])    : '';
$telefono = isset($data['telefono']) ? trim((string) $data['telefono']) : '';

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

if (empty($nombre) || empty($contacto)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Faltan nombre y datos de contacto.']);
    exit;
}

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