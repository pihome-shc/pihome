-- ------------------------------------------------------------------------
--  _____    _   _    _
-- |  __ \  (_) | |  | |
-- | |__) |  _  | |__| |   ___    _ __ ___     ___
-- |  ___/  | | |  __  |  / _ \  | |_  \_ \   / _ \
-- | |      | | | |  | | | (_) | | | | | | | |  __/
-- |_|      |_| |_|  |_|  \___/  |_| |_| |_|  \___|
--
--    S M A R T   H E A T I N G   C O N T R O L
--
--*************************************************************************
--* PiHome is Raspberry Pi based Central Heating Control systems. It runs *
--* from web interface and it comes with ABSOLUTELY NO WARRANTY, to the   *
--* extent permitted by applicable law. I take no responsibility for any  *
--* loss or damage to you or your property.                               *
--* DO NOT MAKE ANY CHANGES TO YOUR HEATING SYSTEM UNTILL UNLESS YOU KNOW *
--* WHAT YOU ARE DOING                                                    *
--*************************************************************************
--                                                      Have Fun - PiHome
-- Host:                         192.168.99.9
-- Server version:               5.5.47-0+deb7u1 - (Debian)
-- Server OS:                    debian-linux-gnu
-- HeidiSQL Version:             9.5.0.5459
-- ------------------------------------------------------------------------


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for pihome
CREATE DATABASE IF NOT EXISTS `pihome` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `pihome`;

-- Dumping structure for table pihome.away
DROP TABLE IF EXISTS `away`;
CREATE TABLE IF NOT EXISTS `away` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT '0',
  `purge` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT NULL,
  `start_datetime` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `end_datetime` timestamp NULL DEFAULT NULL,
  `away_button_id` int(11) DEFAULT '40',
  `away_button_child_id` int(11) DEFAULT '4',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.away: ~1 rows (approximately)
/*!40000 ALTER TABLE `away` DISABLE KEYS */;
REPLACE INTO `away` (`id`, `sync`, `purge`, `status`, `start_datetime`, `end_datetime`, `away_button_id`, `away_button_child_id`) VALUES
	(1, 0, 0, 0, '2019-01-21 21:21:19', NULL, 40, 4);
/*!40000 ALTER TABLE `away` ENABLE KEYS */;

-- Dumping structure for table pihome.boiler
DROP TABLE IF EXISTS `boiler`;
CREATE TABLE IF NOT EXISTS `boiler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT '0',
  `purge` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT '0',
  `fired_status` tinyint(4) DEFAULT '0',
  `name` char(50) COLLATE utf16_bin DEFAULT 'Gas Boiler',
  `node_id` int(11) DEFAULT NULL,
  `node_child_id` int(11) DEFAULT '1',
  `hysteresis_time` tinyint(4) DEFAULT '3',
  `max_operation_time` tinyint(4) DEFAULT '60',
  `datetime` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `gpio_pin` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_boiler_zone` (`node_id`),
  CONSTRAINT `FK_boiler_zone` FOREIGN KEY (`node_id`) REFERENCES `nodes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.boiler: ~1 rows (approximately)
/*!40000 ALTER TABLE `boiler` DISABLE KEYS */;
REPLACE INTO `boiler` (`id`, `sync`, `purge`, `status`, `fired_status`, `name`, `node_id`, `node_child_id`, `hysteresis_time`, `max_operation_time`, `datetime`, `gpio_pin`) VALUES
	(1, 0, 0, 1, 0, 'Gas Boiler', 5, 1, 3, 60, '2019-01-22 12:15:02', 24);
/*!40000 ALTER TABLE `boiler` ENABLE KEYS */;

-- Dumping structure for table pihome.boiler_logs
DROP TABLE IF EXISTS `boiler_logs`;
CREATE TABLE IF NOT EXISTS `boiler_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT '0',
  `purge` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Mark For Deletion',
  `start_datetime` timestamp NULL DEFAULT NULL,
  `start_cause` char(50) COLLATE utf16_bin DEFAULT NULL,
  `stop_datetime` timestamp NULL DEFAULT NULL,
  `stop_cause` char(50) COLLATE utf16_bin DEFAULT NULL,
  `expected_end_date_time` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.boiler_logs: ~0 rows (approximately)
/*!40000 ALTER TABLE `boiler_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `boiler_logs` ENABLE KEYS */;

-- Dumping structure for view pihome.boiler_view
DROP VIEW IF EXISTS `boiler_view`;
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `boiler_view` (
	`status` TINYINT(4) NULL,
	`sync` TINYINT(4) NOT NULL,
	`purge` TINYINT(4) NOT NULL COMMENT 'Mark For Deletion',
	`fired_status` TINYINT(4) NULL,
	`name` CHAR(50) NULL COLLATE 'utf16_bin',
	`node_id` CHAR(50) NOT NULL COLLATE 'utf16_bin',
	`node_child_id` INT(11) NULL,
	`hysteresis_time` TINYINT(4) NULL,
	`max_operation_time` TINYINT(4) NULL,
	`gpio_pin` INT(11) NULL
) ENGINE=MyISAM;

