-- -------------
--   TABLES   --
-- -------------

-- User roles (like admin, moderator, free user etc)
CREATE TABLE `UserRoles`
(
  `Id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `Title` CHAR(20) NOT NULL UNIQUE,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB;

-- A list of (all) activities a user (not everyone) can do
CREATE TABLE `UserPrivileges`
(
  `Id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `Title` CHAR(30) NOT NULL UNIQUE
    COMMENT 'What a user can do',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB;

-- Determines which activities are allowed for a give role
CREATE TABLE `PrivilegesByRole`
(
  `RoleId` INT UNSIGNED NOT NULL,
  `PrivilegeId` INT UNSIGNED NOT NULL,
  FOREIGN KEY (`RoleId`) REFERENCES `UserRoles` (`Id`),
  FOREIGN KEY (`PrivilegeId`) REFERENCES `UserPrivileges` (`Id`),
  PRIMARY KEY (`RoleId`, `PrivilegeId`)
) ENGINE=InnoDB;

-- Users personal data
-- Don't forget to make changes on createUser() when modifying this table!
CREATE TABLE `Users` (
  `Id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `RoleId` INT UNSIGNED NOT NULL,
  `Email` CHAR(255) NOT NULL UNIQUE,
  `Name` CHAR(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `Password` CHAR(32) NOT NULL,
  `RegDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    COMMENT 'Registration date',
  PRIMARY KEY (`Id`),
  KEY `AuthIndex` (`Email`,`Password`),
  FOREIGN KEY (`RoleId`) REFERENCES `UserRoles` (`Id`)
) ENGINE=InnoDB;

-- Current state of user's variables
-- When a user is deleted from Users table, a corresponding record from
-- this table is also deleted (cascade delition)
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
) ENGINE=InnoDB;

-- When a user of a give role is being created, we need to know how many
-- polls he/she can create etc.
CREATE TABLE `RatesByRole` (
  `Id` INT unsigned NOT NULL AUTO_INCREMENT,
  `RoleId` INT unsigned NOT NULL,
  `RateParameter` CHAR(30) NOT NULL,
  `RateValue` INT NOT NULL,
  FOREIGN KEY (`RoleId`) REFERENCES `UserRoles` (`Id`),
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='This table is used 
on creation of a user for initialization of rates';

-- List of polls
-- When a user is deleted from Users table, corresponding records from
-- this table are also deleted (cascade delition)
CREATE TABLE `Polls`
(
  `Id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `UserId` INT UNSIGNED NOT NULL
    COMMENT 'Poll creator. Equals zero for temporary users',
  `Name` CHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL
    COMMENT 'A name (title) for the poll',
  `CreationDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `FiltersMask` TINYINT UNSIGNED DEFAULT 0
    COMMENT 'Bit musk, defines which filters are used',
  FOREIGN KEY (`UserId`) REFERENCES `Users` (`Id`) ON DELETE CASCADE,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB;

-- List of items for polls (one item = question + options)
-- When a poll is deleted from Polls table, corresponding records from
-- this table are also deleted (cascade delition)
CREATE TABLE PollItems
(
  `Id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `PollId` INT UNSIGNED NOT NULL,
  `QuestionText` CHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ImagePath` CHAR(255),
  `InputType` ENUM('checkbox','radio','text') NOT NULL,
  `IsFinal` BOOLEAN
    COMMENT 'Final item (page) does not have questions, it is used to communicate with a quizzee',
  `FinalLink` CHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci
    COMMENT 'On competion of the poll the quizzee will be redirected according to this link',
  `FinalComment` VARCHAR(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  FOREIGN KEY (`PollId`) REFERENCES `Polls` (`Id`) ON DELETE CASCADE,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB;

-- List of options for a poll item
-- When a poll item is deleted from PollItems table, corresponding records from
-- this table are also deleted (cascade delition)
CREATE TABLE ItemOptions
(
  `Id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `PollId` INT UNSIGNED NOT NULL,
  `ItemId` INT UNSIGNED NOT NULL,
  `OptionText` CHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  FOREIGN KEY (`PollId`) REFERENCES `Polls`(`Id`),
  FOREIGN KEY (`ItemId`) REFERENCES `PollItems`(`Id`) ON DELETE CASCADE,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB;

-- Logic router. Determines which item (question) will go next based on
-- current item and selected option
CREATE TABLE Logic
(
  `PollId` INT UNSIGNED NOT NULL,
  `ItemId` INT UNSIGNED NOT NULL,
  `OptionId` INT UNSIGNED NOT NULL,
  `NextItemId` INT UNSIGNED NOT NULL
    COMMENT 'Zero when poll is complete',
  FOREIGN KEY (`PollId`) REFERENCES `Polls`(`Id`),
  FOREIGN KEY (`ItemId`) REFERENCES `PollItems`(`Id`),
  FOREIGN KEY (`OptionId`) REFERENCES `ItemOptions`(`Id`) ON DELETE CASCADE,
  PRIMARY KEY (`PollId`, `ItemId`, `OptionId`)
) ENGINE=InnoDB;










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
    INSERT INTO `Users` (`Id`, `RoleId`, `Email`, `Name`, `Password`, `RegDate`)
    VALUES (NULL, roleId, email, name, pwd, NULL);

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

-- Creating a sample poll
INSERT INTO `Polls` (`Id`, `UserId`, `Name`, `CreationDate`, `FiltersMask`)
VALUES (NULL, '1', 'Фрукты', NULL, '0');

INSERT INTO `PollItems` (`Id`, `PollId`, `QuestionText`, `ImagePath`, `InputType`, `IsFinal`, `FinalLink`, `FinalComment`)
VALUES (1, '1', 'Какие из этих фруктов Вы предпочитаете?', NULL, 'radio', NULL, NULL, NULL);

INSERT INTO `ItemOptions` (`Id`, `PollId`, `ItemId`, `OptionText`)
VALUES (NULL, '1', '1', 'Яблоки'), (NULL, '1', '1', 'Груши');

INSERT INTO `PollItems` (`Id`, `PollId`, `QuestionText`, `ImagePath`, `InputType`, `IsFinal`, `FinalLink`, `FinalComment`)
VALUES (2, '1', 'Какие яблоки вы любите больше?', NULL, 'radio', NULL, NULL, NULL);

INSERT INTO `ItemOptions` (`Id`, `PollId`, `ItemId`, `OptionText`)
VALUES (NULL, '1', '2', 'Большие'), (NULL, '1', '2', 'Маленькие');

INSERT INTO `PollItems` (`Id`, `PollId`, `QuestionText`, `ImagePath`, `InputType`, `IsFinal`, `FinalLink`, `FinalComment`)
VALUES (3, '1', 'Какие груши вы любите больше?', NULL, 'radio', NULL, NULL, NULL);

INSERT INTO `ItemOptions` (`Id`, `PollId`, `ItemId`, `OptionText`)
VALUES (NULL, '1', '3', 'Твёрдые'), (NULL, '1', '3', 'Мягкие');

-- Creating logic for the sample poll
INSERT INTO `surveyzilla`.`Logic` (`PollId`, `ItemId`, `OptionId`, `NextItemId`)
VALUES ('1', '1', '1', '2'), ('1', '1', '2', '3'), ('1', '2', '3', '0'), ('1', '2', '4', '0'), ('1', '3', '5', '0'), ('1', '3', '6', '0');