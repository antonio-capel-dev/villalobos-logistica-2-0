<?php
// app/core/Model.php

namespace Core;

use PDO;

abstract class Model {

    protected PDO $db;

    public function __construct() {
        // Reutilizar la conexión ya definida en conexion.php
        // La variable $pdo queda disponible al hacer require
        require_once APP_ROOT . '/../backend/conexion.php';
        $this->db = $pdo;
    }
}
