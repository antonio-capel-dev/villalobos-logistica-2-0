<?php

    $host = 'localhost';
    $dbname = 'villalobos_logistica_2'; // ← Cambia esto
    $usuario = 'root';                // ← Usuario de MySQL
    $password = '';
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $usuario,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
} catch (PDOException $e) {
    $pdo = null;
    $error_conexion = "Error de conexión: " . $e->getMessage();
}