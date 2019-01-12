-- --------------------------------------------------------
-- Host:                         192.168.99.9
-- Server version:               5.5.47-0+deb7u1 - (Debian)
-- Server OS:                    debian-linux-gnu
-- HeidiSQL Version:             9.5.0.5196
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

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
	(1, 0, 0, 0, '2018-09-21 09:58:51', NULL, 40, 4);
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
	(1, 0, 0, 1, 0, 'Gas Boiler', 5, 1, 3, 60, '2018-09-21 09:58:55', 24);
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
	(8, 0, 0, 0, 33, '2018-09-21 10:27:44', 23, 30, 0, 0),
	(9, 0, 0, 0, 34, '2018-09-21 10:27:45', 23, 30, 0, 0),
	(10, 0, 0, 0, 35, '2018-09-21 10:27:46', 40, 30, 0, 0);
/*!40000 ALTER TABLE `boost` ENABLE KEYS */;

-- Dumping structure for table pihome.frost_protection
DROP TABLE IF EXISTS `frost_protection`;
CREATE TABLE IF NOT EXISTS `frost_protection` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT '0',
  `purge` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Mark For Deletion',
  `datetime` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `temperature` tinyint(4) DEFAULT '5',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- Dumping data for table pihome.frost_protection: ~1 rows (approximately)
/*!40000 ALTER TABLE `frost_protection` DISABLE KEYS */;
REPLACE INTO `frost_protection` (`id`, `sync`, `purge`, `datetime`, `temperature`) VALUES
	(1, 0, 0, '2017-06-16 16:55:48', -1);
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
	(1, 0, 1, 0, 'wifi', '192.168.99.5', '5003', '3', '6968', 'Wed Sep 19 17:56:01 2018', 0, 0, '2.1.1\n');
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

-- Dumping data for table pihome.messages_out: ~7 rows (approximately)
/*!40000 ALTER TABLE `messages_out` DISABLE KEYS */;
REPLACE INTO `messages_out` (`id`, `sync`, `purge`, `node_id`, `child_id`, `sub_type`, `ack`, `type`, `payload`, `sent`, `datetime`, `zone_id`) VALUES
	(15, 0, 0, '101', 1, 1, 1, 2, '0', 1, '2018-09-21 09:56:02', 33),
	(17, 0, 0, '101', 2, 1, 1, 2, '0', 1, '2018-09-21 09:56:04', 34),
	(19, 0, 0, '101', 3, 1, 1, 2, '0', 1, '2018-09-21 09:56:07', 35),
	(21, 0, 0, '40', 1, 1, 1, 2, '1', 1, '2018-05-18 15:16:40', 33),
	(22, 0, 0, '40', 2, 1, 1, 2, '1', 1, '2018-05-18 15:16:42', 34),
	(23, 0, 0, '40', 3, 1, 1, 2, '1', 1, '2018-05-18 15:16:43', 35),
	(24, 0, 0, '100', 1, 1, 1, 2, '0', 1, '2018-09-21 09:56:09', 0);
/*!40000 ALTER TABLE `messages_out` ENABLE KEYS */;

