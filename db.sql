-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Creato il: Giu 14, 2019 alle 18:38
-- Versione del server: 5.7.26-0ubuntu0.16.04.1
-- Versione PHP: 7.0.33-0ubuntu0.16.04.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `s259760`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `accounts`
--

DROP TABLE IF EXISTS `accounts`;
CREATE TABLE `accounts` (
  `Username` varchar(30) NOT NULL,
  `Password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `accounts`
--

INSERT INTO `accounts` (`Username`, `Password`) VALUES
('u1@p.it', 'fafebd5b11a565f6dccf38f743db55df'),
('u2@p.it', '8b1567a60a98ef4e5274447d2a9bc584');

-- --------------------------------------------------------

--
-- Struttura della tabella `airplane`
--

DROP TABLE IF EXISTS `airplane`;
CREATE TABLE `airplane` (
  `Id` int(11) NOT NULL,
  `Larghezza` int(11) NOT NULL,
  `Lunghezza` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `airplane`
--

INSERT INTO `airplane` (`Id`, `Larghezza`, `Lunghezza`) VALUES
(1, 6, 10);

-- --------------------------------------------------------

--
-- Struttura della tabella `seats`
--

DROP TABLE IF EXISTS `seats`;
CREATE TABLE `seats` (
  `Id` varchar(5) NOT NULL,
  `Account` varchar(30) NOT NULL,
  `Status` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `seats`
--

INSERT INTO `seats` (`Id`, `Account`, `Status`) VALUES
('2B', 'u2@p.it', 'seat_venduto'),
('3B', 'u2@p.it', 'seat_venduto'),
('4A', 'u1@p.it', 'seat_prenotato'),
('4B', 'u2@p.it', 'seat_venduto'),
('4D', 'u1@p.it', 'seat_prenotato'),
('4F', 'u2@p.it', 'seat_prenotato');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`Username`);

--
-- Indici per le tabelle `airplane`
--
ALTER TABLE `airplane`
  ADD PRIMARY KEY (`Id`);

--
-- Indici per le tabelle `seats`
--
ALTER TABLE `seats`
  ADD PRIMARY KEY (`Id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
