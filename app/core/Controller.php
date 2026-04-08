<?php
// app/core/Controller.php

namespace Core;

abstract class Controller {

    // Renderiza una vista dentro del layout principal
    protected function render(string $vista, array $datos = []): void {
        // Exponer las variables al scope de la vista
        extract($datos);

        // La ruta de la vista se pasa al layout para que la incluya
        $viewContent = APP_ROOT . '/views/' . $vista . '.php';

        require_once APP_ROOT . '/views/layouts/main.php';
    }

    // Redirige a una URL
    protected function redirigir(string $url): void {
        header('Location: ' . URL_ROOT . '/' . ltrim($url, '/'));
        exit;
    }

    // Comprueba si hay sesión activa; si no, redirige al login
    protected function requiereAuth(): void {
        if (!isset($_SESSION['user_id'])) {
            $this->redirigir('panel/login.php');
        }
    }

    // Comprueba si el usuario tiene un rol concreto
    protected function requiereRol(string $rol): void {
        $this->requiereAuth();
        if (($_SESSION['rol'] ?? '') !== $rol) {
            http_response_code(403);
            echo '<h1>403 — Acceso denegado</h1>';
            exit;
        }
    }
}
