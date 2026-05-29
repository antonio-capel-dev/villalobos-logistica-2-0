<?php
/**
 * calcular_distancia.php — Villalobos Logística 2.0
 *
 * Estrategia dual:
 *  1. Intenta ejecutar el módulo Python (calculadora_distancias.py) via shell_exec.
 *  2. Si shell_exec está desactivado o Python no está disponible en el servidor,
 *     cae al fallback PHP nativo: llama a Nominatim OSM directamente con
 *     file_get_contents() y calcula la distancia Haversine en PHP puro.
 *
 * Ambas rutas devuelven el mismo JSON, de modo que el frontend no nota diferencia.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../conexion.php';

// ── CORS ────────────────────────────────────────────────────────────────────
$origenPermitido = env('CORS_ORIGIN', 'https://www.villaloboslogistica.com');
$origenSolicitud = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origenSolicitud === $origenPermitido || str_starts_with($origenSolicitud, 'http://localhost')) {
    header("Access-Control-Allow-Origin: $origenSolicitud");
    header("Vary: Origin");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido.']);
    exit;
}

// ── Validación de entrada ───────────────────────────────────────────────────
$cuerpo  = json_decode(file_get_contents('php://input'), true);
$origen  = trim($cuerpo['origen']  ?? '');
$destino = trim($cuerpo['destino'] ?? '');

if (!$origen || !$destino) {
    echo json_encode(['ok' => false, 'error' => 'Faltan origen o destino.']);
    exit;
}

$patron = '/^[\p{L}\d\s,\-\.]+$/u';
if (!preg_match($patron, $origen) || !preg_match($patron, $destino)) {
    echo json_encode(['ok' => false, 'error' => 'Caracteres no permitidos en la dirección.']);
    exit;
}

// ── Constantes de tarificación (mismas que el módulo Python) ────────────────
const PRECIO_KM   = 1.25;
const PRECIO_MIN  = 80.00;
const FACTOR_ROAD = 1.30;

// ── Fallback PHP: Nominatim + Haversine ─────────────────────────────────────

/**
 * Geocodifica una dirección usando la API pública de Nominatim (OpenStreetMap).
 * @return array{float, float}  [lat, lon]
 * @throws RuntimeException si la API no responde o no encuentra el lugar.
 */
function geocodificar_php(string $lugar): array
{
    $query = urlencode($lugar . ', España');
    $url   = "https://nominatim.openstreetmap.org/search?q={$query}&format=json&limit=1&accept-language=es";

    $ctx  = stream_context_create([
        'http' => [
            'method'  => 'GET',
            'header'  => "User-Agent: VillalobosLogistica-TFG/2.0 (info@villaloboslogistica.com)\r\n",
            'timeout' => 8,
        ],
    ]);

    $resp = @file_get_contents($url, false, $ctx);
    if ($resp === false) {
        throw new RuntimeException("Nominatim no respondió para: «{$lugar}»");
    }

    $datos = json_decode($resp, true);
    if (empty($datos)) {
        throw new RuntimeException("No se encontró la localización: «{$lugar}»");
    }

    return [(float) $datos[0]['lat'], (float) $datos[0]['lon']];
}

/**
 * Distancia entre dos puntos GPS por la fórmula del Haversine (km en línea recta).
 */
function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
{
    $R    = 6371.0;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a    = sin($dLat / 2) ** 2
          + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
    return $R * 2 * asin(sqrt($a));
}

/**
 * Calcula distancia y precio estimado sin depender de Python.
 */
function calcular_distancia_php(string $origen, string $destino): array
{
    [$lat1, $lon1] = geocodificar_php($origen);
    sleep(0);                          // Nominatim permite 1 req/s; en PHP el tiempo de la req ya actúa de throttle
    [$lat2, $lon2] = geocodificar_php($destino);

    $km_recta     = haversine($lat1, $lon1, $lat2, $lon2);
    $km_carretera = round($km_recta * FACTOR_ROAD, 1);
    $precio       = round(max(PRECIO_MIN, $km_carretera * PRECIO_KM), 2);

    return [
        'ok'              => true,
        'origen'          => $origen,
        'destino'         => $destino,
        'km'              => $km_carretera,
        'precio_estimado' => $precio,
        'motor'           => 'php',          // ayuda en debug: indica qué motor calculó
        'nota'            => 'Precio orientativo. El presupuesto final puede variar según peso y tipo de carga.',
    ];
}

// ── Intento 1: módulo Python ─────────────────────────────────────────────────
$datos = null;

$script = realpath(__DIR__ . '/../../modules/python/calculadora_distancias.py');

if ($script && file_exists($script) && function_exists('shell_exec')) {
    $pythonBin = stripos(PHP_OS, 'WIN') === 0 ? 'python' : 'python3';
    $cmd       = $pythonBin
               . ' ' . escapeshellarg($script)
               . ' ' . escapeshellarg($origen)
               . ' ' . escapeshellarg($destino)
               . ' 2>/dev/null';

    $salida = @shell_exec($cmd);
    if ($salida !== null) {
        $parsed = json_decode($salida, true);
        if (is_array($parsed) && isset($parsed['ok'])) {
            $parsed['motor'] = 'python';   // debug
            $datos = $parsed;
        }
    }
}

// ── Intento 2: fallback PHP nativo ───────────────────────────────────────────
if ($datos === null) {
    try {
        $datos = calcular_distancia_php($origen, $destino);
    } catch (RuntimeException $e) {
        error_log('calcular_distancia fallback PHP: ' . $e->getMessage());
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

echo json_encode($datos, JSON_UNESCAPED_UNICODE);
