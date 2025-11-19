<?php
ini_set('display_errors','1');
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once __DIR__ . '/conexion.php'; // aquí se define $pdo

try {
    if (!$pdo instanceof PDO) {
        throw new RuntimeException('PDO no inicializado (revisa nombre de BD/credenciales).');
    }

    $stmt = $pdo->query("SELECT NOW() AS ahora");
    $fila = $stmt->fetch();

    echo json_encode([
        'ok'        => true,
        'conexion'  => 'ok',
        'ahora'     => $fila['ahora'] ?? null
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
