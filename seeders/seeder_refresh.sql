-- Seeder: Refresh non-auth data (preserves auth/login tables)
-- Preserved tables: `user`, `systemadmin`, `usertype`, `loginlog`, `ci_sessions`

SET FOREIGN_KEY_CHECKS=0;

-- Truncate domain tables (keeps auth tables intact)
TRUNCATE TABLE `campus`;
TRUNCATE TABLE `schoolyear`;
TRUNCATE TABLE `classes`;
TRUNCATE TABLE `section`;
TRUNCATE TABLE `teacher`;
TRUNCATE TABLE `parents`;
TRUNCATE TABLE `student`;
TRUNCATE TABLE `studentextend`;
TRUNCATE TABLE `studentgroup`;
TRUNCATE TABLE `studentrelation`;
TRUNCATE TABLE `transport`;
TRUNCATE TABLE `tmember`;
TRUNCATE TABLE `complain`;
TRUNCATE TABLE `city`;
TRUNCATE TABLE `country`;
TRUNCATE TABLE `conferences`;
TRUNCATE TABLE `conversation_message_info`;
TRUNCATE TABLE `conversation_msg`;

-- Re-insert minimal baseline lookups
INSERT INTO `campus` (`campusID`, `name`, `adminID`) VALUES
(1, 'Main Campus', 1);

INSERT INTO `schoolyear` (`schoolyearID`, `schooltype`, `schoolyear`, `schoolyeartitle`, `startingdate`, `endingdate`, `semestercode`, `create_date`, `modify_date`, `create_userID`, `create_username`, `create_usertype`, `adminID`) VALUES
(1, 'classbase', '2023', 'AY 2023-24', '2023-01-15', '2024-01-15', NULL, '2023-01-08 10:47:19', '2023-01-08 10:47:19', 1, 'admin', 'Admin', 1);

INSERT INTO `city` (`cityid`, `cityname`, `state_id`, `state_code`, `countryid`, `country_code`, `latitude`, `longitude`, `created_at`, `updated_on`, `flag`, `wikiDataId`) VALUES
(1, 'Andorra la Vella', 488, '07', 6, 'AD', 42.50779000, 1.52109000, '2019-10-05 13:28:06', '2019-10-05 13:28:06', 1, 'Q1863'),
(2, 'Arinsal', 493, '04', 6, 'AD', 42.57205000, 1.48453000, '2019-10-05 13:28:06', '2019-10-05 13:28:06', 1, 'Q24554'),
(3, 'Canillo', 489, '02', 6, 'AD', 42.56760000, 1.59756000, '2019-10-05 13:28:06', '2019-10-05 13:28:06', 1, 'Q24554'),
(4, 'El Tarter', 489, '02', 6, 'AD', 42.57952000, 1.65362000, '2019-10-05 13:28:06', '2019-10-05 13:28:06', 1, 'Q24413'),
(5, 'Encamp', 487, '03', 6, 'AD', 42.53474000, 1.58014000, '2019-10-05 13:28:06', '2019-10-05 13:28:06', 1, 'Q24413'),
(6, 'Ordino', 491, '05', 6, 'AD', 42.55623000, 1.53319000, '2019-10-05 13:28:06', '2019-10-05 13:28:06', 1, 'Q3885480'),
(7, 'Pas de la Casa', 487, '03', 6, 'AD', 42.54277000, 1.73361000, '2019-10-05 13:28:06', '2019-10-05 13:28:06', 1, 'Q24456'),
(8, 'Sant Julià de Lòria', 490, '06', 6, 'AD', 42.46372000, 1.49129000, '2019-10-05 13:28:06', '2020-05-01 08:22:33', 1, 'Q1120573'),
(9, 'la Massana', 493, '04', 6, 'AD', 42.54499000, 1.51483000, '2019-10-05 13:28:06', '2019-10-05 13:28:06', 1, 'Q3820973'),
(10, 'les Escaldes', 492, '08', 6, 'AD', 42.50729000, 1.53414000, '2019-10-05 13:28:06', '2019-10-05 13:28:06', 1, 'Q1050185');

INSERT INTO `country` (`countryid`, `countryname`, `iso3`, `iso2`, `phonecode`, `capital`, `currency`, `native`, `emoji`, `emojiU`, `created_at`, `updated_at`, `flag`, `wikiDataId`) VALUES
(1, 'Afghanistan', 'AFG', 'AF', '93', 'Kabul', 'AFN', 'افغانستان', '🇦🇫', 'U+1F1E6 U+1F1EB', '2018-07-20 15:11:03', '2020-05-16 05:49:11', 1, 'Q889'),
(2, 'Aland Islands', 'ALA', 'AX', '+358-18', 'Mariehamn', 'EUR', 'Åland', '🇦🇽', 'U+1F1E6 U+1F1FD', '2018-07-20 15:11:03', '2020-05-16 05:49:11', 1, NULL),
(3, 'Albania', 'ALB', 'AL', '355', 'Tirana', 'ALL', 'Shqipëria', '🇦🇱', 'U+1F1E6 U+1F1F1', '2018-07-20 15:11:03', '2020-05-16 05:49:11', 1, 'Q222'),
(4, 'Algeria', 'DZA', 'DZ', '213', 'Algiers', 'DZD', 'الجزائر', '🇩🇿', 'U+1F1E9 U+1F1FF', '2018-07-20 15:11:03', '2020-05-16 05:49:11', 1, 'Q262'),
(5, 'American Samoa', 'ASM', 'AS', '+1-684', 'Pago Pago', 'USD', 'American Samoa', '🇦🇸', 'U+1F1E6 U+1F1F8', '2018-07-20 15:11:03', '2020-05-16 05:49:11', 1, NULL),
(6, 'Andorra', 'AND', 'AD', '376', 'Andorra la Vella', 'EUR', 'Andorra', '🇦🇩', 'U+1F1E6 U+1F1E9', '2018-07-20 15:11:03', '2020-05-16 05:49:11', 1, 'Q228'),
(7, 'Angola', 'AGO', 'AO', '244', 'Luanda', 'AOA', 'Angola', '🇦🇴', 'U+1F1E6 U+1F1F4', '2018-07-20 15:11:03', '2020-05-16 05:49:11', 1, 'Q916'),
(8, 'Anguilla', 'AIA', 'AI', '+1-264', 'The Valley', 'XCD', 'Anguilla', '🇦🇮', 'U+1F1E6 U+1F1EE', '2018-07-20 15:11:03', '2020-05-16 05:49:11', 1, NULL),
(9, 'Antarctica', 'ATA', 'AQ', '', '', '', 'Antarctica', '🇦🇶', 'U+1F1E6 U+1F1F6', '2018-07-20 15:11:03', '2020-05-16 05:49:11', 1, NULL);
SET FOREIGN_KEY_CHECKS=1;

-- Notes:
-- 1) This script intentionally preserves authentication tables so login credentials remain unchanged.
-- 2) Add/remove TRUNCATE lines as needed for other domain tables.
