-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 19-07-2026 a las 21:37:03
-- Versión del servidor: 8.0.17
-- Versión de PHP: 7.3.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `minecraft_proyect`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fundicion`
--

CREATE TABLE `fundicion` (
  `id_fundicion` int(11) NOT NULL,
  `id_item_entrada` varchar(50) NOT NULL,
  `id_item_salida` varchar(50) NOT NULL,
  `combustible` varchar(50) DEFAULT NULL,
  `tiempo_segundos` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `fundicion`
--

INSERT INTO `fundicion` (`id_fundicion`, `id_item_entrada`, `id_item_salida`, `combustible`, `tiempo_segundos`) VALUES
(1, 'raw_iron', 'iron_ingot', 'Carbón', 10),
(2, 'cobblestone', 'stone', 'Carbón', 10),
(3, 'iron_ore', 'iron_ingot', 'Carbón', 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ingrediente_receta`
--

CREATE TABLE `ingrediente_receta` (
  `id_receta` int(11) NOT NULL,
  `id_item_ingred` varchar(50) NOT NULL,
  `cantidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `ingrediente_receta`
--

INSERT INTO `ingrediente_receta` (`id_receta`, `id_item_ingred`, `cantidad`) VALUES
(1, 'cobblestone', 8),
(2, 'iron_ingot', 9),
(3, 'iron_block', 1),
(4, 'iron_nugget', 9),
(5, 'iron_ingot', 1),
(6, 'oak_log', 1),
(7, 'oak_planks', 2),
(8, 'oak_planks', 1),
(9, 'oak_planks', 6),
(10, 'oak_planks', 4),
(10, 'stick', 2),
(11, 'oak_planks', 2),
(11, 'stick', 4),
(12, 'oak_planks', 6),
(13, 'oak_planks', 3),
(14, 'oak_planks', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario_usuario`
--

CREATE TABLE `inventario_usuario` (
  `id_usuario` int(11) NOT NULL,
  `id_item` varchar(50) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `item`
--

CREATE TABLE `item` (
  `id_item` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `imagen` varchar(255) DEFAULT NULL,
  `es_base` tinyint(1) DEFAULT '0',
  `stack_max` int(11) DEFAULT '64',
  `tipo_herramienta` varchar(50) DEFAULT NULL,
  `nivel_herramienta` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `item`
--

INSERT INTO `item` (`id_item`, `nombre`, `descripcion`, `imagen`, `es_base`, `stack_max`, `tipo_herramienta`, `nivel_herramienta`) VALUES
('cobblestone', 'Roca', 'Se obtiene al picar piedra con un pico. Es uno de los bloques más comunes y versátiles.', 'https://i.pinimg.com/1200x/0e/9b/fb/0e9bfb02069859b618a0324aa10ee18b.jpg', 1, 64, 'Pico', 'Madera'),
('furnace', 'Horno', 'Se fabrica con 8 rocas en forma de cuadrado. Se usa para fundir y cocinar objetos.', 'https://i.pinimg.com/736x/1a/70/c2/1a70c200e2ccaac8c20d974c3e38cf04.jpg', 0, 64, NULL, NULL),
('iron_block', 'Bloque de hierro', 'Se fabrica con 9 lingotes de hierro. También se puede descomponer en 9 lingotes.', 'https://i.pinimg.com/1200x/50/2f/18/502f18f6b07b794948fe1967516ab2f8.jpg', 0, 64, 'Pico', 'Piedra'),
('iron_ingot', 'Lingote de hierro', 'Se obtiene al fundir hierro en bruto en el horno. Es el material base para muchas herramientas y bloques.', 'https://minecraft.wiki/images/Iron_Ingot_JE3_BE2.png?849cb&format=original', 0, 64, NULL, NULL),
('iron_nugget', 'Pepita de hierro', 'Se obtiene al fundir herramientas o armaduras de hierro en el horno. 9 pepitas = 1 lingote.', 'https://minecraft.wiki/images/Iron_Nugget_JE1_BE1.png?fa1c7&format=original', 0, 64, NULL, NULL),
('iron_ore', 'Mena de hierro', 'Se obtiene picando la mena de hierro con un pico de piedra o superior. Con toque de seda se obtiene el bloque.', 'https://i.pinimg.com/1200x/93/a4/13/93a413782edd0348ba5aab32f54991db.jpg', 1, 64, 'Pico', 'Piedra'),
('oak_button', 'Botón de roble', 'Se obtiene al colocar 1 tablón de roble en la mesa de trabajo. Rinde 1 botón.', 'https://minecraft.wiki/images/Oak_Button_%28S%29_JE4.png?59d8c&format=original', 0, 64, NULL, NULL),
('oak_door', 'Puerta de roble', 'Se obtiene al colocar 6 tablones de roble en dos columnas verticales (3x2) en la mesa de trabajo. Rinde 3 puertas.', 'https://minecraft.wiki/images/Oak_Door_JE8.png?f3318&format=original', 0, 64, NULL, NULL),
('oak_fence', 'Valla de roble', 'Se obtiene al colocar 4 tablones de roble y 2 palos en la mesa de trabajo. Rinde 3 vallas.', 'https://minecraft.wiki/images/Oak_Fence_%28EW%29_JE9.png?d3472&format=original', 0, 64, NULL, NULL),
('oak_fence_gate', 'Puerta de valla de roble', 'Se obtiene al colocar 2 tablones de roble y 4 palos en la mesa de trabajo. Rinde 1 puerta de valla.', 'https://minecraft.wiki/images/Oak_Fence_Gate_JE4_BE3.png?04baf&format=original', 0, 64, NULL, NULL),
('oak_log', 'Tronco de roble', 'Se obtiene al talar árboles de roble con un hacha. Es el material base para obtener tablones.', 'https://minecraft.wiki/images/Oak_Log_%28UD%29_JE8_BE3.png?8a080&format=original', 1, 64, 'Hacha', 'Madera'),
('oak_planks', 'Tablón de roble', 'Se obtiene al colocar un tronco de roble en la mesa de trabajo. Rinde 4 tablones por cada tronco.', 'https://minecraft.wiki/images/Oak_Planks.png?d9efa&format=original', 0, 64, NULL, NULL),
('oak_slab', 'Losa de roble', 'Se obtiene al colocar 3 tablones de roble en horizontal en la mesa de trabajo. Rinde 6 losas.', 'https://minecraft.wiki/images/Oak_Slab_JE3_BE2.png?ed04d&format=original', 0, 64, NULL, NULL),
('oak_stairs', 'Escaleras de roble', 'Se obtiene al colocar 6 tablones de roble en forma de escalera en la mesa de trabajo. Rinde 4 escaleras.', 'https://minecraft.wiki/images/Oak_Stairs_%28N%29_JE7_BE6.png?6c0aa&format=original', 0, 64, NULL, NULL),
('oak_trapdoor', 'Trampilla de roble', 'Se obtiene al colocar 4 tablones de roble en forma de cuadrado en la mesa de trabajo. Rinde 2 trampillas.', 'https://minecraft.wiki/images/Oak_Trapdoor_JE4_BE2.png?5c6d0&format=original', 0, 64, NULL, NULL),
('raw_iron', 'Hierro en bruto', 'Se obtiene al picar la mena de hierro sin toque de seda. Se puede fundir en el horno para obtener lingotes.', 'https://minecraft.wiki/images/Raw_Iron_JE3_BE2.png?de3cd&format=original', 1, 64, 'Pico', 'Piedra'),
('stick', 'Palo', 'Se obtiene al colocar 2 tablones de madera verticalmente en la mesa de trabajo. Rinde 4 palos.', 'https://minecraft.wiki/images/Stick_JE1_BE1.png?1fc15&format=original', 0, 64, NULL, NULL),
('stone', 'Piedra', 'Se obtiene al fundir roca en el horno o al picar piedra con un pico con toque de seda.', 'https://i.pinimg.com/1200x/37/99/d7/3799d7358845815a3506412dd1bbb9d6.jpg', 1, 64, 'Pico', 'Madera');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyecto`
--

CREATE TABLE `proyecto` (
  `id_proyecto` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `descripcion` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `proyecto`
--

INSERT INTO `proyecto` (`id_proyecto`, `id_usuario`, `nombre`, `fecha_creacion`, `descripcion`) VALUES
(1, 1, 'Casa Enorme', '2026-07-11 11:36:56', ''),
(2, 1, 'Granja', '2026-07-18 07:39:53', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyecto_detalle`
--

CREATE TABLE `proyecto_detalle` (
  `id_proyecto` int(11) NOT NULL,
  `id_item` varchar(50) NOT NULL,
  `cantidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `receta`
--

CREATE TABLE `receta` (
  `id_receta` int(11) NOT NULL,
  `id_item_resultado` varchar(50) NOT NULL,
  `cantidad_resultado` int(11) NOT NULL,
  `forma` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `receta`
--

INSERT INTO `receta` (`id_receta`, `id_item_resultado`, `cantidad_resultado`, `forma`) VALUES
(1, 'furnace', 1, 'shaped'),
(2, 'iron_block', 1, 'shaped'),
(3, 'iron_ingot', 9, 'shapeless'),
(4, 'iron_ingot', 1, 'shapeless'),
(5, 'iron_nugget', 9, 'shapeless'),
(6, 'oak_planks', 4, 'shapeless'),
(7, 'stick', 4, 'shaped'),
(8, 'oak_button', 1, 'shapeless'),
(9, 'oak_door', 3, 'shaped'),
(10, 'oak_fence', 3, 'shaped'),
(11, 'oak_fence_gate', 1, 'shaped'),
(12, 'oak_stairs', 4, 'shaped'),
(13, 'oak_slab', 6, 'shaped'),
(14, 'oak_trapdoor', 2, 'shaped');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `fecha_reg` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nombre`, `correo`, `contraseña`, `fecha_reg`) VALUES
(1, 'Moxaci', 'ankemaes22@gmail.com', '$2y$10$YY5SS5a0NcAwIxWm1P/Ft.MT1lTQ5WCV2fm6OSesVZ6F5oEftgkgy', '2026-07-11 11:09:07');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `fundicion`
--
ALTER TABLE `fundicion`
  ADD PRIMARY KEY (`id_fundicion`),
  ADD KEY `id_item_entrada` (`id_item_entrada`),
  ADD KEY `id_item_salida` (`id_item_salida`);

--
-- Indices de la tabla `ingrediente_receta`
--
ALTER TABLE `ingrediente_receta`
  ADD PRIMARY KEY (`id_receta`,`id_item_ingred`),
  ADD KEY `id_item_ingred` (`id_item_ingred`);

--
-- Indices de la tabla `inventario_usuario`
--
ALTER TABLE `inventario_usuario`
  ADD PRIMARY KEY (`id_usuario`,`id_item`),
  ADD KEY `id_item` (`id_item`);

--
-- Indices de la tabla `item`
--
ALTER TABLE `item`
  ADD PRIMARY KEY (`id_item`);

--
-- Indices de la tabla `proyecto`
--
ALTER TABLE `proyecto`
  ADD PRIMARY KEY (`id_proyecto`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `proyecto_detalle`
--
ALTER TABLE `proyecto_detalle`
  ADD PRIMARY KEY (`id_proyecto`,`id_item`),
  ADD KEY `id_item` (`id_item`);

--
-- Indices de la tabla `receta`
--
ALTER TABLE `receta`
  ADD PRIMARY KEY (`id_receta`),
  ADD KEY `id_item_resultado` (`id_item_resultado`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `fundicion`
--
ALTER TABLE `fundicion`
  MODIFY `id_fundicion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `proyecto`
--
ALTER TABLE `proyecto`
  MODIFY `id_proyecto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `receta`
--
ALTER TABLE `receta`
  MODIFY `id_receta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `fundicion`
--
ALTER TABLE `fundicion`
  ADD CONSTRAINT `fundicion_ibfk_1` FOREIGN KEY (`id_item_entrada`) REFERENCES `item` (`id_item`),
  ADD CONSTRAINT `fundicion_ibfk_2` FOREIGN KEY (`id_item_salida`) REFERENCES `item` (`id_item`);

--
-- Filtros para la tabla `ingrediente_receta`
--
ALTER TABLE `ingrediente_receta`
  ADD CONSTRAINT `ingrediente_receta_ibfk_1` FOREIGN KEY (`id_receta`) REFERENCES `receta` (`id_receta`),
  ADD CONSTRAINT `ingrediente_receta_ibfk_2` FOREIGN KEY (`id_item_ingred`) REFERENCES `item` (`id_item`);

--
-- Filtros para la tabla `inventario_usuario`
--
ALTER TABLE `inventario_usuario`
  ADD CONSTRAINT `inventario_usuario_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`),
  ADD CONSTRAINT `inventario_usuario_ibfk_2` FOREIGN KEY (`id_item`) REFERENCES `item` (`id_item`);

--
-- Filtros para la tabla `proyecto`
--
ALTER TABLE `proyecto`
  ADD CONSTRAINT `proyecto_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `proyecto_detalle`
--
ALTER TABLE `proyecto_detalle`
  ADD CONSTRAINT `proyecto_detalle_ibfk_1` FOREIGN KEY (`id_proyecto`) REFERENCES `proyecto` (`id_proyecto`),
  ADD CONSTRAINT `proyecto_detalle_ibfk_2` FOREIGN KEY (`id_item`) REFERENCES `item` (`id_item`);

--
-- Filtros para la tabla `receta`
--
ALTER TABLE `receta`
  ADD CONSTRAINT `receta_ibfk_1` FOREIGN KEY (`id_item_resultado`) REFERENCES `item` (`id_item`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
