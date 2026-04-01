<?php 
declare(strict_types=1);

// Iniciamos la sesión aquí para que esté disponible en toda la aplicación
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$DB_HOST = 'localhost';
$DB_NAME = 'villalobos_logistica_2';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
    );
}   catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['ok'=>false, 'error'=>'Error de conexión a la base de datos']));
}




?>