-- Dumping structure for table pihome.boost
DROP TABLE IF EXISTS `boost`;
CREATE TABLE IF NOT EXISTS `boost` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT '0',
  `purge` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT '0',
  `zone_id` int(11) DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `temperature` tinyint(4) DEFAULT NULL,
  `minute` tinyint(4) DEFAULT '30',
  `boost_button_id` int(11) DEFAULT NULL,
  `boost_button_child_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_boost_zone` (`zone_id`),
  CONSTRAINT `FK_boost_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.boost: ~3 rows (approximately)
/*!40000 ALTER TABLE `boost` DISABLE KEYS */;
REPLACE INTO `boost` (`id`, `sync`, `purge`, `status`, `zone_id`, `time`, `temperature`, `minute`, `boost_button_id`, `boost_button_child_id`) VALUES
	(8, 0, 0, 0, 33, '2019-01-21 21:21:19', 23, 30, 0, 0),
	(9, 0, 0, 0, 34, '2019-01-21 21:21:19', 23, 30, 0, 0),
	(10, 0, 0, 0, 35, '2019-01-21 21:21:19', 50, 60, 0, 0);
/*!40000 ALTER TABLE `boost` ENABLE KEYS */;

-- Dumping structure for view pihome.boost_view
DROP VIEW IF EXISTS `boost_view`;
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `boost_view` (
	`status` TINYINT(4) NULL,
	`sync` TINYINT(4) NOT NULL,
	`zone_id` INT(11) NULL,
	`index_id` TINYINT(4) NULL,
	`name` CHAR(50) NULL COLLATE 'utf8_bin',
	`temperature` TINYINT(4) NULL,
	`minute` TINYINT(4) NULL
) ENGINE=MyISAM;

-- Dumping structure for table pihome.frost_protection
DROP TABLE IF EXISTS `frost_protection`;
CREATE TABLE IF NOT EXISTS `frost_protection` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT '0',
  `purge` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Mark For Deletion',
  `datetime` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `temperature` float NOT NULL DEFAULT '5',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- Dumping data for table pihome.frost_protection: ~1 rows (approximately)
/*!40000 ALTER TABLE `frost_protection` DISABLE KEYS */;
REPLACE INTO `frost_protection` (`id`, `sync`, `purge`, `datetime`, `temperature`) VALUES
	(1, 0, 0, '2017-06-16 16:55:48', 8);
/*!40000 ALTER TABLE `frost_protection` ENABLE KEYS */;

-- Dumping structure for table pihome.gateway
DROP TABLE IF EXISTS `gateway`;
CREATE TABLE IF NOT EXISTS `gateway` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `sync` tinyint(4) NOT NULL DEFAULT '0',
  `purge` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Mark For Deletion',
  `type` char(50) COLLATE utf16_bin NOT NULL DEFAULT 'serial' COMMENT 'serial or wifi',
  `location` char(50) COLLATE utf16_bin NOT NULL DEFAULT '/dev/ttyAMA0' COMMENT 'ip address or serial port location i.e. /dev/ttyAMA0',
  `port` char(50) COLLATE utf16_bin NOT NULL DEFAULT '115200' COMMENT 'port number 5003 or baud rate115200 for serial gateway',
  `timout` char(50) COLLATE utf16_bin NOT NULL DEFAULT '3',
  `pid` char(50) COLLATE utf16_bin DEFAULT NULL,
  `pid_running_since` char(50) COLLATE utf16_bin DEFAULT NULL,
  `reboot` tinyint(4) DEFAULT '0',
  `find_gw` tinyint(4) DEFAULT '0',
  `version` char(50) COLLATE utf16_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.gateway: ~1 rows (approximately)
/*!40000 ALTER TABLE `gateway` DISABLE KEYS */;
REPLACE INTO `gateway` (`id`, `status`, `sync`, `purge`, `type`, `location`, `port`, `timout`, `pid`, `pid_running_since`, `reboot`, `find_gw`, `version`) VALUES
	(1, 0, 0, 0, 'wifi', '192.168.99.5', '5003', '3', '22183', 'Mon Jan 21 21:02:01 2019', 0, 1, '2.1.1\n');
/*!40000 ALTER TABLE `gateway` ENABLE KEYS */;

-- Dumping structure for table pihome.gateway_logs
DROP TABLE IF EXISTS `gateway_logs`;
CREATE TABLE IF NOT EXISTS `gateway_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT '0',
  `purge` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Mark For Deletion',
  `type` char(50) COLLATE utf16_bin DEFAULT 'wifi' COMMENT 'serial or wifi',
  `location` char(50) COLLATE utf16_bin DEFAULT '192.168.99.3' COMMENT 'ip address or serial port location i.e. /dev/ttyAMA0',
  `port` char(50) COLLATE utf16_bin DEFAULT '5003' COMMENT 'port number or baud rate for serial gateway',
  `pid` char(50) COLLATE utf16_bin DEFAULT NULL,
  `pid_start_time` char(50) COLLATE utf16_bin DEFAULT NULL,
  `pid_datetime` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.gateway_logs: ~0 rows (approximately)
/*!40000 ALTER TABLE `gateway_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `gateway_logs` ENABLE KEYS */;

-- Dumping structure for table pihome.messages_in
DROP TABLE IF EXISTS `messages_in`;
CREATE TABLE IF NOT EXISTS `messages_in` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT '0',
  `purge` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Mark For Deletion',
  `node_id` char(15) COLLATE utf16_bin DEFAULT NULL,
  `child_id` tinyint(4) DEFAULT NULL,
  `sub_type` int(11) DEFAULT NULL,
  `payload` decimal(10,2) DEFAULT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.messages_in: ~0 rows (approximately)
/*!40000 ALTER TABLE `messages_in` DISABLE KEYS */;
/*!40000 ALTER TABLE `messages_in` ENABLE KEYS */;

-- Dumping structure for view pihome.messages_in_view_24h
DROP VIEW IF EXISTS `messages_in_view_24h`;
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `messages_in_view_24h` (
	`node_id` CHAR(15) NULL COLLATE 'utf16_bin',
	`child_id` TINYINT(4) NULL,
	`datetime` TIMESTAMP NOT NULL,
	`payload` DECIMAL(10,2) NULL
) ENGINE=MyISAM;

-- Dumping structure for table pihome.messages_out
DROP TABLE IF EXISTS `messages_out`;
CREATE TABLE IF NOT EXISTS `messages_out` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT '0',
  `purge` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Mark For Deletion',
  `node_id` char(50) COLLATE utf32_bin NOT NULL COMMENT 'Node ID',
  `child_id` int(11) NOT NULL COMMENT 'Child Sensor',
  `sub_type` int(11) NOT NULL COMMENT 'Command Type',
  `ack` int(11) NOT NULL COMMENT 'Ack Req/Resp',
  `type` int(11) NOT NULL COMMENT 'Type',
  `payload` varchar(100) CHARACTER SET utf8 NOT NULL COMMENT 'Payload',
  `sent` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Sent Status 0 No - 1 Yes',
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Current datetime',
  `zone_id` int(11) NOT NULL COMMENT 'Zone ID related to this entery',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf32 COLLATE=utf32_bin;

