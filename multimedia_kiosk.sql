-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Počítač: 127.0.0.1
-- Vytvořeno: Ned 17. bře 2024, 18:42
-- Verze serveru: 10.4.22-MariaDB
-- Verze PHP: 8.1.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `multimedia_kiosk`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `device`
--

CREATE TABLE `device` (
  `id` int(11) NOT NULL,
  `playlist_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `unique_hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `local_ip_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `disk_usage` longtext COLLATE utf8_unicode_ci DEFAULT NULL,
  `disk_capacity` longtext COLLATE utf8_unicode_ci DEFAULT NULL,
  `first_connection` datetime DEFAULT NULL,
  `last_connection` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `media`
--

CREATE TABLE `media` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `media_data` longtext COLLATE utf8_unicode_ci NOT NULL,
  `media_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `playlist`
--

CREATE TABLE `playlist` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `playlist_media`
--

CREATE TABLE `playlist_media` (
  `id` int(11) NOT NULL,
  `playlist_id` int(11) DEFAULT NULL,
  `media_id` int(11) DEFAULT NULL,
  `show_from` time DEFAULT NULL,
  `show_to` time DEFAULT NULL,
  `custom_time` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `reset_password_request`
--

CREATE TABLE `reset_password_request` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `selector` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `hashed_token` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `requested_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `expires_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `email` varchar(180) COLLATE utf8_unicode_ci NOT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`roles`)),
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `last_login` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Vypisuji data pro tabulku `user`
--

INSERT INTO `user` (`id`, `email`, `roles`, `password`, `created_at`, `last_login`) VALUES
(13, 'dev@test.com', '[\"ROLE_ADMIN\"]', '$2y$13$Tp2ADLdA04p6eoPLC7n3z.1cXMs7Wvk5UZgH4l51xjHi.1Lz6UfXm', '2024-03-17 18:41:20', NULL);

--
-- Indexy pro exportované tabulky
--

--
-- Indexy pro tabulku `device`
--
ALTER TABLE `device`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_92FB68E5E237E06` (`name`),
  ADD UNIQUE KEY `UNIQ_92FB68E173059B4` (`unique_hash`),
  ADD KEY `IDX_92FB68E6BBD148` (`playlist_id`);

--
-- Indexy pro tabulku `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_6A2CA10C5E237E06` (`name`);

--
-- Indexy pro tabulku `playlist`
--
ALTER TABLE `playlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_D782112D5E237E06` (`name`);

--
-- Indexy pro tabulku `playlist_media`
--
ALTER TABLE `playlist_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_C930B84F6BBD148` (`playlist_id`),
  ADD KEY `IDX_C930B84FEA9FDD75` (`media_id`);

--
-- Indexy pro tabulku `reset_password_request`
--
ALTER TABLE `reset_password_request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_7CE748AA76ED395` (`user_id`);

--
-- Indexy pro tabulku `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `device`
--
ALTER TABLE `device`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pro tabulku `media`
--
ALTER TABLE `media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pro tabulku `playlist`
--
ALTER TABLE `playlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pro tabulku `playlist_media`
--
ALTER TABLE `playlist_media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT pro tabulku `reset_password_request`
--
ALTER TABLE `reset_password_request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pro tabulku `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `device`
--
ALTER TABLE `device`
  ADD CONSTRAINT `FK_92FB68E6BBD148` FOREIGN KEY (`playlist_id`) REFERENCES `playlist` (`id`);

--
-- Omezení pro tabulku `playlist_media`
--
ALTER TABLE `playlist_media`
  ADD CONSTRAINT `FK_C930B84F6BBD148` FOREIGN KEY (`playlist_id`) REFERENCES `playlist` (`id`),
  ADD CONSTRAINT `FK_C930B84FEA9FDD75` FOREIGN KEY (`media_id`) REFERENCES `media` (`id`);

--
-- Omezení pro tabulku `reset_password_request`
--
ALTER TABLE `reset_password_request`
  ADD CONSTRAINT `FK_7CE748AA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
