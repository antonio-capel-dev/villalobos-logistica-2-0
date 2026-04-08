<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido.']);
    exit;
}

$cuerpo  = json_decode(file_get_contents('php://input'), true);
$origen  = trim($cuerpo['origen']  ?? '');
$destino = trim($cuerpo['destino'] ?? '');

if (!$origen || !$destino) {
    echo json_encode(['ok' => false, 'error' => 'Faltan origen o destino.']);
    exit;
}

if (!preg_match('/^[\w\s,\-áéíóúüñÁÉÍÓÚÜÑ\.]+$/u', $origen) ||
    !preg_match('/^[\w\s,\-áéíóúüñÁÉÍÓÚÜÑ\.]+$/u', $destino)) {
    echo json_encode(['ok' => false, 'error' => 'Caracteres no permitidos en la dirección.']);
    exit;
}

$script = realpath(__DIR__ . '/../../modules/python/calculadora_distancias.py');

if (!$script || !file_exists($script)) {
    echo json_encode(['ok' => false, 'error' => 'Módulo Python no encontrado.']);
    exit;
}

$cmd   = 'python3 ' . escapeshellarg($script)
       . ' ' . escapeshellarg($origen)
       . ' ' . escapeshellarg($destino)
       . ' 2>&1';

$salida = shell_exec($cmd);
$datos  = json_decode($salida, true);

if ($datos === null) {
    echo json_encode(['ok' => false, 'error' => 'Error en el módulo Python.', 'raw' => $salida]);
    exit;
}

echo json_encode($datos, JSON_UNESCAPED_UNICODE);
