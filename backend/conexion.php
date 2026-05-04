<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

// Endurecemos las cookies de sesion antes de iniciarla
if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

$DB_HOST = env('DB_HOST', 'localhost');
$DB_NAME = env('DB_NAME', 'villalobos_logistica_2');
$DB_USER = env('DB_USER', 'root');
$DB_PASS = env('DB_PASS', '') ?? '';

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log('PDO: ' . $e->getMessage());
    http_response_code(500);
    die(json_encode(['ok' => false, 'error' => 'Error de conexion a la base de datos']));
}