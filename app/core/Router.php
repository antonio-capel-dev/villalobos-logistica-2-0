<?php
// app/core/Router.php

namespace Core;

class Router {

    // Tabla de rutas: 'url' => ['Controller', 'metodo']
    private static array $rutas = [
        ''        => ['HomeController',  'index'],
        'home'    => ['HomeController',  'index'],
        'portes'  => ['PorteController', 'index'],
    ];

    public static function despachar(): void {
        // Extraer la ruta relativa eliminando el prefijo del proyecto
        $uri   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $base  = str_replace('/public/index.php', '', $_SERVER['SCRIPT_NAME']);
        $ruta  = trim(str_replace($base, '', $uri), '/');

        // Buscar la ruta en la tabla; si no existe, usar 404
        if (array_key_exists($ruta, self::$rutas)) {
            [$controlador, $metodo] = self::$rutas[$ruta];
        } else {
            self::error404();
            return;
        }

        // Cargar el controlador y ejecutar el método
        $clase = 'Controllers\\' . $controlador;
        if (class_exists($clase)) {
            (new $clase())->$metodo();
        } else {
            self::error404();
        }
    }

    private static function error404(): void {
        http_response_code(404);
        echo '<h1>404 — Página no encontrada</h1>';
    }
}