-- Dumping data for table pihome.messages_out: ~6 rows (approximately)
/*!40000 ALTER TABLE `messages_out` DISABLE KEYS */;
REPLACE INTO `messages_out` (`id`, `sync`, `purge`, `node_id`, `child_id`, `sub_type`, `ack`, `type`, `payload`, `sent`, `datetime`, `zone_id`) VALUES
	(15, 0, 0, '101', 1, 1, 1, 2, '0', 1, '2019-01-22 15:38:03', 33),
	(17, 0, 0, '101', 2, 1, 1, 2, '0', 1, '2019-01-22 15:38:05', 34),
	(19, 0, 0, '101', 3, 1, 1, 2, '0', 1, '2019-01-22 15:38:07', 35),
	(21, 0, 0, '40', 1, 1, 1, 2, '1', 1, '2018-05-18 15:16:40', 33),
	(22, 0, 0, '40', 2, 1, 1, 2, '1', 1, '2018-05-18 15:16:42', 34),
	(23, 0, 0, '40', 3, 1, 1, 2, '1', 1, '2018-05-18 15:16:43', 35),
	(24, 0, 0, '100', 1, 1, 1, 2, '0', 0, '2018-05-13 00:08:01', 0);
/*!40000 ALTER TABLE `messages_out` ENABLE KEYS */;

-- Dumping structure for table pihome.nodes
DROP TABLE IF EXISTS `nodes`;
CREATE TABLE IF NOT EXISTS `nodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT '0',
  `purge` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Mark For Deletion',
  `node_id` char(50) COLLATE utf16_bin NOT NULL,
  `child_id_1` int(11) DEFAULT NULL,
  `child_id_2` INT(11) NULL DEFAULT NULL,
  `child_id_3` int(11) DEFAULT NULL,
  `child_id_4` int(11) DEFAULT NULL,
  `child_id_5` int(11) DEFAULT NULL,
  `child_id_6` int(11) DEFAULT NULL,
  `child_id_7` int(11) DEFAULT NULL,
  `child_id_8` int(11) DEFAULT NULL,
  `name` char(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `last_seen` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `status` char(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `ms_version` char(50) COLLATE utf16_bin DEFAULT NULL,
  `sketch_version` char(50) COLLATE utf16_bin DEFAULT NULL,
  `repeater` TINYINT(4) NULL DEFAULT NULL COMMENT 'Repeater Feature Enabled=1 or Disable=0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.nodes: ~8 rows (approximately)
/*!40000 ALTER TABLE `nodes` DISABLE KEYS */;
REPLACE INTO `nodes` (`id`, `sync`, `purge`, `node_id`, `child_id_1`, `child_id_2`, `child_id_3`, `child_id_4`, `child_id_5`, `child_id_6`, `child_id_7`, `child_id_8`, `name`, `last_seen`, `status`, `ms_version`, `sketch_version`) VALUES
	(0, 0, 0, '0', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Zone Controller Relay', '2019-01-21 21:21:19', NULL, NULL, NULL),
	(1, 0, 0, '21', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Temperature Sensor', '2019-01-22 15:38:22', 'Active', '2.1.1', '1.37'),
	(2, 0, 0, '20', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Temperature Sensor', '2019-01-22 15:37:46', 'Active', '2.1.1', '1.34'),
	(4, 0, 0, '30', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Temperature Sensor', '2019-01-22 15:37:50', 'Active', '2.1.1', '1.37'),
	(5, 0, 0, '100', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Boiler Relay', '2019-01-21 21:21:19', 'Active', NULL, '1.3'),
	(7, 0, 0, '101', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Zone Controller Relay', '2019-01-21 21:21:19', NULL, '2.1.1\n', '1.23'),
	(8, 0, 0, '40', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Button Console', '2019-01-21 21:21:19', NULL, '2.1.1\n', '1.31'),
	(9, 0, 1, '31', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Temperature Sensor', '2019-01-21 21:21:19', NULL, '2.1.1\n', '1.37');
/*!40000 ALTER TABLE `nodes` ENABLE KEYS */;

-- Dumping structure for table pihome.nodes_battery
DROP TABLE IF EXISTS `nodes_battery`;
CREATE TABLE IF NOT EXISTS `nodes_battery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT '0',
  `purge` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Mark For Deletion',
  `node_id` int(11) DEFAULT NULL,
  `bat_voltage` decimal(10,2) DEFAULT NULL,
  `bat_level` decimal(10,2) DEFAULT NULL,
  `update` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.nodes_battery: ~0 rows (approximately)
/*!40000 ALTER TABLE `nodes_battery` DISABLE KEYS */;
/*!40000 ALTER TABLE `nodes_battery` ENABLE KEYS */;

-- Dumping structure for table pihome.notice
DROP TABLE IF EXISTS `notice`;
CREATE TABLE IF NOT EXISTS `notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT '0',
  `purge` tinyint(4) NOT NULL DEFAULT '0',
  `datetime` datetime DEFAULT NULL,
  `message` varchar(200) COLLATE utf16_bin DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.notice: ~0 rows (approximately)
/*!40000 ALTER TABLE `notice` DISABLE KEYS */;
/*!40000 ALTER TABLE `notice` ENABLE KEYS */;

-- Dumping structure for table pihome.override
DROP TABLE IF EXISTS `override`;
CREATE TABLE IF NOT EXISTS `override` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT '0',
  `purge` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT '0',
  `zone_id` int(11) DEFAULT NULL,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `temperature` tinyint(4) DEFAULT '22',
  PRIMARY KEY (`id`),
  KEY `FK_override_zone` (`zone_id`),
  CONSTRAINT `FK_override_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.override: ~3 rows (approximately)