-- Dumping structure for table pihome.nodes
DROP TABLE IF EXISTS `nodes`;
CREATE TABLE IF NOT EXISTS `nodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT '0',
  `purge` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Mark For Deletion',
  `node_id` char(50) COLLATE utf16_bin NOT NULL,
  `child_id_1` int(11) DEFAULT NULL,
  `child_id_2` int(11) DEFAULT NULL,
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.nodes: ~7 rows (approximately)
/*!40000 ALTER TABLE `nodes` DISABLE KEYS */;
REPLACE INTO `nodes` (`id`, `sync`, `purge`, `node_id`, `child_id_1`, `child_id_2`, `child_id_3`, `child_id_4`, `child_id_5`, `child_id_6`, `child_id_7`, `child_id_8`, `name`, `last_seen`, `status`, `ms_version`, `sketch_version`) VALUES
	(0, 0, 0, '0', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Zone Controller Relay', '2018-09-21 10:28:11', NULL, NULL, NULL),
	(1, 0, 0, '21', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Temperature Sensor', '2018-09-21 10:28:13', 'Active', '2.1.1', '1.36'),
	(2, 0, 0, '20', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Temperature Sensor', '2018-09-21 10:28:14', 'Active', '2.1.1', '1.36'),
	(4, 0, 0, '30', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Temperature Sensor', '2018-09-21 10:28:15', 'Active', '2.1.1', '1.36'),
	(5, 0, 0, '100', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Boiler Relay', '2018-09-21 10:28:16', 'Active', NULL, '1.3'),
	(7, 0, 0, '101', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Zone Controller Relay', '2018-09-21 10:28:17', NULL, '2.1.1\n', '1.2'),
	(8, 0, 0, '40', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Button Console', '2018-09-21 10:28:19', NULL, '2.1.1\n', '1.31');
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
	(8, 0, 0, 0, 33, '2018-09-21 10:28:25', 24),
	(9, 0, 0, 0, 34, '2018-09-21 10:28:26', 24),
	(10, 0, 0, 0, 35, '2018-09-21 10:28:27', 35);
/*!40000 ALTER TABLE `override` ENABLE KEYS */;

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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.schedule_daily_time: ~21 rows (approximately)
/*!40000 ALTER TABLE `schedule_daily_time` DISABLE KEYS */;
REPLACE INTO `schedule_daily_time` (`id`, `sync`, `purge`, `status`, `start`, `end`) VALUES
	(60, 1, 0, 1, '18:00:00', '18:15:00'),
	(61, 1, 0, 1, '19:10:00', '19:20:00'),
	(64, 1, 0, 1, '16:30:00', '16:50:00'),
	(65, 1, 0, 1, '09:15:00', '09:45:00'),
	(67, 1, 0, 1, '12:00:00', '12:15:00'),
	(68, 1, 0, 1, '14:15:00', '14:30:00'),
	(69, 1, 0, 1, '17:15:00', '17:45:00'),
	(71, 1, 0, 1, '10:00:00', '10:30:00'),
	(72, 1, 0, 1, '13:00:00', '13:15:00'),
	(73, 1, 0, 1, '06:15:00', '06:30:00'),
	(80, 1, 0, 1, '02:00:00', '02:20:00'),
	(81, 1, 0, 1, '03:00:00', '03:25:00'),
	(82, 1, 0, 1, '04:00:00', '04:15:00'),
	(83, 1, 0, 1, '04:50:00', '05:05:00'),
	(84, 1, 0, 1, '18:45:00', '18:55:00'),
	(85, 1, 0, 1, '19:50:00', '20:00:00'),
	(86, 1, 0, 1, '20:30:00', '21:00:00'),
	(87, 1, 0, 1, '21:30:00', '21:45:00'),
	(90, 1, 0, 1, '13:33:00', '14:20:00'),
	(92, 1, 0, 1, '01:00:00', '01:30:00'),
	(93, 1, 0, 1, '02:42:00', '03:30:00');
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
  `temperature` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_schedule_daily_time_zone_schedule_daily_time` (`schedule_daily_time_id`),
  KEY `FK_schedule_daily_time_zone_zone` (`zone_id`),
  CONSTRAINT `FK_schedule_daily_time_zone_schedule_daily_time` FOREIGN KEY (`schedule_daily_time_id`) REFERENCES `schedule_daily_time` (`id`),
  CONSTRAINT `FK_schedule_daily_time_zone_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=238 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.schedule_daily_time_zone: ~63 rows (approximately)
/*!40000 ALTER TABLE `schedule_daily_time_zone` DISABLE KEYS */;
REPLACE INTO `schedule_daily_time_zone` (`id`, `sync`, `purge`, `status`, `schedule_daily_time_id`, `zone_id`, `temperature`) VALUES
	(123, 1, 0, 1, 60, 33, 19),
	(124, 1, 0, 0, 60, 34, 0),
	(125, 1, 0, 0, 60, 35, 0),
	(126, 1, 0, 0, 61, 33, 19),
	(127, 1, 0, 0, 61, 34, 0),
	(128, 1, 0, 0, 61, 35, 0),
	(135, 1, 0, 1, 64, 33, 20),
	(136, 1, 0, 0, 64, 34, 0),
	(137, 1, 0, 0, 64, 35, 26),
	(139, 1, 0, 1, 65, 33, 21),
	(140, 1, 0, 0, 65, 34, 0),
	(141, 1, 0, 1, 65, 35, 27),
	(148, 1, 0, 1, 67, 33, 21),
	(149, 1, 0, 0, 67, 34, 0),
	(150, 1, 0, 0, 67, 35, 28),
	(151, 1, 0, 1, 68, 33, 20),
	(152, 1, 0, 0, 68, 34, 0),
	(153, 1, 0, 1, 68, 35, 28),
	(154, 1, 0, 0, 69, 33, 21),
	(155, 1, 0, 0, 69, 34, 0),
	(156, 1, 0, 1, 69, 35, 28),
	(162, 1, 0, 1, 71, 33, 20),
	(163, 1, 0, 0, 71, 34, 0),
	(164, 1, 0, 1, 71, 35, 26),
	(166, 1, 0, 0, 72, 33, 0),
	(167, 1, 0, 0, 72, 34, 0),
	(168, 1, 0, 1, 72, 35, 24),
	(170, 1, 0, 1, 73, 33, 19),
	(171, 1, 0, 1, 73, 34, 20),
	(172, 1, 0, 0, 73, 35, 28),
	(191, 1, 0, 0, 80, 33, 0),
	(192, 1, 0, 1, 80, 34, 20),
	(193, 1, 0, 0, 80, 35, 0),
	(194, 1, 0, 0, 81, 33, 0),
	(195, 1, 0, 1, 81, 34, 20),
	(196, 1, 0, 0, 81, 35, 0),
	(197, 1, 0, 0, 82, 33, 0),
	(198, 1, 0, 1, 82, 34, 20),
	(199, 1, 0, 0, 82, 35, 0),
	(200, 1, 0, 0, 83, 33, 0),
	(201, 1, 0, 1, 83, 34, 20),
	(202, 1, 0, 0, 83, 35, 0),
	(203, 1, 0, 0, 84, 33, 20),
	(204, 1, 0, 0, 84, 34, 0),
	(205, 1, 0, 1, 84, 35, 28),
	(206, 1, 0, 1, 85, 33, 21),
	(207, 1, 0, 0, 85, 34, 20),
	(208, 1, 0, 1, 85, 35, 26),
	(209, 1, 0, 0, 86, 33, 0),
	(210, 1, 0, 1, 86, 34, 19),
	(211, 1, 0, 0, 86, 35, 0),
	(212, 1, 0, 0, 87, 33, 20),
	(213, 1, 0, 0, 87, 34, 20),
	(214, 1, 0, 1, 87, 35, 26),
	(225, 1, 0, 0, 90, 33, 21),
	(226, 1, 0, 0, 90, 34, 0),
	(227, 1, 0, 0, 90, 35, 25),
	(232, 1, 0, 0, 92, 33, 0),
	(233, 1, 0, 1, 92, 34, 20),
	(234, 1, 0, 0, 92, 35, 0),
	(235, 1, 0, 0, 93, 33, 0),
	(236, 1, 0, 1, 93, 34, 23),
	(237, 1, 0, 0, 93, 35, 0);
/*!40000 ALTER TABLE `schedule_daily_time_zone` ENABLE KEYS */;

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
	(1, 1, 0, 0, '21:30:00', '05:30:00');
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
  `min_temperature` int(11) DEFAULT '18',
  `max_temperature` int(11) DEFAULT '21',
  PRIMARY KEY (`id`),
  KEY `FK_schedule_zone_night_climat_zone` (`zone_id`),
  KEY `FK_schedule_zone_night_climat_schedule_night_climate` (`schedule_night_climate_id`),
  CONSTRAINT `FK_schedule_zone_night_climat_schedule_night_climate` FOREIGN KEY (`schedule_night_climate_id`) REFERENCES `schedule_night_climate_time` (`id`),
  CONSTRAINT `FK_schedule_zone_night_climat_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.schedule_night_climat_zone: ~3 rows (approximately)
