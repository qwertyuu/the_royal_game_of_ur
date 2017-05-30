-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- Client :  127.0.0.1
-- Généré le :  Mar 30 Mai 2017 à 02:26
-- Version du serveur :  10.1.21-MariaDB
-- Version de PHP :  5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `ur`
--

-- --------------------------------------------------------

--
-- Structure de la table `game`
--

CREATE TABLE `game` (
  `game_id` int(11) NOT NULL,
  `en_creation` tinyint(1) NOT NULL,
  `joueur_courant` int(11) NOT NULL,
  `en_attente` tinyint(1) NOT NULL,
  `last_move_id` int(11) NOT NULL,
  `gagnee` tinyint(1) NOT NULL,
  `gagnant_position` int(11) NOT NULL,
  `last_de` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `joueur_jeton`
--

CREATE TABLE `joueur_jeton` (
  `jeton_id` int(11) NOT NULL,
  `jeton_fk_game_id` int(11) NOT NULL,
  `jeton_joueur_position` int(11) NOT NULL,
  `jeton_position` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `move`
--

CREATE TABLE `move` (
  `move_id` int(11) NOT NULL,
  `move_fk_jeton_id` int(11) NOT NULL,
  `move_fk_game_id` int(11) NOT NULL,
  `move_last_position` int(11) NOT NULL,
  `move_new_position` int(11) NOT NULL,
  `rosette` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Index pour les tables exportées
--

--
-- Index pour la table `game`
--
ALTER TABLE `game`
  ADD PRIMARY KEY (`game_id`);

--
-- Index pour la table `joueur_jeton`
--
ALTER TABLE `joueur_jeton`
  ADD PRIMARY KEY (`jeton_id`);

--
-- Index pour la table `move`
--
ALTER TABLE `move`
  ADD PRIMARY KEY (`move_id`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `game`
--
ALTER TABLE `game`
  MODIFY `game_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;
--
-- AUTO_INCREMENT pour la table `joueur_jeton`
--
ALTER TABLE `joueur_jeton`
  MODIFY `jeton_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=197;
--
-- AUTO_INCREMENT pour la table `move`
--
ALTER TABLE `move`
  MODIFY `move_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=191;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
