<?php
declare(strict_types=1);

header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Metodo no soportado.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true) ?: [];

$nombre   = isset($data['nombre'])   ? trim((string) $data['nombre'])   : '';
$contacto = isset($data['contacto']) ? trim((string) $data['contacto']) : '';
$servicio = isset($data['servicio']) ? trim((string) $data['servicio']) : 'No especificado';
$ruta     = isset($data['ruta'])     ? trim((string) $data['ruta'])     : 'No especificada';
$origen   = isset($data['origen'])   ? trim((string) $data['origen'])   : ''; // 'chatbot' o 'whatsapp'

if (empty($nombre) || empty($contacto)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Faltan nombre y datos de contacto.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO chat_leads (nombre, contacto, servicio, ruta, email_enviado, leido) VALUES (:nombre, :contacto, :servicio, :ruta, 0, 0)");
    $stmt->execute([
        ':nombre'   => $nombre,
        ':contacto' => $contacto,
        ':servicio' => $servicio,
        ':ruta'     => $ruta
    ]);

    http_response_code(201);
    echo json_encode(['ok' => true, 'mensaje' => 'Lead guardado correctamente.']);
} catch (PDOException $e) {
    error_log('chat_lead: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error al guardar el lead en BD.']);
}
