<?php
// backend/api/estadisticas.php
// Devuelve KPIs del negocio para el dashboard del panel

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

// Esta API es privada
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autorizado.']);
    exit;
}

try {
    // Total de portes
    $totalPortes = $pdo->query("SELECT COUNT(*) FROM portes")->fetchColumn();

    // Portes por estado
    $stmt = $pdo->query("SELECT estado, COUNT(*) as total FROM portes GROUP BY estado");
    $porEstado = $stmt->fetchAll();

    // Ingresos totales (suma de precios de portes entregados)
    $ingresos = $pdo->query("SELECT COALESCE(SUM(precio), 0) FROM portes WHERE estado = 'entregado'")->fetchColumn();

    // Mensajes sin leer
    $mensajesSinLeer = $pdo->query("SELECT COUNT(*) FROM mensajes_contacto WHERE leido = 0")->fetchColumn();

    // Último porte registrado
    $ultimoPorte = $pdo->query("SELECT origen, destino, fecha_programada, estado FROM portes ORDER BY id DESC LIMIT 1")->fetch();

    echo json_encode([
        'ok' => true,
        'data' => [
            'total_portes'       => (int) $totalPortes,
            'por_estado'         => $porEstado,
            'ingresos_entregados' => number_format((float) $ingresos, 2, '.', ''),
            'mensajes_sin_leer'  => (int) $mensajesSinLeer,
            'ultimo_porte'       => $ultimoPorte ?: null,
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error al obtener estadísticas.']);
}
?>
