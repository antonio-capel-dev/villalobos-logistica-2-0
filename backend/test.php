<?php

header('Content-Type: application/json');
require __DIR__.'/conexion.php';

try {
    $stmt = $pdo->query("SELECT NOW() as ahora;
    $fila = $stmt->fetch();
    echo
}

require_once 'conexion.php';

echo "Conexión exitosa con la base de datos <br>";
echo "Base de datos villalobos_logistica_2<br>";

// Esto es para contar los usuarios
$stmt = $pdo->query('SELECT COUNT(*) FROM usuarios');
$count = $stmt->fetchColumn();
echo "Usuarios en la BD: $count<br>";

//Contar los portes
$stmt = $pdo->query('SELECT COUNT(*) FROM portes');
$count = $stmt->fetchColumn();
echo "Número de portes en la BD:  $count<br>";

?>