/*!40000 ALTER TABLE `override` DISABLE KEYS */;
REPLACE INTO `override` (`id`, `sync`, `purge`, `status`, `zone_id`, `time`, `temperature`) VALUES
	(8, 1, 0, 0, 33, '2019-01-20 19:07:30', 24),
	(9, 1, 0, 0, 34, '2019-01-19 00:48:08', 24),
	(10, 1, 0, 0, 35, '2019-01-19 00:48:06', 35);
/*!40000 ALTER TABLE `override` ENABLE KEYS */;

-- Dumping structure for view pihome.override_view
DROP VIEW IF EXISTS `override_view`;
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `override_view` (
	`status` TINYINT(4) NULL,
	`sync` TINYINT(4) NOT NULL,
	`zone_id` INT(11) NULL,
	`index_id` TINYINT(4) NULL,
	`name` CHAR(50) NULL COLLATE 'utf8_bin',
	`time` TIMESTAMP NULL,
	`temperature` TINYINT(4) NULL
) ENGINE=MyISAM;

-- Dumping structure for table pihome.piconnect
DROP TABLE IF EXISTS `piconnect`;
CREATE TABLE IF NOT EXISTS `piconnect` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` int(11) NOT NULL DEFAULT '0',
  `protocol` varchar(50) COLLATE utf16_bin DEFAULT NULL,
  `url` varchar(50) COLLATE utf16_bin DEFAULT NULL,
  `script` char(50) COLLATE utf16_bin DEFAULT NULL,
  `api_key` varchar(200) COLLATE utf16_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.piconnect: ~1 rows (approximately)
/*!40000 ALTER TABLE `piconnect` DISABLE KEYS */;
REPLACE INTO `piconnect` (`id`, `status`, `protocol`, `url`, `script`, `api_key`) VALUES
	(1, 0, 'http', 'www.pihome.eu', '/piconnect/mypihome.php', '');
/*!40000 ALTER TABLE `piconnect` ENABLE KEYS */;

-- Dumping structure for table pihome.schedule_daily_time
DROP TABLE IF EXISTS `schedule_daily_time`;
CREATE TABLE IF NOT EXISTS `schedule_daily_time` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT '0',
  `purge` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT NULL,
  `start` time DEFAULT NULL,
  `end` time DEFAULT NULL,
  `WeekDays` smallint(6) NOT NULL DEFAULT '127',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.schedule_daily_time: ~21 rows (approximately)
/*!40000 ALTER TABLE `schedule_daily_time` DISABLE KEYS */;
REPLACE INTO `schedule_daily_time` (`id`, `sync`, `purge`, `status`, `start`, `end`, `WeekDays`) VALUES
	(68, 0, 0, 1, '00:01:00', '00:35:00', 65),
	(69, 0, 0, 1, '14:15:00', '14:40:00', 127),
	(72, 0, 0, 1, '06:25:00', '07:00:00', 127),
	(73, 0, 0, 1, '10:00:00', '10:30:00', 62),
	(74, 0, 0, 1, '19:48:00', '20:00:00', 127),
	(75, 0, 0, 1, '02:00:00', '02:10:00', 15),
	(76, 0, 0, 1, '07:10:00', '07:45:00', 62),
	(77, 0, 0, 1, '02:25:00', '02:45:00', 127),
	(78, 0, 0, 1, '03:15:00', '03:45:00', 127),
	(79, 0, 0, 1, '04:00:00', '04:15:00', 127),
	(80, 0, 0, 1, '04:50:00', '05:05:00', 127),
	(81, 0, 0, 1, '11:05:00', '11:25:00', 127),
	(82, 0, 0, 1, '01:27:00', '01:45:00', 127),
	(83, 0, 0, 1, '02:25:00', '02:40:00', 127),
	(84, 0, 0, 1, '11:50:00', '12:15:00', 119),
	(85, 0, 0, 1, '20:30:00', '20:50:00', 119),
	(86, 0, 0, 1, '22:10:00', '22:30:00', 127),
	(87, 0, 0, 1, '23:00:00', '23:30:00', 127),
	(88, 0, 0, 1, '18:00:00', '18:15:00', 127),
	(89, 0, 0, 1, '19:00:00', '19:20:00', 127),
	(90, 0, 0, 1, '13:00:00', '13:15:00', 127);
/*!40000 ALTER TABLE `schedule_daily_time` ENABLE KEYS */;

-- Dumping structure for table pihome.schedule_daily_time_zone
DROP TABLE IF EXISTS `schedule_daily_time_zone`;
CREATE TABLE IF NOT EXISTS `schedule_daily_time_zone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT '0',
  `purge` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT NULL,
  `schedule_daily_time_id` int(11) DEFAULT NULL,
  `zone_id` int(11) DEFAULT NULL,
  `temperature` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_schedule_daily_time_zone_schedule_daily_time` (`schedule_daily_time_id`),
  KEY `FK_schedule_daily_time_zone_zone` (`zone_id`),
  CONSTRAINT `FK_schedule_daily_time_zone_schedule_daily_time` FOREIGN KEY (`schedule_daily_time_id`) REFERENCES `schedule_daily_time` (`id`),
  CONSTRAINT `FK_schedule_daily_time_zone_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=145 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.schedule_daily_time_zone: ~57 rows (approximately)