/*!40000 ALTER TABLE `schedule_night_climat_zone` DISABLE KEYS */;
REPLACE INTO `schedule_night_climat_zone` (`id`, `sync`, `purge`, `status`, `zone_id`, `schedule_night_climate_id`, `min_temperature`, `max_temperature`) VALUES
	(8, 1, 0, 0, 33, 1, 18, 21),
	(9, 1, 0, 1, 34, 1, 20, 22),
	(10, 1, 0, 0, 35, 1, 18, 21);
/*!40000 ALTER TABLE `schedule_night_climat_zone` ENABLE KEYS */;

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
  `city` char(100) CHARACTER SET latin1 DEFAULT NULL,
  `openweather_api` char(100) CHARACTER SET latin1 DEFAULT NULL,
  `backup_email` char(100) COLLATE utf16_bin DEFAULT NULL,
  `ping_home` bit(1) DEFAULT b'1',
  `timezone` varchar(50) COLLATE utf16_bin DEFAULT 'Europe/Dublin',
  `shutdown` tinyint(4) DEFAULT '0',
  `reboot` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.system: ~1 rows (approximately)
/*!40000 ALTER TABLE `system` DISABLE KEYS */;
REPLACE INTO `system` (`id`, `sync`, `purge`, `name`, `version`, `build`, `update_location`, `update_file`, `update_alias`, `country`, `city`, `openweather_api`, `backup_email`, `ping_home`, `timezone`, `shutdown`, `reboot`) VALUES
	(2, 0, 0, 'PiHome - Smart Heating Control', '1.23', '040918', 'http://www.pihome.eu/updates/', 'current-release-versions.php', 'pihome', 'IE', 'Portlaoise', '', '', b'1', 'Europe/Dublin', 0, 0);
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
	(1, 1, 'Administrator', 'admin', 'info@pihome.eu', '0f5f9ba0136d5a8588b3fc70ec752869', '2018-03-31 23:30:59', '2017-06-13 16:10:31', 1, 1, 1, 1);
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
	(1, 1, 'Portlaoise', 9, '14.97', 'Clouds', 'broken clouds', '1537510422', '1537554575', '04d', '2018-09-21 09:30:19');
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
	(33, 1, 0, 1, 1, 'Ground Floor', 'Heating', NULL, 25, 60, 3, 1, 0, 7, 1, 1, NULL),
	(34, 1, 0, 1, 2, 'First Floor', 'Heating', NULL, 25, 60, 3, 2, 0, 7, 2, 1, 22),
	(35, 1, 0, 1, 5, 'Ch. Hot Water', 'Water', NULL, 45, 60, 3, 4, 0, 7, 3, 1, 23);
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

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
