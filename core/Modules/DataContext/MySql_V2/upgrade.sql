/*
DROP TABLE IF EXISTS SC_Channels;
DROP TABLE IF EXISTS SC_Sources;
DROP TABLE IF EXISTS SC_Content;
DROP TABLE IF EXISTS SC_Tags;
DROP TABLE IF EXISTS SC_Content_Tags;
*/
-- *****************************************************************************
-- Tables 
-- *****************************************************************************

-- Create the Channel table
CREATE TABLE IF NOT EXISTS SC_Channels (
    id VARCHAR( 48 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    type VARCHAR( 48 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    subType VARCHAR( 256 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    active BIT( 1 ) NOT NULL ,
    inProcess BIT( 1 ) NOT NULL ,
    nextRun INT NOT NULL ,
    json TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    PRIMARY KEY ( id )
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Create the Sources Table
CREATE TABLE IF NOT EXISTS SC_Sources (
    id VARCHAR( 48 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    channelId VARCHAR( 48 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    score INT NULL ,
    name VARCHAR( 256 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    type  VARCHAR( 48 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    subType VARCHAR( 48 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    json TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    PRIMARY KEY ( id )
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Create the Content Table
CREATE TABLE IF NOT EXISTS SC_Content (
    id VARCHAR (48) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    sourceId VARCHAR( 48 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    state VARCHAR (48) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    date INT NOT NULL ,
    json TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    PRIMARY KEY ( id )
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;
-- Create the Tags Table
CREATE TABLE IF NOT EXISTS SC_Tags (
    id VARCHAR (48) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    type VARCHAR (48) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    text VARCHAR (256) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    PRIMARY KEY ( id )
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Create the Cotent_Tags
CREATE TABLE IF NOT EXISTS SC_Content_Tags (
    contentId VARCHAR (48) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    tagId VARCHAR (48) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
    PRIMARY KEY ( contentId, tagId )
) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;


-- *****************************************************************************
-- Channel Related Stored Procedures
-- *****************************************************************************

-- Create the GetChannelByChannelId stored procedure
DROP PROCEDURE IF EXISTS SC_GetChannelByChannelIds;
DELIMITER $$
CREATE PROCEDURE SC_GetChannelByChannelIds (IN channelIdsAsInArray VARCHAR(48))
    BEGIN
        DECLARE text VARCHAR (256);
        SET text = CONCAT('SELECT json, active, inProcess FROM SC_Channels WHERE id in ', channelIdsAsInArray);
        SET @queryText = text;
        PREPARE query FROM @queryText;
        EXECUTE query;
    END $$
DELIMITER ;

-- Create the SaveChannel stored procedure
DROP PROCEDURE IF EXISTS SC_SaveChannel;
DELIMITER $$
CREATE PROCEDURE SC_SaveChannel (
        IN channelId VARCHAR( 48 ),
        IN channelType VARCHAR( 48 ),
        IN channelSubType VARCHAR( 256 ),
        IN channelActive BIT( 1 ),
        IN channelInProcess BIT( 1 ),
        IN channelNextRun INT,
        IN channelJson TEXT)
    BEGIN
        DECLARE count INT DEFAULT 0;
        SET count = (SELECT count(id) FROM SC_Channels WHERE id = channelId);
        IF (count > 0) THEN
            UPDATE
                SC_Channels
            SET
                type = channelType,
                subType = channelSubType,
                active = channelActive,
                inProcess = channelInProcess,
                nextRun = channelNextRun,
                json = channelJson
            WHERE
                id = channelId;
        ELSE
            INSERT
                INTO SC_Channels
            VALUES (
                channelId,
                channelType,
                channelSubType,
                channelActive,
                channelInProcess,
                channelNextRun,
                channelJson);
        END IF;
    END $$
DELIMITER ;

-- Create the DeleteChannel stored procedure
DROP PROCEDURE IF EXISTS SC_DeleteChannel;
DELIMITER $$
CREATE PROCEDURE SC_DeleteChannel (IN channelId VARCHAR (48))
    BEGIN
        DELETE FROM SC_Channels WHERE id = channelId;
    END $$
DELIMITER ;

-- Create the SelectNextDueChannel stored procedure
DROP PROCEDURE IF EXISTS SC_SelectNextDueChannel;
DELIMITER $$
CREATE PROCEDURE SC_SelectNextDueChannel (IN dueBeforeTime INT)
    BEGIN
        SELECT
            json
        FROM
            SC_Channels
        WHERE
            nextRun <= dueBeforeTime
        AND
            active = 1
        AND
            inProcess = 0
        ORDER BY
            nextRun ASC
        LIMIT
            1;
    END $$
DELIMITER ;

-- Create the ListAllChannels Procedure
DROP PROCEDURE IF EXISTS SC_ListAllChannels;
DELIMITER $$
CREATE PROCEDURE SC_ListAllChannels ()
    BEGIN
        SELECT
            id, type, subType, active, inProcess, nextRun, json
        FROM
            SC_Channels;
    END $$
DELIMITER ;


-- *****************************************************************************
-- Content Related Stored Procedures
-- *****************************************************************************

-- Create the SaveContent stored procedure
DROP PROCEDURE IF EXISTS SC_SaveContent;
DELIMITER $$
CREATE PROCEDURE SC_SaveContent (
        contentId VARCHAR (48),
        contentType VARCHAR (48),
        contentDate INT,
        contentJson TEXT)
    BEGIN
        DECLARE count INT DEFAULT 0;
        SET count = (SELECT count(id) FROM SC_Content WHERE id = contentID);
        IF (count > 0) THEN
            UPDATE
                SC_Content
            SET
                type = contentType,
                date = contentDate,
                json = contentJson
            WHERE
                id = contentId;
        ELSE
            INSERT
                INTO SC_Content
            VALUES (
                contentId,
                contentType,
                contentDate,
                contentJson);
        END IF;
    END $$
DELIMITER ;

-- Create the GetContent stored procedure
DROP PROCEDURE IF EXISTS SC_GetContent;
DELIMITER $$
CREATE PROCEDURE SC_GetContent (contentIdsAsInArray VARCHAR (48))
    BEGIN
        SET @queryText = CONCAT('SELECT c.id, s.type, c.date, c.json FROM SC_Content c JOIN SC_Sources s ON c.sourceId = s.id WHERE c.id in ', contentIdsAsInArray);
        PREPARE query FROM @queryText;
        EXECUTE query;
    END $$
DELIMITER ;

-- Create the DeleteContent stored procedure
DROP PROCEDURE IF EXISTS SC_DeleteContent;
DELIMITER $$
CREATE PROCEDURE SC_DeleteContent (IN contentIdToDelete VARCHAR (48))
    BEGIN
        DELETE FROM SC_Content_Tags WHERE contentId = contentIdToDelete;
        DELETE FROM SC_Content WHERE id = contentIdToDelete;
    END $$
DELIMITER ;

-- *****************************************************************************
-- Source Related Stored Procedures
-- *****************************************************************************

-- Create the GetSourceById Stored procedure
