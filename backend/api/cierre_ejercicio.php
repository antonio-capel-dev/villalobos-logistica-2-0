<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../conexion.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Acceso restringido a administradores.']);
    exit;
}

$mes = $_GET['mes'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $mes)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Formato de mes incorrecto. Use YYYY-MM.']);
    exit;
}

$script = realpath(__DIR__ . '/../../modules/python/estadisticas_billing.py');

if (!$script || !file_exists($script)) {
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => 'Modulo Python no encontrado.',
    ]);
    exit;
}

// Detectamos el ejecutable python disponible (Linux/Windows)
$pythonBin = stripos(PHP_OS, 'WIN') === 0 ? 'python' : 'python3';

$cmd = $pythonBin . ' ' . escapeshellarg($script) . ' ' . escapeshellarg($mes) . ' 2>&1';
$salida = shell_exec($cmd);

if ($salida === null) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'No se pudo ejecutar el modulo Python.']);
    exit;
}

$datos = json_decode($salida, true);

if ($datos === null) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Salida invalida del modulo Python.', 'raw' => $salida]);
    exit;
}

echo json_encode($datos, JSON_UNESCAPED_UNICODE);