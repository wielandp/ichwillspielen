DROP DATABASE IF EXISTS tkl;
CREATE DATABASE tkl CHARACTER SET utf8;
CREATE USER 'tkl'@'localhost' IDENTIFIED BY 'wasauchimmer';
GRANT ALL PRIVILEGES ON tkl.* TO 'tkl'@'localhost' WITH GRANT OPTION;

USE tkl;
CREATE TABLE custom(
         id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
         title VARCHAR(64),
		 body VARCHAR(128),
		 start DATETIME,
		 end DATETIME,
		 typ INT,
		 uid INT
       ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
	   
ALTER TABLE custom AUTO_INCREMENT = 1000000;

CREATE UNIQUE INDEX reservation ON custom (start, uid);

CREATE TABLE weekly(
         id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
         title VARCHAR(64),
		 body VARCHAR(128),
		 day INT,
		 start TIME,
		 end TIME,
		 typ INT DEFAULT 1,
		 uid INT
       ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE setup(
         skey VARCHAR(64) PRIMARY KEY,
		 value VARCHAR(128)
       ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
INSERT INTO setup (skey, value) VALUES ('apptitle', 'TKL Tennis');
INSERT INTO setup (skey, value) VALUES ('mindate', '2016-01-01 00:00:00');
INSERT INTO setup (skey, value) VALUES ('maxdate', '2016-12-31 00:00:00');
INSERT INTO setup (skey, value) VALUES ('startdaytime', '8');
INSERT INTO setup (skey, value) VALUES ('enddaytime', '24');

CREATE TABLE IF NOT EXISTS `login` (
  `id` int(4) NOT NULL,
  `name` varchar(20) COLLATE utf8_bin NOT NULL,
  `pwd` varchar(40) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO login (`id`, `name`, `pwd`) VALUES ('1001', 'admin1', md5('admin1pw'));
INSERT INTO login (`id`, `name`, `pwd`) VALUES ('1002', 'admin2', md5('admin2pw'));
INSERT INTO login (`id`, `name`, `pwd`) VALUES ('1003', 'admin3', md5('admin3pw'));

CREATE TABLE savetransaction(
         id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		 action VARCHAR(16),
		 actiondate DATETIME,
		 ipaddr VARCHAR(16),
         title VARCHAR(64),
		 start DATETIME,
		 end DATETIME,
		 uid INT
       ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
	   
--
-- Tabelle `preise`
--
CREATE TABLE IF NOT EXISTS `preise` (
  `tagstunde` int(3) NOT NULL,
  `euro` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `preise` (`tagstunde`, `euro`) VALUES
(108, 18),(208, 18),(308, 18),(408, 18),(508, 18),(608, 21),
(8, 21),(109, 18),(209, 18),(309, 18),(409, 18),(509, 18),(609, 21),(9, 21),(110, 18),(210, 18),(310, 18),(410, 18),(510, 18),(610, 21),(10, 21),(111, 18),(211, 18),(311, 18),(411, 18),(511, 18),(611, 21),
(11, 21),(112, 18),(212, 18),(312, 18),(412, 18),(512, 18),(612, 21),(12, 21),(113, 18),(213, 18),(313, 18),(413, 18),(513, 18),(613, 21),(13, 21),(114, 21),(214, 21),(314, 21),(414, 21),(514, 21),(614, 21),
(14, 21),(115, 21),(215, 21),(315, 21),(415, 21),(515, 21),(615, 21)(15, 21),(116, 21),(216, 21),(316, 21),(416, 21),(516, 21),(616, 21),(16, 21),(117, 26),(217, 26),(317, 26),(417, 26),(517, 26),(617, 21),(17, 21),
(118, 26),(218, 26),(318, 26),(418, 26),(518, 26),(618, 21),(18, 21),(119, 26),(219, 26),(319, 26),(419, 26),(519, 26),(619, 21),(19, 21),(120, 26),(220, 26),(320, 26),(420, 26),(520, 21),(620, 21),(20, 21),
(121, 26),(221, 26),(321, 26),(421, 26),(521, 21),(621, 21),(21, 21),(122, 21),(222, 21),(322, 21),(422, 21),(522, 21),(622, 21),(22, 21),(123, 21),(223, 21),(323, 21),(423, 21),(523, 21),(623, 21),(23, 21);

--
-- Tabelle `accesslog`
--

CREATE TABLE IF NOT EXISTS `accesslog` (
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `typ` int(1) NOT NULL,
  `fail` int(1) NOT NULL,
  `ipaddr` varchar(16) COLLATE utf8_bin NOT NULL,
  `text` varchar(40) COLLATE utf8_bin NOT NULL,
  KEY `idx_accesslog` (`ts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
