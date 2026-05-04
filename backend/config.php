<?php
declare(strict_types=1);

// Función para cargar el archivo .env a mano sin usar librerías externas
function cargarEnv(string $rutaEnv): void
{
    if (!is_readable($rutaEnv)) {
        return;
    }
    $lineas = file($rutaEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lineas as $linea) {
        $linea = trim($linea);
        if ($linea === '' || str_starts_with($linea, '#')) {
            continue;
        }
        if (!str_contains($linea, '=')) {
            continue;
        }
        [$clave, $valor] = array_map('trim', explode('=', $linea, 2));
        // Quitar comillas envolventes si las hay
        if (strlen($valor) >= 2 && (
            ($valor[0] === '"' && substr($valor, -1) === '"') ||
            ($valor[0] === "'" && substr($valor, -1) === "'")
        )) {
            $valor = substr($valor, 1, -1);
        }
        if (getenv($clave) === false) {
            putenv("$clave=$valor");
            $_ENV[$clave] = $valor;
        }
    }
}

cargarEnv(__DIR__ . '/../.env');

// Ayudante para sacar valores del .env o devolver uno por defecto
function env(string $clave, ?string $defecto = null): ?string
{
    $valor = getenv($clave);
    return ($valor === false || $valor === '') ? $defecto : $valor;
}