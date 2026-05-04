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
  PRIMARY KEY (`id`),
  KEY `idx_mensajes_creado_en` (`creado_en`),
  KEY `idx_mensajes_leido` (`leido`)
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
  KEY `idx_portes_estado` (`estado`),
  KEY `idx_portes_fecha_programada` (`fecha_programada`),
  CONSTRAINT `fk_portes_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_portes_conductor` FOREIGN KEY (`conductor_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

DROP TABLE IF EXISTS `chat_leads`;
CREATE TABLE IF NOT EXISTS `chat_leads` (
  `id`         int          NOT NULL AUTO_INCREMENT,
  `nombre`     varchar(100) NOT NULL,
  `contacto`   varchar(120) NOT NULL,
  `servicio`   varchar(80)  NOT NULL,
  `ruta`       varchar(150) NOT NULL,
  `email_enviado` tinyint(1) DEFAULT '0',
  `leido`      tinyint(1)   DEFAULT '0',
  `creado_en`  timestamp    NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_chat_leads_creado_en` (`creado_en`)
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


-- --------------------------------------------------------
-- Datos adicionales para demo: portes, mensajes, leads
-- --------------------------------------------------------

INSERT INTO `portes` (`id`, `cliente_id`, `conductor_id`, `fecha_programada`, `origen`, `destino`, `kms`, `peso`, `precio`, `estado`) VALUES
(2, 3, 4, '2026-02-10', 'Málaga', 'Granada', 125.00, 800.00, 156.25, 'entregado'),
(3, 3, 4, '2026-02-18', 'Málaga', 'Sevilla', 210.00, 2200.00, 350.00, 'entregado'),
(4, 3, 4, '2026-03-05', 'Málaga', 'Cádiz', 245.00, 1500.00, 306.25, 'entregado'),
(5, 3, 4, '2026-03-12', 'Sevilla', 'Madrid', 530.00, 3000.00, 662.50, 'entregado'),
(6, 3, 4, '2026-03-20', 'Málaga', 'Córdoba', 165.00, 900.00, 206.25, 'entregado'),
(7, 3, 4, '2026-03-28', 'Málaga', 'Almería', 215.00, 1200.00, 268.75, 'entregado'),
(8, 3, 4, '2026-04-02', 'Málaga', 'Huelva', 300.00, 2500.00, 375.00, 'entregado'),
(9, 3, 4, '2026-04-08', 'Málaga', 'Marbella', 60.00, 400.00, 80.00, 'entregado'),
(10, 3, 4, '2026-04-10', 'Málaga', 'Antequera', 55.00, 600.00, 80.00, 'entregado'),
(11, 3, 4, '2026-04-14', 'Málaga', 'Algeciras', 135.00, 1100.00, 168.75, 'entregado'),
(12, 3, 4, '2026-04-18', 'Málaga', 'Ronda', 110.00, 750.00, 137.50, 'en_ruta'),
(13, 3, 4, '2026-04-20', 'Granada', 'Murcia', 260.00, 1800.00, 325.00, 'en_ruta'),
(14, 3, 4, '2026-04-25', 'Málaga', 'Jaén', 175.00, 950.00, 218.75, 'pendiente'),
(15, 3, 4, '2026-04-30', 'Málaga', 'Barcelona', 990.00, 4000.00, 1237.50, 'pendiente');

INSERT INTO `mensajes_contacto` (`id`, `nombre`, `email`, `mensaje`, `leido`) VALUES
(2, 'María García', 'maria@constructora-sol.es', 'Necesitamos transportar material de construcción desde Málaga a Sevilla, unos 5 palets de 500 kg cada uno. ¿Cuál sería el precio?', 1),
(3, 'Construcciones Norte SL', 'info@construccionesnorte.com', 'Somos una empresa constructora y necesitamos almacenaje temporal para material de obra durante 3 semanas. Aproximadamente 20 toneladas.', 0),
(4, 'Ana Martínez', 'ana.m@gmail.com', 'Hola, quería saber el precio de mover mobiliario de oficina desde el centro de Málaga al Polígono Industrial de Antequera.', 0),
(5, 'Distribuciones Costa SL', 'pedidos@distribuciones-costa.es', 'Necesitamos hacer reparto de mercancía a varios puntos de la Costa del Sol. ¿Tienen servicio de distribución capilar?', 0);

INSERT INTO `chat_leads` (`id`, `nombre`, `contacto`, `servicio`, `ruta`, `email_enviado`, `leido`) VALUES
(1, 'Carlos López', 'carlos@empresa.com', 'Transporte', 'Málaga a Sevilla', 1, 1),
(2, 'Fundición García e Hijos', '622345678', 'Almacenaje', 'Córdoba', 1, 0),
(3, 'Laura Sánchez', 'laura.s@hotmail.com', 'Mudanza', 'Málaga a Granada', 1, 0),
(4, 'Materiales del Sur', 'info@materialesdelsur.es', 'Distribución', 'Costa del Sol', 0, 0),
(5, 'Pedro Alonso', '678901234', 'Urgente', 'Málaga a Madrid', 1, 0);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
