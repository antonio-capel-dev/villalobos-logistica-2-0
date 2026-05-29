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
header('Content-Type: application/json; charset=UTF-8');

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

try {
    global $pdo;

    // 1. Total portes
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM portes WHERE DATE_FORMAT(fecha_programada,'%Y-%m') = ?");
    $stmt->execute([$mes]);
    $total_portes = (int)$stmt->fetchColumn();

    // 2. Ingresos
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(precio),0) FROM portes WHERE estado='entregado' AND DATE_FORMAT(fecha_programada,'%Y-%m') = ?");
    $stmt->execute([$mes]);
    $ingresos = round((float)$stmt->fetchColumn(), 2);

    // 3. KM totales
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(kms),0) FROM portes WHERE DATE_FORMAT(fecha_programada,'%Y-%m') = ?");
    $stmt->execute([$mes]);
    $km_totales = round((float)$stmt->fetchColumn(), 1);

    // 4. Media porte
    $stmt = $pdo->prepare("SELECT COALESCE(AVG(precio),0) FROM portes WHERE estado='entregado' AND DATE_FORMAT(fecha_programada,'%Y-%m') = ?");
    $stmt->execute([$mes]);
    $media_porte = round((float)$stmt->fetchColumn(), 2);

    // 5. Conductor top
    $stmt = $pdo->prepare("
        SELECT u.nombre, COUNT(*) AS total 
        FROM portes p 
        JOIN usuarios u ON p.conductor_id = u.id 
        WHERE DATE_FORMAT(p.fecha_programada,'%Y-%m') = ? 
        GROUP BY p.conductor_id 
        ORDER BY total DESC LIMIT 1
    ");
    $stmt->execute([$mes]);
    $conductor = $stmt->fetch(PDO::FETCH_ASSOC);
    $nombre_conductor = $conductor ? $conductor['nombre'] : 'Sin datos';
    $portes_conductor = $conductor ? (int)$conductor['total'] : 0;

    // 6. Portes por estado
    $stmt = $pdo->prepare("SELECT estado, COUNT(*) AS total FROM portes WHERE DATE_FORMAT(fecha_programada,'%Y-%m') = ? GROUP BY estado");
    $stmt->execute([$mes]);
    $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $por_estado = [];
    foreach ($estados as $est) {
        $por_estado[$est['estado']] = (int)$est['total'];
    }

    $resultado = [
        'ok' => True,
        'mes' => $mes,
        'total_portes' => $total_portes,
        'ingresos_mes' => $ingresos,
        'km_totales' => $km_totales,
        'media_porte' => $media_porte,
        'conductor_top' => $nombre_conductor,
        'portes_conductor' => $portes_conductor,
        'por_estado' => $por_estado,
    ];

    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error en base de datos: ' . $e->getMessage()]);
}