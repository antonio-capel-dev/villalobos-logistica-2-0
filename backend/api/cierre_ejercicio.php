<?php
header('Content-Type: application/json; charset=UTF-8');

require_once '../conexion.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Acceso restringido a administradores.']);
    exit;
}

$mes = $_GET['mes'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $mes)) {
    echo json_encode(['ok' => false, 'error' => 'Formato de mes incorrecto. Use YYYY-MM.']);
    exit;
}

$jar = realpath(__DIR__ . '/../../modules/java/EstadisticasBilling.jar');

if (!$jar || !file_exists($jar)) {
    echo json_encode([
        'ok'          => false,
        'error'       => 'Módulo Java no compilado.',
        'instruccion' => 'Ejecuta modules/java/compilar.sh o compilar.bat para generar el JAR.'
    ]);
    exit;
}

$cmd    = 'java -jar ' . escapeshellarg($jar) . ' ' . escapeshellarg($mes) . ' 2>&1';
$salida = shell_exec($cmd);
$datos  = json_decode($salida, true);

if ($datos === null) {
    echo json_encode(['ok' => false, 'error' => 'Error en el módulo Java.', 'raw' => $salida]);
    exit;
}

echo json_encode($datos, JSON_UNESCAPED_UNICODE);