/*!40000 ALTER TABLE `schedule_daily_time_zone` DISABLE KEYS */;
REPLACE INTO `schedule_daily_time_zone` (`id`, `sync`, `purge`, `status`, `schedule_daily_time_id`, `zone_id`, `temperature`) VALUES
	(82, 0, 0, 0, 68, 33, 10),
	(83, 0, 0, 0, 68, 34, 21),
	(84, 0, 0, 0, 68, 35, 10),
	(85, 0, 0, 1, 69, 33, 20),
	(86, 0, 0, 0, 69, 34, 21),
	(87, 0, 0, 1, 69, 35, 32),
	(94, 0, 0, 0, 72, 33, 10),
	(95, 0, 0, 1, 72, 34, 20),
	(96, 0, 0, 0, 72, 35, 10),
	(97, 0, 0, 1, 73, 33, 20),
	(98, 0, 0, 0, 73, 34, 0),
	(99, 0, 0, 1, 73, 35, 30),
	(100, 0, 0, 0, 75, 33, 0),
	(101, 0, 0, 1, 75, 34, 21),
	(102, 0, 0, 0, 75, 35, 0),
	(103, 0, 0, 1, 76, 33, 20),
	(104, 0, 0, 1, 76, 34, 20),
	(105, 0, 0, 1, 76, 35, 32),
	(106, 0, 0, 0, 78, 33, 0),
	(107, 0, 0, 1, 78, 34, 20),
	(108, 0, 0, 0, 78, 35, 0),
	(109, 0, 0, 0, 79, 33, 0),
	(110, 0, 0, 1, 79, 34, 20),
	(111, 0, 0, 0, 79, 35, 0),
	(112, 0, 0, 0, 80, 33, 0),
	(113, 0, 0, 1, 80, 34, 20),
	(114, 0, 0, 0, 80, 35, 0),
	(115, 0, 0, 0, 81, 33, 21),
	(116, 0, 0, 0, 81, 34, 0),
	(117, 0, 0, 0, 81, 35, 0),
	(118, 0, 0, 0, 82, 33, 0),
	(119, 0, 0, 1, 82, 34, 20),
	(120, 0, 0, 0, 82, 35, 0),
	(121, 0, 0, 0, 83, 33, 0),
	(122, 0, 0, 1, 83, 34, 20),
	(123, 0, 0, 0, 83, 35, 0),
	(124, 0, 0, 1, 84, 33, 21),
	(125, 0, 0, 0, 84, 34, 0),
	(126, 0, 0, 1, 84, 35, 30),
	(127, 0, 0, 0, 85, 33, 0),
	(128, 0, 0, 1, 85, 34, 20),
	(129, 0, 0, 1, 85, 35, 28),
	(130, 0, 0, 0, 86, 33, 0),
	(131, 0, 0, 1, 86, 34, 19),
	(132, 0, 0, 0, 86, 35, 0),
	(133, 0, 0, 0, 87, 33, 0),
	(134, 0, 0, 1, 87, 34, 19),
	(135, 0, 0, 0, 87, 35, 0),
	(136, 0, 0, 1, 88, 33, 20),
	(137, 0, 0, 0, 88, 34, 0),
	(138, 0, 0, 1, 88, 35, 30),
	(139, 0, 0, 1, 89, 33, 20),
	(140, 0, 0, 0, 89, 34, 0),
	(141, 0, 0, 0, 89, 35, 0),
	(142, 0, 0, 0, 90, 33, 0),
	(143, 0, 0, 0, 90, 34, 0),
	(144, 0, 0, 1, 90, 35, 30);
/*!40000 ALTER TABLE `schedule_daily_time_zone` ENABLE KEYS */;

-- Dumping structure for view pihome.schedule_daily_time_zone_view
DROP VIEW IF EXISTS `schedule_daily_time_zone_view`;
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `schedule_daily_time_zone_view` (
	`time_id` INT(11) NOT NULL,
	`time_status` TINYINT(4) NULL,
	`start` TIME NULL,
	`end` TIME NULL,
	`WeekDays` SMALLINT(6) NOT NULL,
	`tz_sync` TINYINT(4) NOT NULL,
	`tz_id` INT(11) NOT NULL,
	`tz_status` TINYINT(4) NULL,
	`zone_id` INT(11) NULL,
	`index_id` TINYINT(4) NULL,
	`zone_name` CHAR(50) NULL COLLATE 'utf8_bin',
	`temperature` FLOAT NOT NULL
) ENGINE=MyISAM;

-- Dumping structure for table pihome.schedule_night_climate_time
DROP TABLE IF EXISTS `schedule_night_climate_time`;
CREATE TABLE IF NOT EXISTS `schedule_night_climate_time` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT '0',
  `purge` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT NULL,
  `start_time` time DEFAULT '21:00:00',
  `end_time` time DEFAULT '06:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.schedule_night_climate_time: ~1 rows (approximately)
/*!40000 ALTER TABLE `schedule_night_climate_time` DISABLE KEYS */;
REPLACE INTO `schedule_night_climate_time` (`id`, `sync`, `purge`, `status`, `start_time`, `end_time`) VALUES
	(1, 0, 0, 0, '18:00:00', '23:30:00');
/*!40000 ALTER TABLE `schedule_night_climate_time` ENABLE KEYS */;

-- Dumping structure for table pihome.schedule_night_climat_zone
DROP TABLE IF EXISTS `schedule_night_climat_zone`;
CREATE TABLE IF NOT EXISTS `schedule_night_climat_zone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT '0',
  `purge` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT NULL,
  `zone_id` int(11) DEFAULT NULL,
  `schedule_night_climate_id` int(11) DEFAULT NULL,
  `min_temperature` float NOT NULL DEFAULT '18',
  `max_temperature` float NOT NULL DEFAULT '21',
  PRIMARY KEY (`id`),
  KEY `FK_schedule_zone_night_climat_zone` (`zone_id`),
  KEY `FK_schedule_zone_night_climat_schedule_night_climate` (`schedule_night_climate_id`),
  CONSTRAINT `FK_schedule_zone_night_climat_schedule_night_climate` FOREIGN KEY (`schedule_night_climate_id`) REFERENCES `schedule_night_climate_time` (`id`),
  CONSTRAINT `FK_schedule_zone_night_climat_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.schedule_night_climat_zone: ~3 rows (approximately)
/*!40000 ALTER TABLE `schedule_night_climat_zone` DISABLE KEYS */;
REPLACE INTO `schedule_night_climat_zone` (`id`, `sync`, `purge`, `status`, `zone_id`, `schedule_night_climate_id`, `min_temperature`, `max_temperature`) VALUES
	(8, 0, 0, 0, 33, 1, 18, 20),
	(9, 0, 0, 1, 34, 1, 19, 21),
	(10, 0, 0, 0, 35, 1, 19, 21);
