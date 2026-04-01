-- phpMyAdmin SQL Dump
-- Base de datos: `villalobos_logistica_2`

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('admin','editor','cliente','conductor') DEFAULT 'cliente',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes_contacto`
--
DROP TABLE IF EXISTS `mensajes_contacto`;
CREATE TABLE IF NOT EXISTS `mensajes_contacto` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `mensaje` text NOT NULL,
  `leido` tinyint(1) DEFAULT '0',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `portes`
--
DROP TABLE IF EXISTS `portes`;
CREATE TABLE IF NOT EXISTS `portes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cliente_id` int NULL,
  `conductor_id` int NULL,
  `fecha_programada` date NOT NULL,
  `origen` varchar(100) NOT NULL,
  `destino` varchar(100) NOT NULL,
  `kms` decimal(8,2) DEFAULT '0.00',
  `peso` decimal(8,2) DEFAULT '0.00',
  `precio` decimal(10,2) DEFAULT '0.00',
  `estado` enum('pendiente','en_ruta','entregado','cancelado') DEFAULT 'pendiente',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_portes_cliente` (`cliente_id`),
  KEY `fk_portes_conductor` (`conductor_id`),
  CONSTRAINT `fk_portes_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_portes_conductor` FOREIGN KEY (`conductor_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Volcado de datos para la tabla `usuarios`
--
-- Nota: La contraseña para todos los usuarios es '123456'
-- El hash corresponde a password_hash('123456', PASSWORD_DEFAULT)
INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password_hash`, `rol`, `creado_en`) VALUES
(1, 'Admin', 'admin@villalobos.local', '$2y$10$5MNmjB2v2q9H6b0J6b7j/OSJ5o2tQmRkzC8ht9mH9e6XzV3iM0j6a', 'admin', CURRENT_TIMESTAMP),
(2, 'Editor Contenido', 'editor@villalobos.local', '$2y$10$5MNmjB2v2q9H6b0J6b7j/OSJ5o2tQmRkzC8ht9mH9e6XzV3iM0j6a', 'editor', CURRENT_TIMESTAMP),
(3, 'Empresa Cliente', 'cliente@empresa.local', '$2y$10$5MNmjB2v2q9H6b0J6b7j/OSJ5o2tQmRkzC8ht9mH9e6XzV3iM0j6a', 'cliente', CURRENT_TIMESTAMP),
(4, 'Paco Conductor', 'paco@villalobos.local', '$2y$10$5MNmjB2v2q9H6b0J6b7j/OSJ5o2tQmRkzC8ht9mH9e6XzV3iM0j6a', 'conductor', CURRENT_TIMESTAMP);

--
-- Volcado de datos para la tabla `mensajes_contacto`
--
INSERT INTO `mensajes_contacto` (`id`, `nombre`, `email`, `mensaje`, `leido`) VALUES
(1, 'Juan Pérez', 'juan@ejemplo.com', 'Hola, necesito saber el coste de enviar 5 palets desde Málaga a Madrid el próximo martes. Gracias.', 0);

--
-- Volcado de datos para la tabla `portes`
--
INSERT INTO `portes` (`id`, `cliente_id`, `conductor_id`, `fecha_programada`, `origen`, `destino`, `kms`, `peso`, `precio`, `estado`) VALUES
(1, 3, 4, '2026-03-20', 'Málaga', 'Sevilla', 210.50, 1500.00, 350.00, 'pendiente');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
