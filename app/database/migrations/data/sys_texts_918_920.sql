-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Хост: localhost:3306
-- Время создания: Июл 01 2026 г., 18:57
-- Версия сервера: 10.11.13-MariaDB-0ubuntu0.24.04.1
-- Версия PHP: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `yozh`
--

-- --------------------------------------------------------

--
-- Дамп данных таблицы `sys_texts`
--

INSERT IGNORE INTO `sys_texts` (`id`, `alias`, `lang`, `context`) VALUES
(919, 'repeat_type_new_deadline', 'ru', ' {#TYPE#}.
Срок выполнения: до {#DATE#}.'),
(920, 'telegram_repeat_type_new_deadline', 'ru', ' <i>{#TYPE#}.</i>
<b>Срок выполнения:</b> <i>до {#DATE#}.</i>');
COMMIT;