/*!40000 ALTER TABLE `schedule_night_climat_zone` ENABLE KEYS */;

-- Dumping structure for view pihome.schedule_night_climat_zone_view
DROP VIEW IF EXISTS `schedule_night_climat_zone_view`;
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `schedule_night_climat_zone_view` (
	`t_status` TINYINT(4) NULL,
	`z_status` TINYINT(4) NULL,
	`zone_id` INT(11) NULL,
	`start_time` TIME NULL,
	`end_time` TIME NULL,
	`min_temperature` FLOAT NOT NULL,
	`max_temperature` FLOAT NOT NULL
) ENGINE=MyISAM;

-- Dumping structure for table pihome.system
DROP TABLE IF EXISTS `system`;
CREATE TABLE IF NOT EXISTS `system` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT '0',
  `purge` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Mark For Deletion',
  `name` varchar(50) COLLATE utf16_bin DEFAULT NULL,
  `version` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `build` varchar(50) COLLATE utf16_bin DEFAULT NULL,
  `update_location` char(250) CHARACTER SET latin1 DEFAULT NULL,
  `update_file` char(100) CHARACTER SET latin1 DEFAULT NULL,
  `update_alias` char(100) CHARACTER SET latin1 DEFAULT NULL,
  `country` char(2) CHARACTER SET latin1 DEFAULT NULL,
  `language` CHAR(10) NULL DEFAULT 'en' COLLATE 'utf16_bin',
  `city` char(100) CHARACTER SET latin1 DEFAULT NULL,
  `zip` char(100) COLLATE utf16_bin DEFAULT NULL,
  `openweather_api` char(100) CHARACTER SET latin1 DEFAULT NULL,
  `backup_email` char(100) COLLATE utf16_bin DEFAULT NULL,
  `ping_home` bit(1) DEFAULT b'1',
  `timezone` varchar(50) COLLATE utf16_bin DEFAULT 'Europe/Dublin',
  `shutdown` tinyint(4) DEFAULT '0',
  `reboot` tinyint(4) DEFAULT '0',
  `c_f` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=C, 1=F',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.system: ~1 rows (approximately)
/*!40000 ALTER TABLE `system` DISABLE KEYS */;
REPLACE INTO `system` (`id`, `sync`, `purge`, `name`, `version`, `build`, `update_location`, `update_file`, `update_alias`, `country`, `language`, `city`, `zip`, `openweather_api`, `backup_email`, `ping_home`, `timezone`, `shutdown`, `reboot`, `c_f`) VALUES
	(2, 0, 0, 'PiHome - Smart Heating Control', '1.6', '080419', 'http://www.pihome.eu/updates/', 'current-release-versions.php', 'pihome', 'IE', 'en', 'Portlaoise', NULL, '', '', b'1', 'Europe/Dublin', 0, 0, 0);
/*!40000 ALTER TABLE `system` ENABLE KEYS */;

-- Dumping structure for table pihome.user
DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_enable` tinyint(1) DEFAULT NULL,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(25) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `cpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `account_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `backup` tinyint(4) DEFAULT NULL,
  `users` tinyint(4) DEFAULT NULL,
  `support` tinyint(4) DEFAULT NULL,
  `settings` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- Dumping data for table pihome.user: ~1 rows (approximately)
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
REPLACE INTO `user` (`id`, `account_enable`, `fullname`, `username`, `email`, `password`, `cpdate`, `account_date`, `backup`, `users`, `support`, `settings`) VALUES
	(1, 1, 'Administrator', 'admin', '', '0f5f9ba0136d5a8588b3fc70ec752869', '2019-01-22 15:55:45', '2017-06-13 16:10:31', 1, 1, 1, 1);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;

-- Dumping structure for table pihome.userhistory
DROP TABLE IF EXISTS `userhistory`;
CREATE TABLE IF NOT EXISTS `userhistory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `audit` tinytext,
  `ipaddress` tinytext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table pihome.userhistory: ~0 rows (approximately)
/*!40000 ALTER TABLE `userhistory` DISABLE KEYS */;
/*!40000 ALTER TABLE `userhistory` ENABLE KEYS */;

-- Dumping structure for table pihome.weather
DROP TABLE IF EXISTS `weather`;
CREATE TABLE IF NOT EXISTS `weather` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT '0',
  `location` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `c` tinyint(4) DEFAULT NULL,
  `wind_speed` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `title` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `description` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `sunrise` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `sunset` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `img` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last weather update',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Dumping data for table pihome.weather: ~1 rows (approximately)
/*!40000 ALTER TABLE `weather` DISABLE KEYS */;
REPLACE INTO `weather` (`id`, `sync`, `location`, `c`, `wind_speed`, `title`, `description`, `sunrise`, `sunset`, `img`, `last_update`) VALUES
	(1, 0, 'Portlaoise', 2, '7', 'Clouds', 'scattered clouds', '1548059271', '1548089642', '03n', '2019-01-21 21:21:20');
/*!40000 ALTER TABLE `weather` ENABLE KEYS */;

