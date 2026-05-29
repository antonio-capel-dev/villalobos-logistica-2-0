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
$_SESSION['chat_lead_envios'] = array_filter(
    $_SESSION['chat_lead_envios'] ?? [],
    fn($t) => $t > $ahora - 600
);
if (count($_SESSION['chat_lead_envios']) >= 5) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'Demasiadas solicitudes. Inténtalo en unos minutos.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true) ?: [];

// Límites de longitud
$nombre   = mb_substr(trim((string) ($data['nombre']   ?? '')), 0, 100);
$contacto = mb_substr(trim((string) ($data['contacto'] ?? '')), 0, 120);
$servicio = mb_substr(trim((string) ($data['servicio'] ?? 'No especificado')), 0, 60);
$ruta     = mb_substr(trim((string) ($data['ruta']     ?? 'No especificada')), 0, 200);
$origen   = mb_substr(trim((string) ($data['origen']   ?? '')), 0, 20); // 'chatbot' o 'whatsapp'

if (empty($nombre) || empty($contacto)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Faltan nombre y datos de contacto.']);
    exit;
}

$_SESSION['chat_lead_envios'][] = $ahora;

try {
    // Enviar notificación al comercial antes de registrar para conocer si el email fue enviado
    $cuerpo  = "NUEVO LEAD DEL CHATBOT\n";
    $cuerpo .= "======================\n\n";
    $cuerpo .= "Nombre:   $nombre\n";
    $cuerpo .= "Contacto: $contacto\n";
    $cuerpo .= "Servicio: $servicio\n";
    $cuerpo .= "Ruta:     $ruta\n";
    $cuerpo .= "Canal:    " . ($origen ?: 'chatbot') . "\n";

    $emailEnviado = enviarEmail("Nuevo lead chatbot: $servicio – $nombre", $cuerpo) ? 1 : 0;

    $stmt = $pdo->prepare("INSERT INTO chat_leads (nombre, contacto, servicio, ruta, email_enviado, leido) VALUES (:nombre, :contacto, :servicio, :ruta, :email_enviado, 0)");
    $stmt->execute([
        ':nombre'        => $nombre,
        ':contacto'      => $contacto,
        ':servicio'      => $servicio,
        ':ruta'          => $ruta,
        ':email_enviado' => $emailEnviado,
    ]);

    http_response_code(201);
    echo json_encode(['ok' => true, 'mensaje' => 'Lead guardado correctamente.']);
} catch (PDOException $e) {
    error_log('chat_lead: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error al guardar el lead en BD.']);
}
