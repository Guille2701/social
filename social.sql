-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-12-2025 a las 13:53:54
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `social`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `conversation`
--

CREATE TABLE `conversation` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `user1_id` int(11) NOT NULL,
  `user2_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20251201083359', '2025-12-01 09:34:12', 89);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `friend`
--

CREATE TABLE `friend` (
  `id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `friend`
--

INSERT INTO `friend` (`id`, `status`, `sender_id`, `receiver_id`) VALUES
(1, 'pending', 6, 1),
(2, 'pending', 6, 3),
(3, 'pending', 6, 5),
(4, 'pending', 6, 1),
(5, 'pending', 6, 4),
(6, 'pending', 1, 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `message`
--

CREATE TABLE `message` (
  `id` int(11) NOT NULL,
  `content` longtext NOT NULL,
  `created_at` datetime NOT NULL,
  `is_read` tinyint(4) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `post`
--

CREATE TABLE `post` (
  `id` int(11) NOT NULL,
  `text` varchar(255) DEFAULT NULL,
  `postdate` datetime NOT NULL,
  `img` varchar(255) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `post`
--

INSERT INTO `post` (`id`, `text`, `postdate`, `img`, `author_id`) VALUES
(4, 'oasdjkvnqsdoiuf hs', '2025-12-01 10:18:43', 'descarga-692d5d738672c.png', 1),
(6, 'dasdawdfsfdgedfg', '2025-12-01 10:29:44', 'descarga-692d600885f1c.png', 1),
(7, 'hola', '2025-12-02 12:33:21', 'WIN-20251201-12-46-52-Pro-692ece810336c.jpg', 4),
(8, 'hola', '2025-12-02 12:34:27', NULL, 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `post_user`
--

CREATE TABLE `post_user` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `post_user`
--

INSERT INTO `post_user` (`post_id`, `user_id`) VALUES
(4, 1),
(4, 2),
(6, 1),
(6, 2),
(8, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `story`
--

CREATE TABLE `story` (
  `id` int(11) NOT NULL,
  `img` varchar(255) DEFAULT NULL,
  `creation_date` datetime NOT NULL,
  `author_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `story`
--

INSERT INTO `story` (`id`, `img`, `creation_date`, `author_id`) VALUES
(1, 'descarga-692eafcfb6855.png', '2025-12-02 10:22:23', 2),
(2, 'descarga-692ed032b2a2e.png', '2025-12-02 12:40:34', 1),
(3, 'descarga-692ed03626894.jpg', '2025-12-02 12:40:38', 5),
(4, '448-f2-692ed047118ce.png', '2025-12-02 12:40:55', 6),
(5, 'WIN-20251125-19-05-46-Pro-69381f338a1e6.jpg', '2025-12-09 14:08:03', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `story_user`
--

CREATE TABLE `story_user` (
  `story_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `story_user`
--

INSERT INTO `story_user` (`story_id`, `user_id`) VALUES
(1, 5),
(2, 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(180) NOT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`roles`)),
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `user`
--

INSERT INTO `user` (`id`, `username`, `roles`, `password`) VALUES
(1, 'admin', '[\"ROLE_ADMIN\"]', '$2y$13$xdYK/Sn.Dkf5ArMjp.MO1uvXgnjTRSCNNJt/NilSfupDCOWQ57PQW'),
(2, 'admin2', '[]', '$2y$13$IzrA6gWcXXrbHf6S4mNQj.MGW/50XJEKsjfSi0crLRq.W1cHLwHKi'),
(3, 'abdul', '[]', '$2y$13$ACdgJDHI6OqHLcAOUdOQxOs9Pb3ReCMqabMEg8ZlUl53.PrQSQ.pq'),
(4, 'angelito', '[\"ROLE_ADMIN\"]', '$2y$13$ouQ7zMoC6rGIxT95/GneJeEeb2Qdy1qtFILIZ.p06ehCCpG2lIvr2'),
(5, 'esther', '[]', '$2y$13$so4Z0TYJZ7xCvGXTNVRJiubl78rwZLUbYKyjcn2uJXyjT70isT.z6'),
(6, 'iru', '[]', '$2y$13$HYuf/nQG/rM/KhcQlqaMS.kwEGQPW9NnXXl02hawDCpKJJr9udL7e'),
(7, 'abdul1', '[]', '$2y$13$fyxT.G70Fy8RGe47X8hsq.5bYhmgaN.nqknsvJ1TIwsoU8ye9mpwG');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_followers`
--

CREATE TABLE `user_followers` (
  `user_source` int(11) NOT NULL,
  `user_target` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `user_followers`
--

INSERT INTO `user_followers` (`user_source`, `user_target`) VALUES
(1, 2),
(2, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_post`
--

CREATE TABLE `user_post` (
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `user_post`
--

INSERT INTO `user_post` (`user_id`, `post_id`) VALUES
(4, 8),
(5, 7);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `conversation`
--
ALTER TABLE `conversation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_conversation_users` (`user1_id`,`user2_id`),
  ADD KEY `IDX_8A8E26E956AE248B` (`user1_id`),
  ADD KEY `IDX_8A8E26E9441B8B65` (`user2_id`);

--
-- Indices de la tabla `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Indices de la tabla `friend`
--
ALTER TABLE `friend`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_55EEAC61F624B39D` (`sender_id`),
  ADD KEY `IDX_55EEAC61CD53EDB6` (`receiver_id`);

--
-- Indices de la tabla `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_B6BD307F9AC0396` (`conversation_id`),
  ADD KEY `IDX_B6BD307FF624B39D` (`sender_id`);

--
-- Indices de la tabla `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_5A8A6C8DF675F31B` (`author_id`);

--
-- Indices de la tabla `post_user`
--
ALTER TABLE `post_user`
  ADD PRIMARY KEY (`post_id`,`user_id`),
  ADD KEY `IDX_44C6B1424B89032C` (`post_id`),
  ADD KEY `IDX_44C6B142A76ED395` (`user_id`);

--
-- Indices de la tabla `story`
--
ALTER TABLE `story`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_EB560438F675F31B` (`author_id`);

--
-- Indices de la tabla `story_user`
--
ALTER TABLE `story_user`
  ADD PRIMARY KEY (`story_id`,`user_id`),
  ADD KEY `IDX_1B3EBA67AA5D4036` (`story_id`),
  ADD KEY `IDX_1B3EBA67A76ED395` (`user_id`);

--
-- Indices de la tabla `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_IDENTIFIER_USERNAME` (`username`);

--
-- Indices de la tabla `user_followers`
--
ALTER TABLE `user_followers`
  ADD PRIMARY KEY (`user_source`,`user_target`),
  ADD KEY `IDX_84E870433AD8644E` (`user_source`),
  ADD KEY `IDX_84E87043233D34C1` (`user_target`);

--
-- Indices de la tabla `user_post`
--
ALTER TABLE `user_post`
  ADD PRIMARY KEY (`user_id`,`post_id`),
  ADD KEY `IDX_200B2044A76ED395` (`user_id`),
  ADD KEY `IDX_200B20444B89032C` (`post_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `conversation`
--
ALTER TABLE `conversation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `friend`
--
ALTER TABLE `friend`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `message`
--
ALTER TABLE `message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `post`
--
ALTER TABLE `post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `story`
--
ALTER TABLE `story`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `conversation`
--
ALTER TABLE `conversation`
  ADD CONSTRAINT `FK_8A8E26E9441B8B65` FOREIGN KEY (`user2_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_8A8E26E956AE248B` FOREIGN KEY (`user1_id`) REFERENCES `user` (`id`);

--
-- Filtros para la tabla `friend`
--
ALTER TABLE `friend`
  ADD CONSTRAINT `FK_55EEAC61CD53EDB6` FOREIGN KEY (`receiver_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_55EEAC61F624B39D` FOREIGN KEY (`sender_id`) REFERENCES `user` (`id`);

--
-- Filtros para la tabla `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `FK_B6BD307F9AC0396` FOREIGN KEY (`conversation_id`) REFERENCES `conversation` (`id`),
  ADD CONSTRAINT `FK_B6BD307FF624B39D` FOREIGN KEY (`sender_id`) REFERENCES `user` (`id`);

--
-- Filtros para la tabla `post`
--
ALTER TABLE `post`
  ADD CONSTRAINT `FK_5A8A6C8DF675F31B` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`);

--
-- Filtros para la tabla `post_user`
--
ALTER TABLE `post_user`
  ADD CONSTRAINT `FK_44C6B1424B89032C` FOREIGN KEY (`post_id`) REFERENCES `post` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_44C6B142A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `story`
--
ALTER TABLE `story`
  ADD CONSTRAINT `FK_EB560438F675F31B` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`);

--
-- Filtros para la tabla `story_user`
--
ALTER TABLE `story_user`
  ADD CONSTRAINT `FK_1B3EBA67A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_1B3EBA67AA5D4036` FOREIGN KEY (`story_id`) REFERENCES `story` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `user_followers`
--
ALTER TABLE `user_followers`
  ADD CONSTRAINT `FK_84E87043233D34C1` FOREIGN KEY (`user_target`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_84E870433AD8644E` FOREIGN KEY (`user_source`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `user_post`
--
ALTER TABLE `user_post`
  ADD CONSTRAINT `FK_200B20444B89032C` FOREIGN KEY (`post_id`) REFERENCES `post` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_200B2044A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
