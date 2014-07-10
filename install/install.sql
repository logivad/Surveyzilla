CREATE DATABASE surveyzilla DEFAULT CHARACTER SET utf8;

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
  `Type` ENUM('internal', 'vk', 'fb', 'gp') NOT NULL,
  `Email` CHAR(255) NOT NULL UNIQUE,
  `Name` CHAR(20) NOT NULL,
  `Password` CHAR(32) NOT NULL,
  `Hash` CHAR(32),
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
  `Name` CHAR(255) NOT NULL
    COMMENT 'A name (title) for the poll',
  `CreationDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `FiltersMask` TINYINT UNSIGNED DEFAULT 0
    COMMENT 'Bit mask, defines which filters are used',
  `ReportingMask` TINYINT UNSIGNED DEFAULT 0
    COMMENT 'Bit mask, defines What data to gather',
  FOREIGN KEY (`UserId`) REFERENCES `Users` (`Id`) ON DELETE CASCADE,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB;

-- List of items for polls (one item = question + options)
-- When a poll is deleted from Polls table, corresponding records from
-- this table are also deleted (cascade delition)
CREATE TABLE `PollItems`
(
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pollId` INT UNSIGNED NOT NULL,
  `questionText` CHAR(255) NOT NULL,
  `imagePath` CHAR(255),
  `inputType` ENUM('checkbox','radio','text') NOT NULL,
  `isFinal` BOOLEAN
    COMMENT 'Final item (page) does not have questions, it is used to 
    communicate with a quizzee',
  `finalLink` CHAR(255)
    COMMENT 'On competion of the poll the quizzee will be redirected according 
    to this link',
  `finalComment` VARCHAR(1000),
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
  `OptionText` CHAR(255) NOT NULL,
  FOREIGN KEY (`PollId`) REFERENCES `Polls`(`Id`),
  FOREIGN KEY (`ItemId`) REFERENCES `PollItems`(`id`) ON DELETE CASCADE,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB;

-- Logic router. Determines which item (question) will go next based on
-- current item and selected option
CREATE TABLE Logic
(
  `PollId` INT UNSIGNED NOT NULL,
  `ItemId` INT UNSIGNED NOT NULL,
  `Options` INT UNSIGNED NOT NULL
    COMMENT 'Bit mask of selected options',
  `NextItemId` INT UNSIGNED NOT NULL
    COMMENT 'Zero when poll is complete',
  FOREIGN KEY (`PollId`) REFERENCES `Polls`(`Id`),
  FOREIGN KEY (`ItemId`) REFERENCES `PollItems`(`id`) ON DELETE CASCADE,
  PRIMARY KEY (`PollId`, `ItemId`, `Options`)
) ENGINE=InnoDB;

-- If poll creator wishes, every answer of a registered quizzee is
-- recorder here (this is set in Polls.ReportingMask)
CREATE TABLE AnswersInternal
(
  `UserId` INT UNSIGNED NOT NULL,
  `PollId` INT UNSIGNED NOT NULL,
  `ItemId` INT UNSIGNED NOT NULL,
  `OptionId` INT UNSIGNED NOT NULL,
  `CustomText` VARCHAR(255),
  FOREIGN KEY (`PollId`) REFERENCES `Polls`(`Id`) ON DELETE CASCADE,
  FOREIGN KEY (`ItemId`) REFERENCES `PollItems`(`id`),
  FOREIGN KEY (`OptionId`) REFERENCES `ItemOptions`(`Id`),
  PRIMARY KEY (`UserId`, `PollId`, `ItemId`)
) ENGINE=InnoDB;

-- Accumulates answer of all quizzees (not only registered on the website)
CREATE TABLE PollResults
(
  `PollId` INT UNSIGNED NOT NULL,
  `ItemId` INT UNSIGNED NOT NULL,
  `OptionId` INT UNSIGNED NOT NULL,
  `VotesCount` INT UNSIGNED DEFAULT 0,
  FOREIGN KEY (`PollId`) REFERENCES `Polls`(`Id`) ON DELETE CASCADE,
  FOREIGN KEY (`ItemId`) REFERENCES `PollItems`(`id`),
  FOREIGN KEY (`OptionId`) REFERENCES `ItemOptions`(`Id`),
  PRIMARY KEY (`PollId`, `ItemId`, `OptionId`)
) ENGINE=InnoDB;

-- List of filters for poll
CREATE TABLE Filters
(
  `Id` INT NOT NULL AUTO_INCREMENT,
  `Name` VARCHAR(30),
  PRIMARY KEY (`Id`)
);

-- If this filter used, only registered users with given ID's
-- are allowed to run the poll
CREATE TABLE FilterPollAllowUserId
(
  `PollId` INT UNSIGNED NOT NULL,
  `UserId` INT UNSIGNED NOT NULL,
  FOREIGN KEY (`PollId`) REFERENCES `Polls`(`Id`) ON DELETE CASCADE,
  FOREIGN KEY (`UserId`) REFERENCES `Users`(`Id`),
  PRIMARY KEY (`PollId`, `UserId`)
);

