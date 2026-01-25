-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 30-10-2025 a las 07:04:59
-- Versión del servidor: 9.1.0
-- Versión de PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `villalobos_logistica_2`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `portes`
--

DROP TABLE IF EXISTS `portes`;
CREATE TABLE IF NOT EXISTS `portes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fecha_programada` date NOT NULL,
  `origen` varchar(100) NOT NULL,
  `destino` varchar(100) NOT NULL,
  `kms` decimal(8,2) DEFAULT '0.00',
  `peso` decimal(8,2) DEFAULT '0.00',
  `ingreso_estimado` decimal(10,2) DEFAULT '0.00',
  `coste_estimado` decimal(10,2) DEFAULT '0.00',
  `estado` enum('recibido','en_ruta','entregado','retrasado') DEFAULT NULL,
  `conductor` varchar(100) DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `portes`
--

INSERT INTO `portes` (`id`, `fecha_programada`, `origen`, `destino`, `kms`, `peso`, `ingreso_estimado`, `coste_estimado`, `estado`, `conductor`, `creado_en`) VALUES
(1, '2025-10-09', 'Málaga', 'Sevilla', 300.00, 350.00, 400.00, 200.00, 'entregado', 'Jose Luis', '2025-10-29 10:56:47');

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
  `rol` enum('editor','admin') DEFAULT 'editor',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password_hash`, `rol`, `creado_en`) VALUES
(1, 'Admin', 'admin@demo.local', '$2y$10$5MNmjB2v2q9H6b0J6b7j/OSJ5o2tQmRkzC8ht9mH9e6XzV3iM0j6a', 'admin', '2025-10-29 10:55:29');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
