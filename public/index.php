<?php
// public/index.php — Punto de entrada único (Front Controller)

require_once '../app/config/config.php';

// Autoload: carga automáticamente las clases según su namespace
spl_autoload_register(function (string $clase): void {
    // Convierte "Core\Router" -> "app/core/Router.php"
    // Convierte "Controllers\HomeController" -> "app/controllers/HomeController.php"
    // Convierte "Models\PorteModel" -> "app/models/PorteModel.php"
    $partes = explode('\\', $clase);
    $carpeta = strtolower($partes[0]);   // core / controllers / models
    $archivo = $partes[1] . '.php';

    $ruta = APP_ROOT . '/' . $carpeta . '/' . $archivo;
    if (file_exists($ruta)) {
        require_once $ruta;
    }
});

// Delegar al router
Core\Router::despachar();