-- Dumping structure for table pihome.zone
DROP TABLE IF EXISTS `zone`;
CREATE TABLE IF NOT EXISTS `zone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT '0',
  `purge` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT NULL,
  `index_id` tinyint(4) DEFAULT NULL,
  `name` char(50) COLLATE utf8_bin DEFAULT NULL,
  `type` char(50) COLLATE utf8_bin DEFAULT NULL,
  `model` char(50) COLLATE utf8_bin DEFAULT NULL,
  `max_c` tinyint(4) DEFAULT NULL,
  `max_operation_time` tinyint(4) DEFAULT NULL,
  `hysteresis_time` tinyint(4) DEFAULT NULL,
  `sp_deadband` float NOT NULL DEFAULT '0.5'
  `sensor_id` int(11) DEFAULT NULL,
  `sensor_child_id` int(11) DEFAULT NULL,
  `controler_id` int(11) DEFAULT NULL,
  `controler_child_id` int(11) DEFAULT NULL,
  `boiler_id` int(11) DEFAULT NULL,
  `gpio_pin` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_zone_nodes` (`sensor_id`),
  KEY `FK_zone_nodes_2` (`controler_id`),
  KEY `FK_zone_boiler` (`boiler_id`),
  CONSTRAINT `FK_zone_boiler` FOREIGN KEY (`boiler_id`) REFERENCES `boiler` (`id`),
  CONSTRAINT `FK_zone_nodes` FOREIGN KEY (`sensor_id`) REFERENCES `nodes` (`id`),
  CONSTRAINT `FK_zone_nodes_2` FOREIGN KEY (`controler_id`) REFERENCES `nodes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Dumping data for table pihome.zone: ~3 rows (approximately)
/*!40000 ALTER TABLE `zone` DISABLE KEYS */;
REPLACE INTO `zone` (`id`, `sync`, `purge`, `status`, `index_id`, `name`, `type`, `model`, `max_c`, `max_operation_time`, `hysteresis_time`, `sensor_id`, `sensor_child_id`, `controler_id`, `controler_child_id`, `boiler_id`, `gpio_pin`) VALUES
	(33, 0, 0, 1, 1, 'Ground Floor', 'Heating', 'DE000F', 25, 60, 3, 1, 0, 7, 1, 1, NULL),
	(34, 0, 0, 1, 2, 'First Floor', 'Heating', '7D0096', 25, 60, 3, 2, 0, 7, 2, 1, 22),
	(35, 0, 0, 1, 5, 'Ch. Hot Water', 'Water', '009604', 45, 60, 3, 4, 0, 7, 3, 1, 23);
/*!40000 ALTER TABLE `zone` ENABLE KEYS */;

-- Dumping structure for table pihome.zone_logs
DROP TABLE IF EXISTS `zone_logs`;
CREATE TABLE IF NOT EXISTS `zone_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT '0',
  `purge` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Mark For Deletion',
  `zone_id` int(11) DEFAULT NULL,
  `boiler_log_id` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_zone_logs_zone` (`zone_id`),
  KEY `FK_zone_logs_boiler_logs` (`boiler_log_id`),
  CONSTRAINT `FK_zone_logs_boiler_logs` FOREIGN KEY (`boiler_log_id`) REFERENCES `boiler_logs` (`id`),
  CONSTRAINT `FK_zone_logs_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.zone_logs: ~0 rows (approximately)
/*!40000 ALTER TABLE `zone_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `zone_logs` ENABLE KEYS */;

-- Dumping structure for view pihome.zone_log_view
DROP VIEW IF EXISTS `zone_log_view`;
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `zone_log_view` (
	`id` INT(11) NOT NULL,
	`zone_id` INT(11) NULL,
	`type` CHAR(50) NULL COLLATE 'utf8_bin',
	`boiler_log_id` INT(11) NULL,
	`start_datetime` TIMESTAMP NULL,
	`stop_datetime` TIMESTAMP NULL,
	`expected_end_date_time` TIMESTAMP NULL,
	`status` INT(11) NULL
) ENGINE=MyISAM;

-- Dumping structure for view pihome.zone_view
DROP VIEW IF EXISTS `zone_view`;
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `zone_view` (
	`status` TINYINT(4) NULL,
	`sync` TINYINT(4) NOT NULL,
	`id` INT(11) NOT NULL,
	`index_id` TINYINT(4) NULL,
	`name` CHAR(50) NULL COLLATE 'utf8_bin',
	`type` CHAR(50) NULL COLLATE 'utf8_bin',
	`max_c` TINYINT(4) NULL,
	`max_operation_time` TINYINT(4) NULL,
	`hysteresis_time` TINYINT(4) NULL,
  `sp_deadband` FLOAT NOT NULL,
	`sensors_id` CHAR(50) NOT NULL COLLATE 'utf16_bin',
	`sensor_child_id` INT(11) NULL,
	`controler_id` CHAR(50) NOT NULL COLLATE 'utf16_bin',
	`controler_child_id` INT(11) NULL,
	`gpio_pin` INT(11) NULL,
	`last_seen` TIMESTAMP NULL,
	`ms_version` CHAR(50) NULL COLLATE 'utf16_bin',
	`sketch_version` CHAR(50) NULL COLLATE 'utf16_bin'
) ENGINE=MyISAM;

-- Dumping structure for view pihome.boiler_view
DROP VIEW IF EXISTS `boiler_view`;
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `boiler_view`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `boiler_view` AS select `boiler`.`status` AS `status`,`boiler`.`sync` AS `sync`,`boiler`.`purge` AS `purge`,`boiler`.`fired_status` AS `fired_status`,`boiler`.`name` AS `name`,`nodes`.`node_id` AS `node_id`,`boiler`.`node_child_id` AS `node_child_id`,`boiler`.`hysteresis_time` AS `hysteresis_time`,`boiler`.`max_operation_time` AS `max_operation_time`,`boiler`.`gpio_pin` AS `gpio_pin` from (`boiler` join `nodes` on((`boiler`.`node_id` = `nodes`.`id`))) where (`boiler`.`purge` = '0');

-- Dumping structure for view pihome.boost_view
DROP VIEW IF EXISTS `boost_view`;
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `boost_view`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `boost_view` AS select `boost`.`status` AS `status`,`boost`.`sync` AS `sync`,`boost`.`zone_id` AS `zone_id`,`zone_idx`.`index_id` AS `index_id`,`zone`.`name` AS `name`,`boost`.`temperature` AS `temperature`,`boost`.`minute` AS `minute` from ((`boost` join `zone` on((`boost`.`zone_id` = `zone`.`id`))) join `zone` `zone_idx` on((`boost`.`zone_id` = `zone_idx`.`id`)));

-- Dumping structure for view pihome.messages_in_view_24h
DROP VIEW IF EXISTS `messages_in_view_24h`;
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `messages_in_view_24h`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `messages_in_view_24h` AS select `messages_in`.`node_id` AS `node_id`,`messages_in`.`child_id` AS `child_id`,`messages_in`.`datetime` AS `datetime`,`messages_in`.`payload` AS `payload` from `messages_in` where (`messages_in`.`datetime` > (now() - interval 24 hour));

