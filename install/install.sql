-- -------------
--   TABLES   --
-- -------------

-- User roles (like admin, moderator, free user etc)
CREATE TABLE `UserRoles`
(
  `Id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `Title` VARCHAR(20) NOT NULL UNIQUE,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- A list of (all) activities a user (not everyone) can do
CREATE TABLE `UserPrivileges`
(
  `Id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `Title` VARCHAR(30) NOT NULL UNIQUE
    COMMENT 'What a user can do',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Determines which activities are allowed for a give role
CREATE TABLE `PrivilegesByRole`
(
  `RoleId` INT UNSIGNED NOT NULL,
  `PrivilegeId` INT UNSIGNED NOT NULL,
  FOREIGN KEY (`RoleId`) REFERENCES `UserRoles` (`Id`),
  FOREIGN KEY (`PrivilegeId`) REFERENCES `UserPrivileges` (`Id`),
  PRIMARY KEY (`RoleId`, `PrivilegeId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Users personal data
CREATE TABLE `Users` (
  `Id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `RoleId` INT UNSIGNED NOT NULL,
  `Email` VARCHAR(255) NOT NULL UNIQUE,
  `Name` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `Password` VARCHAR(32) NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `AuthIndex` (`Email`,`Password`),
  FOREIGN KEY (`RoleId`) REFERENCES `UserRoles` (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Current state of user's variables
-- When a user is deleted from Users table, a corresponding record from
-- this table is also deleted
CREATE TABLE `UserMetrics`
(
  `UserId` INT UNSIGNED NOT NULL
    COMMENT 'Each user has one Rates record (which is deleted 
    with the user in a cascade manner)',
  `Balance` DECIMAL(7,2) DEFAULT '0.00',
  `PollsLeft` SMALLINT UNSIGNED DEFAULT '0'
    COMMENT 'Number of polls a user can create',
  `AnsLeft` MEDIUMINT UNSIGNED DEFAULT '0'
    COMMENT 'Number of answers a user is allowed to receive by the end
    of a period (usually a month)',
  `PeriodEnd` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`UserId`) REFERENCES `Users` (`Id`) ON DELETE CASCADE,
  PRIMARY KEY (`UserId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- When a user of a give role is being created, we need to know how many
-- polls he/she can create etc.
CREATE TABLE `RatesByRole` (
  `Id` INT unsigned NOT NULL AUTO_INCREMENT,
  `RoleId` INT unsigned NOT NULL,
  `RateParameter` varchar(30) NOT NULL,
  `RateValue` INT NOT NULL,
  FOREIGN KEY (`RoleId`) REFERENCES `UserRoles` (`Id`),
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='This table is used 
on creation of a user for initialization of rates';


-- -----------------
--   PROCEDURES   --
-- -----------------

-- Creates a new user:
--  * adds a record to Users table
--  * adds a record to UserMetrics table. Uses constants from RatesByRole table
--    for initial initialization
DROP PROCEDURE IF EXISTS `createUser`; 
DELIMITER //
CREATE PROCEDURE `createUser` (IN roleId INT, IN email VARCHAR(255), IN name VARCHAR(20) CHARSET utf8, IN pwd VARCHAR(32))
BEGIN
    START TRANSACTION;
    INSERT INTO `Users` (`Id`, `RoleId`, `Email`, `Name`, `Password`)
    VALUES (NULL, roleId, email, name, pwd);

    INSERT INTO `UserMetrics` (`UserId`, `PollsLeft`, `AnsLeft`, `PeriodEnd`)
    VALUES (
      (SELECT `Id` FROM `Users` WHERE `Users`.`Email` = email),
      (SELECT `RateValue` FROM `RatesByRole` WHERE `RatesByRole`.`RoleId` = roleId AND `RatesByRole`.`RateParameter` = 'PollsLeft'),
      (SELECT `RateValue` FROM `RatesByRole` WHERE `RatesByRole`.`RoleId` = roleId AND `RatesByRole`.`RateParameter` = 'AnsLeft'),
      (SELECT DATE_ADD(NOW(), INTERVAL 30 DAY))
    );
    COMMIT;
END//
DELIMITER ;


-- -----------
--   DATA   --
-- -----------

-- The roles one of which a user can play
INSERT INTO `UserRoles` (`Title`)
VALUES ('admin'), ('moderator'), ('temp'), ('free');

-- A list of activities of users on the website
INSERT INTO `UserPrivileges` (`Title`)
VALUES ('createPoll'), ('receiveAnswer');

-- Who (by role) can do what
INSERT INTO `PrivilegesByRole` (`RoleId`, `PrivilegeId`)
VALUES (1, 1), (1, 2);

-- Initial metrics for a given role (admin can create 100 polls and geather 1000 answers etc)
INSERT INTO `RatesByRole` (`RoleId`, `RateParameter`, `RateValue`)
VALUES (1, 'PollsLeft', 100), (1, 'AnsLeft', 1000);

-- Using a nice procedure to create the very first user
CALL createUser(1, 'admin@surveyzilla.ru', 'Admin', MD5('l234'));


