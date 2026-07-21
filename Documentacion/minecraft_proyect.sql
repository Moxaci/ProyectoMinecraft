-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 21-07-2026 a las 02:00:55
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
(3, 'raw_copper', 'copper_ingot', 'Carbón', 10),
(4, 'sand', 'glass', 'Carbón', 10);

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
(14, 'oak_planks', 4),
(15, 'oak_log', 4),
(16, 'oak_planks', 6),
(16, 'stick', 1),
(17, 'andesite', 3),
(18, 'andesite', 6),
(19, 'andesite', 6),
(20, 'iron_ingot', 1),
(20, 'stone', 3),
(22, 'copper_ingot', 1),
(23, 'iron_ingot', 1),
(23, 'iron_nugget', 2),
(24, 'coal', 1),
(24, 'stick', 1),
(25, 'coal', 1),
(25, 'soul_sand', 1),
(25, 'stick', 1),
(26, 'coal', 1),
(26, 'copper_nugget', 1),
(26, 'stick', 1),
(27, 'iron_nugget', 8),
(27, 'torch', 1),
(28, 'chain', 2),
(28, 'stripped_oak_log', 6),
(29, 'copper_ingot', 9),
(30, 'copper_nugget', 8),
(30, 'torch', 1),
(31, 'iron_nugget', 8),
(31, 'soul_torch', 1),
(32, 'copper_block', 4),
(33, 'cut_copper', 3),
(34, 'cut_copper', 6),
(35, 'glass', 6),
(36, 'iron_block', 3),
(36, 'iron_ingot', 4),
(37, 'oak_planks', 6),
(37, 'oak_slab', 2),
(38, 'glass', 3),
(38, 'oak_slab', 3),
(38, 'quartz', 3),
(39, 'glass', 5),
(39, 'nether_star', 1),
(39, 'obsidian', 3),
(40, 'orange_tulip', 1),
(41, 'lilac', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario_usuario`
--

CREATE TABLE `inventario_usuario` (
  `id_usuario` int(11) NOT NULL,
  `id_item` varchar(50) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `inventario_usuario`
--

INSERT INTO `inventario_usuario` (`id_usuario`, `id_item`, `cantidad`) VALUES
(1, 'copper_nugget', 9),
(1, 'iron_nugget', 64);

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
('andesite', 'Andesita', 'Se genera de forma natural en el subsuelo. Se mina con cualquier pico.', 'https://es.minecraft.wiki/images/Andesita.png?9547c&format=original', 1, 64, 'Pico', 'Madera'),
('andesite_slab', 'Losa de andesita', 'Se obtiene al colocar 3 bloques de andesita horizontalmente en la mesa de trabajo. Rinde 6 losas.', 'https://minecraft.wiki/images/Andesite_Slab_JE2_BE2.png?075a2&format=original', 0, 64, 'Pico', 'Madera'),
('andesite_stairs', 'Escalera de andesita', 'Se obtiene al colocar 6 bloques de andesita en forma de escalera en la mesa de trabajo. Rinde 4 escaleras.', 'https://minecraft.wiki/images/Andesite_Stairs_%28N%29_JE2_BE1.png?fb984&format=original', 0, 64, 'Pico', 'Madera'),
('andesite_wall', 'Muro de andesita', 'Se obtiene al colocar 6 bloques de andesita en dos filas horizontales de 3x2 en la mesa de trabajo. Rinde 6 muros.', 'https://minecraft.wiki/images/Andesite_Wall_%28ewU%29_JE2.png?5f62d&format=original', 0, 64, 'Pico', 'Madera'),
('anvil', 'Yunque', 'Se obtiene al colocar 3 bloques de hierro en la fila superior y 4 lingotes de hierro abajo (1 en el centro y 3 en la fila inferior). Rinde 1 yunque.', 'https://minecraft.wiki/images/Anvil_%28N%29_JE3.png?d438e&format=original', 0, 64, NULL, NULL),
('barrel', 'Barril', 'Se obtiene al colocar 6 tablones de madera en los laterales y 2 losas de madera en el centro (arriba y abajo). Rinde 1 barril.', 'https://minecraft.wiki/images/Barrel_%28U%29_JE1_BE1.png?f98f5&format=original', 0, 64, 'Hacha', 'Madera'),
('beacon', 'Faro', 'Se obtiene al colocar 1 estrella del Nether en el centro encima de 3 bloques de obsidiana en la base y rodeada por 5 bloques de cristal. Rinde 1 faro.', 'https://minecraft.wiki/images/Beacon_JE6_BE2.png?684bf&format=original', 0, 64, NULL, NULL),
('chain', 'Cadena', 'Se obtiene al colocar 1 lingote de hierro y 2 pepitas de hierro en la mesa de trabajo. Rinde 1 cadena.', 'https://minecraft.wiki/images/Iron_Chain_%28UD%29_JE2.png?75f45&format=original', 0, 64, 'Pico', 'Piedra'),
('coal', 'Carbón', 'Se obtiene picando mena de carbón con cualquier pico o derrotando esqueletos Wither. Se usa como combustible y para fabricar antorchas.', 'https://minecraft.wiki/images/Coal_JE4_BE3.png?165e9&format=original', 1, 64, 'Pico', 'Madera'),
('cobblestone', 'Roca', 'Se obtiene al picar piedra con un pico. Es uno de los bloques más comunes y versátiles.', 'https://minecraft.wiki/images/Cobblestone_JE5_BE3.png?25024&format=original', 1, 64, 'Pico', 'Madera'),
('copper_block', 'Bloque de cobre', 'Se obtiene al colocar 9 lingotes de cobre en la mesa de trabajo. Rinde 1 bloque de cobre.', 'https://minecraft.wiki/images/Block_of_Copper_JE1_BE1.png?b75fe&format=original', 0, 64, 'Pico', 'Piedra'),
('copper_ingot', 'Lingote de cobre', 'Se obtiene al fundir cobre bruto en un horno o un alto horno. También se puede obtener juntando 9 pepitas de cobre.', 'https://minecraft.wiki/images/Copper_Ingot_JE2_BE1.png?0d410&format=original', 0, 64, NULL, NULL),
('copper_lantern', 'Farol de cobre', 'Se obtiene al colocar 1 antorcha rodeada por 8 pepitas de cobre en la mesa de trabajo. Rinde 1 farol de cobre.', 'https://minecraft.wiki/images/Copper_Lantern_JE2.gif?a4e18&format=original', 0, 64, 'Pico', 'Piedra'),
('copper_nugget', 'Pepita de cobre', 'Se obtiene al colocar 1 lingote de cobre en la cuadrícula de fabricación. Rinde 9 pepitas por cada lingote.', 'https://minecraft.wiki/images/Copper_Nugget_JE1_BE1.png?42865&format=original', 0, 64, NULL, NULL),
('copper_torch', 'Antorcha de cobre', 'Se obtiene al colocar 1 pepita de cobre encima de 1 carbón y debajo 1 palo. Rinde 4 antorchas de cobre.', 'https://minecraft.wiki/images/Copper_Torch_JE2.gif?741db&format=original', 0, 64, NULL, NULL),
('cut_copper', 'Cobre cortado', 'Se obtiene al colocar 4 bloques de cobre en un cuadrado de 2x2 en la mesa de trabajo o colocando 1 bloque de cobre en el cortapiedras. Rinde 4 bloques de cobre cortado.', 'https://minecraft.wiki/images/Cut_Copper_JE2_BE1.png?d3c9f&format=original', 0, 64, 'Pico', 'Piedra'),
('cut_copper_slab', 'Losa de cobre cortado', 'Se obtiene al colocar 3 bloques de cobre cortado en horizontal en la mesa de trabajo (rinde 6 losas) o colocando 1 bloque de cobre cortado en el cortapiedras (rinde 2 losas).', 'https://minecraft.wiki/images/Cut_Copper_Slab_JE2_BE1.png?de5b9&format=original', 0, 64, 'Pico', 'Piedra'),
('cut_copper_stairs', 'Escaleras de cobre cortado', 'Se obtiene al colocar 6 bloques de cobre cortado en forma de escalera en la mesa de trabajo (rinde 4 escaleras) o colocando 1 bloque de cobre cortado en el cortapiedras (rinde 1 escalera).', 'https://minecraft.wiki/images/Cut_Copper_Stairs_%28N%29_JE2_BE1.png?389db&format=original', 0, 64, 'Pico', 'Piedra'),
('daylight_detector', 'Sensor de luz solar', 'Se obtiene al colocar 3 bloques de cristal en la fila superior encima de 3 cuarzos del Nether en el medio y 3 losas de madera en la base. Rinde 1 sensor de luz solar.', 'https://minecraft.wiki/images/Daylight_Detector_JE1_BE1.png?c5bbc&format=original', 0, 64, NULL, NULL),
('furnace', 'Horno', 'Se fabrica con 8 rocas en forma de cuadrado. Se usa para fundir y cocinar objetos.', 'https://minecraft.wiki/images/Furnace_%28S%29_JE4.png?93891&format=original', 0, 64, NULL, NULL),
('glass', 'Cristal', 'Se obtiene al fundir arena en un horno. Se usa para fabricar paneles de cristal y otros objetos.', 'https://minecraft.wiki/images/Glass_JE4.png?d0e06&format=original', 0, 64, NULL, NULL),
('glass_pane', 'Panel de cristal', 'Se obtiene al colocar 6 bloques de cristal en las dos filas inferiores de la mesa de trabajo. Rinde 16 paneles.', 'https://minecraft.wiki/images/Glass_Pane_%28EW%29_JE12.png?eb1c9&format=original', 0, 64, NULL, NULL),
('iron_block', 'Bloque de hierro', 'Se fabrica con 9 lingotes de hierro. También se puede descomponer en 9 lingotes.', 'https://minecraft.wiki/images/Block_of_Iron_JE4_BE3.png?18948&format=original', 0, 64, 'Pico', 'Piedra'),
('iron_ingot', 'Lingote de hierro', 'Se obtiene al fundir hierro en bruto en el horno. Es el material base para muchas herramientas y bloques.', 'https://minecraft.wiki/images/Iron_Ingot_JE3_BE2.png?849cb&format=original', 0, 64, NULL, NULL),
('iron_nugget', 'Pepita de hierro', 'Se obtiene al fundir herramientas o armaduras de hierro en el horno. 9 pepitas = 1 lingote.', 'https://minecraft.wiki/images/Iron_Nugget_JE1_BE1.png?fa1c7&format=original', 0, 64, NULL, NULL),
('iron_ore', 'Mena de hierro', 'Se obtiene picando la mena de hierro con un pico de piedra o superior. Con toque de seda se obtiene el bloque.', 'https://minecraft.wiki/images/Iron_Ore_JE6_BE4.png?b1fb3&format=original', 1, 64, 'Pico', 'Piedra'),
('lantern', 'Farol', 'Se obtiene al colocar 1 antorcha rodeada por 8 pepitas de hierro en la mesa de trabajo. Rinde 1 farol.', 'https://minecraft.wiki/images/Lantern_JE1_BE1.gif?25a95&format=original', 0, 64, 'Pico', 'Piedra'),
('lilac', 'Lila', 'Es una flor de dos bloques de alto que se genera de forma natural en biomas de bosque. Se usa para fabricar tinte magenta.', 'https://minecraft.wiki/images/Lilac_JE4_BE2.png?2aa23&format=original', 1, 64, NULL, NULL),
('magenta_dye', 'Tinte magenta', 'Se obtiene colocando 1 lila o 1 alium en la cuadrícula, o mezclando 1 tinte morado y 1 tinte rosa.', 'https://minecraft.wiki/images/Magenta_Dye_JE3_BE3.png?e6f8c&format=original', 0, 64, NULL, NULL),
('nether_star', 'Estrella del Nether', 'Se obtiene como recompensa obligatoria al derrotar al jefe Wither. Se usa para fabricar faros.', 'https://minecraft.wiki/images/Nether_Star.gif?fb01f&format=original', 1, 64, NULL, NULL),
('oak_button', 'Botón de roble', 'Se obtiene al colocar 1 tablón de roble en la mesa de trabajo. Rinde 1 botón.', 'https://minecraft.wiki/images/Oak_Button_%28S%29_JE4.png?59d8c&format=original', 0, 64, NULL, NULL),
('oak_door', 'Puerta de roble', 'Se obtiene al colocar 6 tablones de roble en dos columnas verticales (3x2) en la mesa de trabajo. Rinde 3 puertas.', 'https://minecraft.wiki/images/Oak_Door_JE8.png?f3318&format=original', 0, 64, NULL, NULL),
('oak_fence', 'Valla de roble', 'Se obtiene al colocar 4 tablones de roble y 2 palos en la mesa de trabajo. Rinde 3 vallas.', 'https://minecraft.wiki/images/Oak_Fence_%28EW%29_JE9.png?d3472&format=original', 0, 64, NULL, NULL),
('oak_fence_gate', 'Puerta de valla de roble', 'Se obtiene al colocar 2 tablones de roble y 4 palos en la mesa de trabajo. Rinde 1 puerta de valla.', 'https://minecraft.wiki/images/Oak_Fence_Gate_JE4_BE3.png?04baf&format=original', 0, 64, NULL, NULL),
('oak_hanging_sign', 'Cartel colgante de roble', 'Se obtiene al colocar 2 cadenas de hierro y 6 troncos sin corteza de roble en la mesa de trabajo. Rinde 1 cartel colgante.', 'https://minecraft.wiki/images/Oak_Wall_Hanging_Sign_JE1.png?e608d&format=original', 0, 16, NULL, NULL),
('oak_log', 'Tronco de roble', 'Se obtiene al talar árboles de roble con un hacha. Es el material base para obtener tablones.', 'https://minecraft.wiki/images/Oak_Log_%28UD%29_JE8_BE3.png?8a080&format=original', 1, 64, 'Hacha', 'Madera'),
('oak_planks', 'Tablón de roble', 'Se obtiene al colocar un tronco de roble en la mesa de trabajo. Rinde 4 tablones por cada tronco.', 'https://minecraft.wiki/images/Oak_Planks.png?d9efa&format=original', 0, 64, NULL, NULL),
('oak_sign', 'Cartel de roble', 'Se obtiene al colocar 6 tablones de roble en las dos filas superiores y 1 palo en el centro de la fila inferior. Rinde 3 carteles.', 'https://minecraft.wiki/images/Oak_Sign_%280%29.png?530fb&format=original', 0, 16, NULL, NULL),
('oak_slab', 'Losa de roble', 'Se obtiene al colocar 3 tablones de roble en horizontal en la mesa de trabajo. Rinde 6 losas.', 'https://minecraft.wiki/images/Oak_Slab_JE3_BE2.png?ed04d&format=original', 0, 64, NULL, NULL),
('oak_stairs', 'Escaleras de roble', 'Se obtiene al colocar 6 tablones de roble en forma de escalera en la mesa de trabajo. Rinde 4 escaleras.', 'https://minecraft.wiki/images/Oak_Stairs_%28N%29_JE7_BE6.png?6c0aa&format=original', 0, 64, NULL, NULL),
('oak_trapdoor', 'Trampilla de roble', 'Se obtiene al colocar 4 tablones de roble en forma de cuadrado en la mesa de trabajo. Rinde 2 trampillas.', 'https://minecraft.wiki/images/Oak_Trapdoor_JE4_BE2.png?5c6d0&format=original', 0, 64, NULL, NULL),
('oak_wood', 'Leño de roble', 'Se obtiene al colocar 4 troncos de roble formando un cuadrado de 2x2 en la mesa de trabajo. Rinde 3 leños.', 'https://minecraft.wiki/images/Oak_Wood_%28UD%29_JE7_BE2.png?74743&format=original', 0, 64, 'Hacha', 'Madera'),
('obsidian', 'Obsidiana', 'Se genera cuando el agua toca un bloque de lava de origen. Se pica con un pico de diamante o de netherita.', 'https://minecraft.wiki/images/Obsidian_JE3_BE2.png?0a8ae&format=original', 1, 64, 'Pico', 'Diamante'),
('orange_dye', 'Tinte naranja', 'Se obtiene colocando 1 tulipán naranja en la cuadrícula de fabricación, o mezclando 1 tinte rojo y 1 tinte amarillo.', 'https://minecraft.wiki/images/Orange_Dye_JE3_BE3.png?7f1e2&format=original', 0, 64, NULL, NULL),
('orange_tulip', 'Tulipán naranja', 'Se genera naturalmente en biomas de llanuras y bosques floridos. Se usa para fabricar tinte naranja.', 'https://minecraft.wiki/images/Orange_Tulip_JE7_BE2.png?46bee&format=original', 1, 64, NULL, NULL),
('quartz', 'Cuarzo del Nether', 'Se obtiene picando mena de cuarzo en el Nether con cualquier pico. Se usa para fabricar sensores de luz solar.', 'https://minecraft.wiki/images/Nether_Quartz_JE2_BE2.png?d0049&format=original', 1, 64, 'Pico', 'Madera'),
('raw_copper', 'Cobre en bruto', 'Se obtiene al picar mena de cobre con un pico de piedra o superior. Se funde en el horno para obtener lingotes de cobre.', 'https://minecraft.wiki/images/Raw_Copper_JE3_BE2.png?61c79&format=original', 1, 64, 'Pico', 'Piedra'),
('raw_iron', 'Hierro en bruto', 'Se obtiene al picar la mena de hierro sin toque de seda. Se puede fundir en el horno para obtener lingotes.', 'https://minecraft.wiki/images/Raw_Iron_JE3_BE2.png?de3cd&format=original', 1, 64, 'Pico', 'Piedra'),
('sand', 'Arena', 'Se genera de forma natural en desiertos, playas y ríos. Se usa para fabricar cristal y otros bloques.', 'https://es.minecraft.wiki/images/Arena.png?62ad5&format=original', 1, 64, 'Pala', 'Madera'),
('soul_lantern', 'Farol de almas', 'Se obtiene al colocar 1 antorcha de almas rodeada por 8 pepitas de hierro en la mesa de trabajo. Rinde 1 farol de almas.', 'https://minecraft.wiki/images/Soul_Lantern_JE2_BE1.gif?ca51c&format=original', 0, 64, 'Pico', 'Piedra'),
('soul_sand', 'Arena de almas', 'Se genera de forma natural en el Nether. Se usa para fabricar antorchas de almas y cultivar verrugas del Nether.', 'https://minecraft.wiki/images/Soul_Sand_JE2_BE2.png?2334d&format=original', 1, 64, 'Pala', 'Madera'),
('soul_torch', 'Antorcha de almas', 'Se obtiene al colocar 1 carbón encima de 1 palo y debajo 1 bloque de arena de almas o tierra de almas. Rinde 4 antorchas de almas.', 'https://minecraft.wiki/images/Soul_Torch.gif?49a46&format=original', 0, 64, NULL, NULL),
('stick', 'Palo', 'Se obtiene al colocar 2 tablones de madera verticalmente en la mesa de trabajo. Rinde 4 palos.', 'https://minecraft.wiki/images/Stick_JE1_BE1.png?1fc15&format=original', 0, 64, NULL, NULL),
('stone', 'Piedra', 'Se obtiene al fundir roca en el horno o al picar piedra con un pico con toque de seda.', 'https://es.minecraft.wiki/images/Piedra.png?2f87a&format=original', 0, 64, 'Pico', 'Madera'),
('stonecutter', 'Cortapiedras', 'Se obtiene al colocar 1 lingote de hierro en la fila superior (centro) y 3 bloques de piedra horizontalmente en la fila central. Rinde 1 cortapiedras.', 'https://minecraft.wiki/images/Stonecutter_JE2_BE1.gif?bb269&format=original', 0, 64, NULL, NULL),
('stripped_oak_log', 'Tronco sin corteza de roble', 'Se obtiene haciendo clic derecho con un hacha sobre un tronco de roble. No tiene receta de crafteo.', 'https://minecraft.wiki/images/Stripped_Oak_Log_%28UD%29_JE2_BE2.png?2f50d&format=original', 1, 64, 'Hacha', 'Madera'),
('torch', 'Antorcha', 'Se obtiene al colocar 1 carbón (o carbón vegetal) encima de 1 palo. Rinde 4 antorchas.', 'https://minecraft.wiki/images/Torch.gif?8e1d4&format=original', 0, 64, NULL, NULL);

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
(4, 1, 'Granja', '2026-07-20 11:56:09', '');

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
(14, 'oak_trapdoor', 2, 'shaped'),
(15, 'oak_wood', 3, 'shaped'),
(16, 'oak_sign', 3, 'shaped'),
(17, 'andesite_slab', 6, 'shaped'),
(18, 'andesite_stairs', 4, 'shaped'),
(19, 'andesite_wall', 6, 'shaped'),
(20, 'stonecutter', 1, 'shaped'),
(21, 'copper_ingot', 1, 'shapeless'),
(22, 'copper_nugget', 9, 'shapeless'),
(23, 'chain', 1, 'shaped'),
(24, 'torch', 4, 'shaped'),
(25, 'soul_torch', 4, 'shaped'),
(26, 'copper_torch', 4, 'shaped'),
(27, 'lantern', 1, 'shaped'),
(28, 'oak_hanging_sign', 1, 'shaped'),
(29, 'copper_block', 1, 'shaped'),
(30, 'copper_lantern', 1, 'shaped'),
(31, 'soul_lantern', 1, 'shaped'),
(32, 'cut_copper', 4, 'shaped'),
(33, 'cut_copper_slab', 6, 'shaped'),
(34, 'cut_copper_stairs', 4, 'shaped'),
(35, 'glass_pane', 16, 'shaped'),
(36, 'anvil', 1, 'shaped'),
(37, 'barrel', 1, 'shaped'),
(38, 'daylight_detector', 1, 'shaped'),
(39, 'beacon', 1, 'shaped'),
(40, 'orange_dye', 1, 'shapeless'),
(41, 'magenta_dye', 2, 'shapeless');

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
  MODIFY `id_fundicion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `proyecto`
--
ALTER TABLE `proyecto`
  MODIFY `id_proyecto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `receta`
--
ALTER TABLE `receta`
  MODIFY `id_receta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

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