-- Table for storing temporary Answer objects
-- When a user finishes answering a poll, corresponding record 
-- from this table is deleted
CREATE TABLE `AnswerTemp`
(
  `Token` DECIMAL(14,4) NOT NULL COMMENT 'PHP microtime, float',
  `AnswerObj` VARCHAR(500) NOT NULL,
  PRIMARY KEY (`Token`)
);


-- -----------------
--   PROCEDURES   --
-- -----------------

-- Creates a new user:
--  * adds a record to Users table
--  * adds a record to UserMetrics table. Uses constants from RatesByRole table
--    for initial initialization
DROP PROCEDURE IF EXISTS `createUser`; 
DELIMITER //
CREATE PROCEDURE `createUser` (
  IN roleId INT, 
  IN type CHAR(10),
  IN email CHAR(255), 
  IN name CHAR(20) CHARSET utf8, 
  IN pwd CHAR(32))
BEGIN
    START TRANSACTION;
    INSERT INTO `Users` (`Id`, `RoleId`, `Type`, `Email`, `Name`, `Password`, `RegDate`)
    VALUES (NULL, roleId, type, email, name, pwd, NULL);

    INSERT INTO `UserMetrics` (`UserId`, `PollsLeft`, `AnsLeft`, `PeriodEnd`)
    VALUES (
      (SELECT `Id` FROM `Users` WHERE `Users`.`Email` = email),
      (SELECT `RateValue` FROM `RatesByRole` WHERE `RatesByRole`.`RoleId` = roleId 
        AND `RatesByRole`.`RateParameter` = 'PollsLeft'),
      (SELECT `RateValue` FROM `RatesByRole` WHERE `RatesByRole`.`RoleId` = roleId 
        AND `RatesByRole`.`RateParameter` = 'AnsLeft'),
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

-- Initial metrics for a given role (admin can create 100 polls etc)
INSERT INTO `RatesByRole` (`RoleId`, `RateParameter`, `RateValue`)
VALUES (1, 'PollsLeft', 100), (1, 'AnsLeft', 1000);

-- Using a nice procedure to create the very first user
CALL createUser(1, 'internal', 'admin@surveyzilla.ru', 'Admin', MD5(CONCAT('x9gZhq!pgh','l234rt')));

-- Creating a sample poll
INSERT INTO `Polls` (`Id`, `UserId`, `Name`, `CreationDate`, `FiltersMask`)
VALUES (NULL, '1', 'Супер опрос', NULL, '0');

INSERT INTO `PollItems` (`Id`, `PollId`, `QuestionText`, `ImagePath`, `InputType`, `IsFinal`, `FinalComment`)
VALUES (1, '1', 'Будьте добры, укажите свой пол', NULL, 'radio', NULL, NULL);

INSERT INTO `ItemOptions` (`Id`, `PollId`, `ItemId`, `OptionText`)
VALUES (1, '1', '1', 'Мужчина'), (2, '1', '1', 'Женщина');

INSERT INTO `PollItems` (`Id`, `PollId`, `QuestionText`, `ImagePath`, `InputType`, `IsFinal`, `FinalComment`)
VALUES (2, '1', 'Какого цвета эта сумочка?', '/home/vadim/www/surveyzilla.dev/upload/1.jpg', 'radio', NULL, NULL);

INSERT INTO `ItemOptions` (`Id`, `PollId`, `ItemId`, `OptionText`)
VALUES (3, '1', '2', 'Красного'), (4, '1', '2', 'Синего');

INSERT INTO `PollItems` (`Id`, `PollId`, `QuestionText`, `ImagePath`, `InputType`, `IsFinal`, `FinalComment`)
VALUES (3, '1', 'Какого цвета этот автомобиль?', '/home/vadim/www/surveyzilla.dev/upload/2.jpg', 'radio', NULL, NULL);

INSERT INTO `ItemOptions` (`Id`, `PollId`, `ItemId`, `OptionText`)
VALUES (5, '1', '3', 'Красного'), (6, '1', '3', 'Синего');

INSERT INTO `PollItems` (`Id`, `PollId`, `QuestionText`, `ImagePath`, `InputType`, `IsFinal`, `FinalComment`)
VALUES (4, '1', 'Вы различаете цвета, это здорово! Идем дальше?', NULL, 'radio', NULL, NULL);

INSERT INTO `ItemOptions` (`Id`, `PollId`, `ItemId`, `OptionText`)
VALUES (7, '1', '4', 'Да'), (8, '1', '4', 'Не надо');

INSERT INTO `PollItems` (`Id`, `PollId`, `QuestionText`, `ImagePath`, `InputType`, `IsFinal`, `FinalComment`)
VALUES (5, '1', '', NULL, 'radio', 1, 'Вы не различаете цвета. Очень жаль, но для Вас опрос окончен :-(');

-- Creating logic for the sample poll
INSERT INTO `surveyzilla`.`Logic` (`PollId`, `ItemId`, `Options`, `NextItemId`)
VALUES 
('1', '1', '1', '3'),
('1', '1', '2', '2'),
('1', '2', '3', '4'),
('1', '2', '4', '5'),
('1', '3', '5', '4'),
('1', '3', '6', '5'),
('1', '4', '7', '6'),
('1', '4', '8', '0');