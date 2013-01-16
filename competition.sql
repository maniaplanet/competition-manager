-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.5.20-log - MySQL Community Server (GPL)
-- Server OS:                    Win32
-- HeidiSQL version:             7.0.0.4053
-- Date/time:                    2013-01-16 11:51:24
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET FOREIGN_KEY_CHECKS=0 */;

-- Dumping database structure for CompetitionManager
CREATE DATABASE IF NOT EXISTS `CompetitionManager` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `CompetitionManager`;


-- Dumping structure for table CompetitionManager.Competitions
CREATE TABLE IF NOT EXISTS `Competitions` (
  `competitionId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `remoteId` int(10) unsigned DEFAULT NULL,
  `name` varchar(40) NOT NULL,
  `description` text NOT NULL,
  `title` varchar(51) NOT NULL,
  `lobbyId` int(10) unsigned DEFAULT NULL,
  `isLan` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `isTeam` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `teamSize` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `registrationCost` smallint(3) unsigned NOT NULL DEFAULT '0',
  `rewards` text NOT NULL,
  `planetsPool` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `state` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`competitionId`),
  UNIQUE KEY `remoteId` (`remoteId`),
  KEY `state` (`state`),
  KEY `title` (`title`),
  KEY `competitionLobby` (`lobbyId`),
  CONSTRAINT `competitionLobby` FOREIGN KEY (`lobbyId`) REFERENCES `Matches` (`matchId`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table CompetitionManager.Cron
CREATE TABLE IF NOT EXISTS `Cron` (
  `lastExecution` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table CompetitionManager.DedicatedAccounts
CREATE TABLE IF NOT EXISTS `DedicatedAccounts` (
  `login` varchar(25) NOT NULL,
  `password` varchar(64) NOT NULL,
  `rpcHost` varchar(25) DEFAULT NULL,
  `rpcPort` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`login`),
  KEY `accountServer` (`rpcHost`,`rpcPort`),
  CONSTRAINT `accountServer` FOREIGN KEY (`rpcHost`, `rpcPort`) REFERENCES `Servers` (`rpcHost`, `rpcPort`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table CompetitionManager.Maps
CREATE TABLE IF NOT EXISTS `Maps` (
  `path` varchar(255) NOT NULL DEFAULT '',
  `filename` varchar(255) NOT NULL DEFAULT '',
  `uid` char(27) NOT NULL,
  `name` varchar(75) NOT NULL,
  `environment` varchar(15) NOT NULL,
  `mood` varchar(15) NOT NULL,
  `type` varchar(50) NOT NULL,
  `displayCost` int(10) unsigned NOT NULL,
  `nbLaps` int(10) unsigned NOT NULL DEFAULT '0',
  `authorLogin` varchar(25) NOT NULL,
  `authorNick` varchar(75) DEFAULT NULL,
  `authorZone` varchar(255) DEFAULT NULL,
  `authorTime` int(11) DEFAULT NULL,
  `goldTime` int(11) DEFAULT NULL,
  `silverTime` int(11) DEFAULT NULL,
  `bronzeTime` int(11) DEFAULT NULL,
  `authorScore` int(11) DEFAULT NULL,
  `size` int(10) unsigned NOT NULL,
  `mTime` datetime NOT NULL,
  PRIMARY KEY (`path`,`filename`),
  KEY `mTime` (`mTime`),
  KEY `size` (`size`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table CompetitionManager.Matches
CREATE TABLE IF NOT EXISTS `Matches` (
  `matchId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `stageId` int(10) unsigned NOT NULL,
  `startTime` datetime DEFAULT NULL,
  `endTime` datetime DEFAULT NULL,
  `rules` text,
  `state` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`matchId`),
  KEY `stageId` (`stageId`),
  KEY `state` (`state`),
  KEY `startTime` (`startTime`),
  CONSTRAINT `matchStage` FOREIGN KEY (`stageId`) REFERENCES `Stages` (`stageId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table CompetitionManager.MatchMaps
CREATE TABLE IF NOT EXISTS `MatchMaps` (
  `matchId` int(10) unsigned NOT NULL,
  `path` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  UNIQUE KEY `matchId_path_filename` (`matchId`,`path`,`filename`),
  KEY `matchMap` (`path`,`filename`),
  CONSTRAINT `mapMatch` FOREIGN KEY (`matchId`) REFERENCES `Matches` (`matchId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `matchMap` FOREIGN KEY (`path`, `filename`) REFERENCES `Maps` (`path`, `filename`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table CompetitionManager.MatchParticipants
CREATE TABLE IF NOT EXISTS `MatchParticipants` (
  `matchId` int(10) unsigned NOT NULL,
  `participantId` int(10) unsigned NOT NULL,
  `rank` int(10) unsigned DEFAULT NULL,
  `score` int(10) unsigned DEFAULT NULL,
  `scoreDetails` text,
  `qualified` tinyint(1) unsigned NOT NULL DEFAULT '2',
  UNIQUE KEY `matchId_login` (`matchId`,`participantId`),
  KEY `matchParticipant` (`participantId`),
  CONSTRAINT `matchParticipant` FOREIGN KEY (`participantId`) REFERENCES `Participants` (`participantId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `participantMatch` FOREIGN KEY (`matchId`) REFERENCES `Matches` (`matchId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table CompetitionManager.Participants
CREATE TABLE IF NOT EXISTS `Participants` (
  `participantId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(25) DEFAULT NULL,
  `teamId` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`participantId`),
  UNIQUE KEY `login` (`login`),
  UNIQUE KEY `teamId` (`teamId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table CompetitionManager.ParticipantTitles
CREATE TABLE IF NOT EXISTS `ParticipantTitles` (
  `participantId` int(10) unsigned NOT NULL,
  `title` varchar(51) NOT NULL,
  UNIQUE KEY `participantId_title` (`participantId`,`title`),
  CONSTRAINT `titleParticipant` FOREIGN KEY (`participantId`) REFERENCES `Participants` (`participantId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table CompetitionManager.Players
CREATE TABLE IF NOT EXISTS `Players` (
  `login` varchar(25) NOT NULL,
  `name` varchar(75) NOT NULL,
  `path` varchar(255) NOT NULL,
  PRIMARY KEY (`login`),
  CONSTRAINT `player` FOREIGN KEY (`login`) REFERENCES `Participants` (`login`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table CompetitionManager.Servers
CREATE TABLE IF NOT EXISTS `Servers` (
  `name` varchar(75) NOT NULL,
  `rpcHost` varchar(25) NOT NULL,
  `rpcPort` smallint(5) unsigned NOT NULL,
  `rpcPassword` varchar(50) NOT NULL,
  `startTime` datetime NOT NULL,
  `matchId` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`rpcHost`,`rpcPort`),
  KEY `serverMatch` (`matchId`),
  CONSTRAINT `serverMatch` FOREIGN KEY (`matchId`) REFERENCES `Matches` (`matchId`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table CompetitionManager.StageMaps
CREATE TABLE IF NOT EXISTS `StageMaps` (
  `stageId` int(10) unsigned NOT NULL,
  `path` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  UNIQUE KEY `stageId_path_filename` (`stageId`,`path`,`filename`),
  KEY `stageMap` (`path`,`filename`),
  CONSTRAINT `mapStage` FOREIGN KEY (`stageId`) REFERENCES `Stages` (`stageId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stageMap` FOREIGN KEY (`path`, `filename`) REFERENCES `Maps` (`path`, `filename`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table CompetitionManager.StageParticipants
CREATE TABLE IF NOT EXISTS `StageParticipants` (
  `stageId` int(10) unsigned NOT NULL,
  `participantId` int(10) unsigned NOT NULL,
  `rank` int(10) unsigned DEFAULT NULL,
  `score` int(10) unsigned DEFAULT NULL,
  `scoreDetails` text,
  `qualified` tinyint(1) unsigned NOT NULL DEFAULT '2',
  UNIQUE KEY `stageId_participantId` (`stageId`,`participantId`),
  KEY `stageParticipant` (`participantId`),
  CONSTRAINT `participantStage` FOREIGN KEY (`stageId`) REFERENCES `Stages` (`stageId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stageParticipant` FOREIGN KEY (`participantId`) REFERENCES `Participants` (`participantId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table CompetitionManager.Stages
CREATE TABLE IF NOT EXISTS `Stages` (
  `stageId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(2) unsigned NOT NULL,
  `competitionId` int(10) unsigned NOT NULL,
  `previousId` int(10) unsigned DEFAULT NULL,
  `nextId` int(10) unsigned DEFAULT NULL,
  `minSlots` smallint(5) unsigned NOT NULL DEFAULT '0',
  `maxSlots` smallint(5) unsigned NOT NULL DEFAULT '0',
  `state` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `startTime` datetime DEFAULT NULL,
  `endTime` datetime DEFAULT NULL,
  `rules` text NOT NULL,
  `schedule` text NOT NULL,
  `matches` text NOT NULL,
  `parameters` text NOT NULL,
  PRIMARY KEY (`stageId`),
  KEY `previousStage` (`previousId`),
  KEY `nextStage` (`nextId`),
  KEY `stageCup` (`competitionId`),
  KEY `state` (`state`),
  KEY `startTime` (`startTime`),
  CONSTRAINT `nextStage` FOREIGN KEY (`nextId`) REFERENCES `Stages` (`stageId`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `previousStage` FOREIGN KEY (`previousId`) REFERENCES `Stages` (`stageId`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `stageCompetition` FOREIGN KEY (`competitionId`) REFERENCES `Competitions` (`competitionId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table CompetitionManager.Teams
CREATE TABLE IF NOT EXISTS `Teams` (
  `teamId` int(10) unsigned NOT NULL,
  `tag` varchar(5) NOT NULL,
  `name` varchar(75) NOT NULL,
  `path` varchar(255) NOT NULL,
  `city` varchar(35) NOT NULL,
  `players` text,
  PRIMARY KEY (`teamId`),
  CONSTRAINT `team` FOREIGN KEY (`teamId`) REFERENCES `Participants` (`teamId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.


-- Dumping structure for table CompetitionManager.Transactions
CREATE TABLE IF NOT EXISTS `Transactions` (
  `transactionId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `remoteId` int(10) unsigned DEFAULT NULL,
  `competitionId` int(10) unsigned NOT NULL,
  `login` varchar(25) NOT NULL,
  `amount` int(10) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  `message` text,
  PRIMARY KEY (`transactionId`),
  UNIQUE KEY `remoteId` (`remoteId`),
  KEY `login` (`login`),
  KEY `type` (`type`),
  KEY `transactionCompetition` (`competitionId`),
  CONSTRAINT `transactionCompetition` FOREIGN KEY (`competitionId`) REFERENCES `Competitions` (`competitionId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
/*!40014 SET FOREIGN_KEY_CHECKS=1 */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