-- Dumping structure for view pihome.override_view
DROP VIEW IF EXISTS `override_view`;
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `override_view`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `override_view` AS select `override`.`status` AS `status`,`override`.`sync` AS `sync`,`override`.`zone_id` AS `zone_id`,`zone_idx`.`index_id` AS `index_id`,`zone`.`name` AS `name`,`override`.`time` AS `time`,`override`.`temperature` AS `temperature` from ((`override` join `zone` on((`override`.`zone_id` = `zone`.`id`))) join `zone` `zone_idx` on((`override`.`zone_id` = `zone_idx`.`id`)));

-- Dumping structure for view pihome.schedule_daily_time_zone_view
DROP VIEW IF EXISTS `schedule_daily_time_zone_view`;
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `schedule_daily_time_zone_view`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `schedule_daily_time_zone_view` AS select `ss`.`id` AS `time_id`,`ss`.`status` AS `time_status`,`sstart`.`start` AS `start`,`send`.`end` AS `end`,`sWeekDays`.`WeekDays` AS `WeekDays`,`sdtz`.`sync` AS `tz_sync`,`sdtz`.`id` AS `tz_id`,`sdtz`.`status` AS `tz_status`,`sdtz`.`zone_id` AS `zone_id`,`zone`.`index_id` AS `index_id`,`zone`.`name` AS `zone_name`,`sdtz`.`temperature` AS `temperature` from (((((`schedule_daily_time_zone` `sdtz` join `schedule_daily_time` `ss` on((`sdtz`.`schedule_daily_time_id` = `ss`.`id`))) join `schedule_daily_time` `sstart` on((`sdtz`.`schedule_daily_time_id` = `sstart`.`id`))) join `schedule_daily_time` `send` on((`sdtz`.`schedule_daily_time_id` = `send`.`id`))) join `schedule_daily_time` `sWeekDays` on((`sdtz`.`schedule_daily_time_id` = `sWeekDays`.`id`))) join `zone` on((`sdtz`.`zone_id` = `zone`.`id`))) where (`sdtz`.`purge` = '0') order by `zone`.`index_id`;

-- Dumping structure for view pihome.schedule_night_climat_zone_view
DROP VIEW IF EXISTS `schedule_night_climat_zone_view`;
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `schedule_night_climat_zone_view`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `schedule_night_climat_zone_view` AS select `tnct`.`status` AS `t_status`,`ncz`.`status` AS `z_status`,`ncz`.`zone_id` AS `zone_id`,`snct`.`start_time` AS `start_time`,`enct`.`end_time` AS `end_time`,`ncz`.`min_temperature` AS `min_temperature`,`ncz`.`max_temperature` AS `max_temperature` from (((`schedule_night_climat_zone` `ncz` join `schedule_night_climate_time` `snct` on((`ncz`.`schedule_night_climate_id` = `snct`.`id`))) join `schedule_night_climate_time` `enct` on((`ncz`.`schedule_night_climate_id` = `enct`.`id`))) join `schedule_night_climate_time` `tnct` on((`ncz`.`schedule_night_climate_id` = `tnct`.`id`)));

-- Dumping structure for view pihome.zone_log_view
DROP VIEW IF EXISTS `zone_log_view`;
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `zone_log_view`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `zone_log_view` AS select `zone_logs`.`id` AS `id`,`zone_logs`.`zone_id` AS `zone_id`,`ztype`.`type` AS `type`,`zone_logs`.`boiler_log_id` AS `boiler_log_id`,`blst`.`start_datetime` AS `start_datetime`,`blet`.`stop_datetime` AS `stop_datetime`,`blext`.`expected_end_date_time` AS `expected_end_date_time`,`zone_logs`.`status` AS `status` from ((((`zone_logs` join `zone` `ztype` on((`zone_logs`.`zone_id` = `ztype`.`id`))) join `boiler_logs` `blst` on((`zone_logs`.`boiler_log_id` = `blst`.`id`))) join `boiler_logs` `blet` on((`zone_logs`.`boiler_log_id` = `blet`.`id`))) join `boiler_logs` `blext` on((`zone_logs`.`boiler_log_id` = `blext`.`id`))) order by `zone_logs`.`id`;

-- Dumping structure for view pihome.zone_view
DROP VIEW IF EXISTS `zone_view`;
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `zone_view`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `zone_view` AS select `zone`.`status` AS `status`,`zone`.`sync` AS `sync`,`zone`.`id` AS `id`,`zone`.`index_id` AS `index_id`,`zone`.`name` AS `name`,`zone`.`type` AS `type`,`zone`.`max_c` AS `max_c`,`zone`.`max_operation_time` AS `max_operation_time`,`zone`.`hysteresis_time` AS `hysteresis_time`,`zone`.`sp_deadband` AS `sp_deadband`,`sid`.`node_id` AS `sensors_id`,`zone`.`sensor_child_id` AS `sensor_child_id`,`cid`.`node_id` AS `controler_id`,`zone`.`controler_child_id` AS `controler_child_id`,`zone`.`gpio_pin` AS `gpio_pin`,`bid`.`node_id` AS `boiler_id`,`lasts`.`last_seen` AS `last_seen`,`msv`.`ms_version` AS `ms_version`,`skv`.`sketch_version` AS `sketch_version` from ((((((`zone` join `nodes` `sid` on((`zone`.`sensor_id` = `sid`.`id`))) join `nodes` `cid` on((`zone`.`controler_id` = `cid`.`id`))) join `nodes` `bid` on((`zone`.`boiler_id` = `bid`.`id`))) join `nodes` `lasts` on((`zone`.`sensor_id` = `lasts`.`id`))) join `nodes` `msv` on((`zone`.`sensor_id` = `msv`.`id`))) join `nodes` `skv` on((`zone`.`sensor_id` = `skv`.`id`))) where (`zone`.`purge` = '0');

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
