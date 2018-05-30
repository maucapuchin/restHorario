-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-05-2018 a las 05:14:11
-- Versión del servidor: 10.1.13-MariaDB
-- Versión de PHP: 5.6.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bd_horario`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumno`
--

CREATE TABLE `alumno` (
  `id_alumno` int(15) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `a_paterno` varchar(30) NOT NULL,
  `a_materno` varchar(30) NOT NULL,
  `semestre` varchar(20) NOT NULL,
  `correo` varchar(80) NOT NULL,
  `password` varchar(60) NOT NULL,
  `carrera` varchar(20) NOT NULL,
  `claveApi` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `alumno`
--

INSERT INTO `alumno` (`id_alumno`, `nombre`, `a_paterno`, `a_materno`, `semestre`, `correo`, `password`, `carrera`, `claveApi`) VALUES
(11, 'Pedro', 'mascorro', 'contreras', '3', 'pedro@mail.com', '$2y$10$xFNodeYGiwGKOFos.fHoludHQCOjLg4dGNH0YEcixZpK3GYaYIRnW', 'teologia', 'c3ce6fd30ed1fa396578d4ad0ff000a8');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumno-docente`
--

CREATE TABLE `alumno-docente` (
  `id` int(11) NOT NULL,
  `id_docente` varchar(20) NOT NULL,
  `id_alumno` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumno-materia`
--

CREATE TABLE `alumno-materia` (
  `id` int(11) NOT NULL,
  `id_alumno` varchar(20) NOT NULL,
  `id_materia` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumno_grupo`
--

CREATE TABLE `alumno_grupo` (
  `id` int(11) NOT NULL,
  `id_grupo` varchar(20) NOT NULL,
  `id_alumno` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docente-grupo`
--

CREATE TABLE `docente-grupo` (
  `id` int(11) NOT NULL,
  `id_grupo` varchar(20) NOT NULL,
  `id_docente` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docentes`
--

CREATE TABLE `docentes` (
  `idDocente` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `a_paterno` varchar(30) NOT NULL,
  `a_materno` varchar(30) NOT NULL,
  `password` varchar(128) NOT NULL,
  `carrera` varchar(60) NOT NULL,
  `claveApi` varchar(60) NOT NULL,
  `correo` varchar(254) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `docentes`
--

INSERT INTO `docentes` (`idDocente`, `nombre`, `a_paterno`, `a_materno`, `password`, `carrera`, `claveApi`, `correo`) VALUES
(0, 'Pedro', 'Perez', 'Lopez', '$2y$10$DR4VfuKlOC.yyFcewpL0V.IlZBx.hXs4VwCggJplM2LBO7fT4PQni', 'Informatica', 'bf49c0b5e1cfee5b895b210feba61ce2', 'pedro@mail.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupo`
--

CREATE TABLE `grupo` (
  `id_grupo` varchar(20) NOT NULL,
  `carrera` varchar(15) NOT NULL,
  `semestre` varchar(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios`
--

CREATE TABLE `horarios` (
  `id_horarios` int(11) NOT NULL,
  `id_alumno` varchar(11) NOT NULL,
  `clave` varchar(20) NOT NULL,
  `carrera` varchar(20) NOT NULL,
  `creditos_total` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  `dia` varchar(20) NOT NULL,
  `tipo_materia` varchar(15) NOT NULL,
  `aula_materia` varchar(9) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `horarios`
--

INSERT INTO `horarios` (`id_horarios`, `id_alumno`, `clave`, `carrera`, `creditos_total`, `fecha`, `dia`, `tipo_materia`, `aula_materia`) VALUES
(1, '1', 'INF-2010', 'info', 8, '0000-00-00 00:00:00', 'lunes', 'repe', 'b3'),
(2, '3', 'INF-2011', 'info', 19, '0000-00-00 00:00:00', 'martes', 'repe', 'b3'),
(3, '3', 'INF-2011', 'info', 19, '0000-00-00 00:00:00', 'martes', 'ordinario', 'b3'),
(4, '3', 'INF-2011', 'info', 19, '0000-00-00 00:00:00', 'martes', 'ordinario', 'b3');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materias`
--

CREATE TABLE `materias` (
  `clave` varchar(20) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `aula` varchar(20) NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `horario` varchar(20) NOT NULL,
  `creditos` int(11) NOT NULL,
  `ht` int(11) NOT NULL,
  `hp` int(11) NOT NULL,
  `idDocente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `materias`
--

INSERT INTO `materias` (`clave`, `nombre`, `aula`, `tipo`, `horario`, `creditos`, `ht`, `hp`, `idDocente`) VALUES
('INF-048', 'materia optativa', 'b5', 'ordinario', '2-3', 8, 1, 4, 0);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alumno`
--
ALTER TABLE `alumno`
  ADD PRIMARY KEY (`id_alumno`);

--
-- Indices de la tabla `alumno-docente`
--
ALTER TABLE `alumno-docente`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `alumno-materia`
--
ALTER TABLE `alumno-materia`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `alumno_grupo`
--
ALTER TABLE `alumno_grupo`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `docente-grupo`
--
ALTER TABLE `docente-grupo`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `grupo`
--
ALTER TABLE `grupo`
  ADD PRIMARY KEY (`id_grupo`);

--
-- Indices de la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD PRIMARY KEY (`id_horarios`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alumno`
--
ALTER TABLE `alumno`
  MODIFY `id_alumno` int(15) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT de la tabla `alumno-materia`
--
ALTER TABLE `alumno-materia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `alumno_grupo`
--
ALTER TABLE `alumno_grupo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `horarios`
--
ALTER TABLE `horarios`
  MODIFY `id_horarios` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
