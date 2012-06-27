-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Machine: localhost
-- Genereertijd: 27 jun 2012 om 17:34
-- Serverversie: 5.5.24-log
-- PHP-versie: 5.4.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Databank: `smf`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `smf_bugtracker_entries`
--

CREATE TABLE IF NOT EXISTS `smf_bugtracker_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` mediumtext NOT NULL,
  `description` longtext NOT NULL,
  `type` tinytext NOT NULL,
  `tracker` int(11) NOT NULL,
  `private` tinyint(1) NOT NULL,
  `startedon` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `project` int(11) NOT NULL,
  `status` mediumtext NOT NULL,
  `attention` tinyint(1) NOT NULL,
  `progress` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=14 ;

--
-- Gegevens worden uitgevoerd voor tabel `smf_bugtracker_entries`
--

INSERT INTO `smf_bugtracker_entries` (`id`, `name`, `description`, `type`, `tracker`, `private`, `startedon`, `project`, `status`, `attention`, `progress`) VALUES
(5, 'Testing', 'Testing if everything works!\r\n\r\nDo all your testing in here.', 'issue', 1, 0, '2012-06-26 13:15:38', 1, 'new', 1, 0),
(6, 'Add real permissions', 'The current permissions do not work, and will only evaluate TRUE if the user is logged in as administrator.', 'feature', 1, 0, '2012-06-26 14:10:51', 2, 'new', 1, 0),
(7, 'Remove entries', 'Allow removal of entries. Also decrease the amount of issues/features in the project.', 'feature', 1, 0, '2012-06-26 13:56:59', 2, 'wip', 0, 5),
(8, 'Move queries and other functions into separate function', 'As the title says. It seems to make the tracker a tad faster.', 'feature', 1, 0, '2012-06-26 13:56:46', 2, 'wip', 1, 25),
(9, 'Testing a new entry', 'Testing if this works and marks it as solved!', 'issue', 1, 0, '2012-06-26 13:21:49', 1, 'done', 0, 0),
(10, 'Testing a new entry', 'Testing if this works and marks it as solved!', 'issue', 1, 0, '2012-06-26 14:48:15', 1, 'done', 0, 100),
(11, 'Info Center', 'Make an info center which shows total amount of entries, something like this:\r\n\r\n[quote]Total entries: xx\r\nSolved: xx\r\nWork In Progress: xx\r\nRejected: xx\r\nUnassigned: xx[/quote]', 'feature', 1, 0, '2012-06-26 13:56:31', 2, 'new', 0, 0),
(12, 'Unread Replies/Posts box', 'Add a box like SimpleDesk does which allows you to see the items requiring attention. See http://simplemachines.org/community/index.php?action=unreadreplies for an example.', 'feature', 1, 0, '2012-06-26 13:58:09', 2, 'new', 0, 0),
(13, 'Allow viewing of all entries of a category', 'Allow users to view all entries of a category at once. So allow them to view items in Unassigned at once, etc.', 'feature', 1, 0, '2012-06-26 13:59:18', 2, 'new', 0, 0);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `smf_bugtracker_projects`
--

CREATE TABLE IF NOT EXISTS `smf_bugtracker_projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` mediumtext NOT NULL,
  `description` longtext NOT NULL,
  `issuenum` int(11) NOT NULL,
  `featurenum` int(11) NOT NULL,
  `lastnum` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Gegevens worden uitgevoerd voor tabel `smf_bugtracker_projects`
--

INSERT INTO `smf_bugtracker_projects` (`id`, `name`, `description`, `issuenum`, `featurenum`, `lastnum`) VALUES
(1, 'Issues', 'Any issues found in FXTracker', 3, 0, 0),
(2, 'Features', 'Features in consideration for FXTracker', 0, 6, 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
