-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 30-08-2025 a las 23:04:34
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
-- Base de datos: `catestructuras`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rrhh_reloj`
--

DROP TABLE IF EXISTS `rrhh_reloj`;
CREATE TABLE IF NOT EXISTS `rrhh_reloj` (
  `id` int NOT NULL AUTO_INCREMENT,
  `legajo` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `tipo_origen` enum('hikvision','gadnic') NOT NULL,
  `f_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=135 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `rrhh_reloj`
--

INSERT INTO `rrhh_reloj` (`id`, `legajo`, `nombre`, `fecha_hora`, `tipo_origen`, `f_creacion`) VALUES
(134, '14', 'Lara Exequi', '2024-12-09 06:53:00', 'hikvision', '2025-05-14 05:40:57'),
(133, '1026', 'Diaz Jorge Luis', '2025-04-17 07:03:52', 'hikvision', '2025-05-14 05:40:46'),
(132, '1025', 'Gimenez Tobares Sol Maria', '2025-04-17 07:02:55', 'hikvision', '2025-05-14 05:40:46'),
(131, '1022', 'Palacio Marcos', '2025-04-17 07:03:57', 'hikvision', '2025-05-14 05:40:46'),
(130, '1014', 'Diaz Espeche Eliana A.', '2025-04-17 13:15:58', 'hikvision', '2025-05-14 05:40:46'),
(129, '1014', 'Diaz Espeche Eliana A.', '2025-04-17 07:08:55', 'hikvision', '2025-05-14 05:40:46'),
(128, '1012', 'Vergara Rocio Edith', '2025-04-17 07:06:03', 'hikvision', '2025-05-14 05:40:46'),
(127, '1007', 'Avaca Javier F. Ivan', '2025-04-17 07:17:11', 'hikvision', '2025-05-14 05:40:46'),
(126, '1004', 'Torres Alexander Efrain', '2025-04-17 07:03:42', 'hikvision', '2025-05-14 05:40:46'),
(125, '290', 'Carrizo Pablo Ismael', '2025-04-17 07:04:03', 'hikvision', '2025-05-14 05:40:46'),
(124, '281', 'Echenique Lucas Miguel', '2025-04-17 07:03:46', 'hikvision', '2025-05-14 05:40:46'),
(123, '253', 'Armenta Oscar Exequiel', '2025-04-17 07:16:33', 'hikvision', '2025-05-14 05:40:46'),
(122, '251', 'Brizuela Juan Pablo', '2025-04-17 07:03:32', 'hikvision', '2025-05-14 05:40:46'),
(121, '214', 'Romero Juan Carlos', '2025-04-17 07:04:21', 'hikvision', '2025-05-14 05:40:46'),
(120, '213', 'Torres Ramon Alberto', '2025-04-17 07:03:35', 'hikvision', '2025-05-14 05:40:46'),
(119, '201', 'Orellana Maria Gisel', '2025-04-17 07:04:33', 'hikvision', '2025-05-14 05:40:46'),
(118, '182', 'Ojeda Raul Esteban', '2025-04-17 07:03:04', 'hikvision', '2025-05-14 05:40:46'),
(117, '160', 'Barrientos Franco Javier', '2025-04-17 07:04:09', 'hikvision', '2025-05-14 05:40:46'),
(116, '158', 'Leiva Leonardo Miguel', '2025-04-17 07:03:27', 'hikvision', '2025-05-14 05:40:46'),
(115, '141', 'Tejeda Marcelo Dario', '2025-04-17 07:04:41', 'hikvision', '2025-05-14 05:40:46'),
(114, '129', 'Ayosa Kevin Nahuel', '2025-04-17 07:31:41', 'hikvision', '2025-05-14 05:40:46'),
(113, '122', 'Ayosa Hernan Exequiel', '2025-04-17 07:04:12', 'hikvision', '2025-05-14 05:40:46'),
(112, '121', 'Paez Julio Ivan', '2025-04-17 07:03:38', 'hikvision', '2025-05-14 05:40:46'),
(111, '1026', 'Diaz Jorge Luis', '2025-04-17 07:03:52', 'hikvision', '2025-05-14 05:40:34'),
(110, '1025', 'Gimenez Tobares Sol Maria', '2025-04-17 07:02:55', 'hikvision', '2025-05-14 05:40:34'),
(109, '1022', 'Palacio Marcos', '2025-04-17 07:03:57', 'hikvision', '2025-05-14 05:40:34'),
(108, '1014', 'Diaz Espeche Eliana A.', '2025-04-17 13:15:58', 'hikvision', '2025-05-14 05:40:34'),
(107, '1014', 'Diaz Espeche Eliana A.', '2025-04-17 07:08:55', 'hikvision', '2025-05-14 05:40:34'),
(106, '1012', 'Vergara Rocio Edith', '2025-04-17 07:06:03', 'hikvision', '2025-05-14 05:40:34'),
(105, '1007', 'Avaca Javier F. Ivan', '2025-04-17 07:17:11', 'hikvision', '2025-05-14 05:40:34'),
(104, '1004', 'Torres Alexander Efrain', '2025-04-17 07:03:42', 'hikvision', '2025-05-14 05:40:34'),
(103, '290', 'Carrizo Pablo Ismael', '2025-04-17 07:04:03', 'hikvision', '2025-05-14 05:40:34'),
(102, '281', 'Echenique Lucas Miguel', '2025-04-17 07:03:46', 'hikvision', '2025-05-14 05:40:34'),
(101, '253', 'Armenta Oscar Exequiel', '2025-04-17 07:16:33', 'hikvision', '2025-05-14 05:40:34'),
(100, '251', 'Brizuela Juan Pablo', '2025-04-17 07:03:32', 'hikvision', '2025-05-14 05:40:34'),
(99, '214', 'Romero Juan Carlos', '2025-04-17 07:04:21', 'hikvision', '2025-05-14 05:40:34'),
(98, '213', 'Torres Ramon Alberto', '2025-04-17 07:03:35', 'hikvision', '2025-05-14 05:40:34'),
(97, '201', 'Orellana Maria Gisel', '2025-04-17 07:04:33', 'hikvision', '2025-05-14 05:40:34'),
(96, '182', 'Ojeda Raul Esteban', '2025-04-17 07:03:04', 'hikvision', '2025-05-14 05:40:34'),
(95, '160', 'Barrientos Franco Javier', '2025-04-17 07:04:09', 'hikvision', '2025-05-14 05:40:34'),
(94, '158', 'Leiva Leonardo Miguel', '2025-04-17 07:03:27', 'hikvision', '2025-05-14 05:40:34'),
(93, '141', 'Tejeda Marcelo Dario', '2025-04-17 07:04:41', 'hikvision', '2025-05-14 05:40:34'),
(92, '129', 'Ayosa Kevin Nahuel', '2025-04-17 07:31:41', 'hikvision', '2025-05-14 05:40:34'),
(91, '122', 'Ayosa Hernan Exequiel', '2025-04-17 07:04:12', 'hikvision', '2025-05-14 05:40:34'),
(90, '121', 'Paez Julio Ivan', '2025-04-17 07:03:38', 'hikvision', '2025-05-14 05:40:34');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
