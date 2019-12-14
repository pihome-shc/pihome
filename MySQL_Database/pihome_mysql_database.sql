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
-- *************************************************************************
-- * PiHome is Raspberry Pi based Central Heating Control systems. It runs *
-- * from web interface and it comes with ABSOLUTELY NO WARRANTY, to the   *
-- * extent permitted by applicable law. I take no responsibility for any  *
-- * loss or damage to you or your property.                               *
-- * DO NOT MAKE ANY CHANGES TO YOUR HEATING SYSTEM UNTILL UNLESS YOU KNOW *
-- * WHAT YOU ARE DOING                                                    *
-- *************************************************************************
-- --------------------------------------------------------
-- Host:                         192.168.99.11
-- Server version:               10.3.15-MariaDB-1 - Raspbian testing-staging
-- Server OS:                    debian-linux-gnueabihf
-- HeidiSQL Version:             10.2.0.5599
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
  `sync` tinyint(4) NOT NULL DEFAULT 0,
  `purge` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT NULL,
  `start_datetime` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `end_datetime` timestamp NULL DEFAULT NULL,
  `away_button_id` int(11) DEFAULT 40,
  `away_button_child_id` int(11) DEFAULT 4,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.away: 1 rows
/*!40000 ALTER TABLE `away` DISABLE KEYS */;
REPLACE INTO `away` (`id`, `sync`, `purge`, `status`, `start_datetime`, `end_datetime`, `away_button_id`, `away_button_child_id`) VALUES
	(1, 1, 0, 0, '2019-07-16 18:45:07', NULL, 40, 4);
/*!40000 ALTER TABLE `away` ENABLE KEYS */;

-- Dumping structure for table pihome.boiler
DROP TABLE IF EXISTS `boiler`;
CREATE TABLE IF NOT EXISTS `boiler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT 0,
  `purge` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT 0,
  `fired_status` tinyint(4) DEFAULT 0,
  `name` char(50) CHARACTER SET utf16 COLLATE utf16_bin DEFAULT 'Gas Boiler',
  `node_id` int(11) DEFAULT NULL,
  `node_child_id` int(11) DEFAULT 1,
  `hysteresis_time` tinyint(4) DEFAULT 3,
  `max_operation_time` tinyint(4) DEFAULT 60,
  `datetime` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `gpio_pin` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_boiler_zone` (`node_id`),
  CONSTRAINT `FK_boiler_zone` FOREIGN KEY (`node_id`) REFERENCES `nodes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- Dumping data for table pihome.boiler: ~0 rows (approximately)
/*!40000 ALTER TABLE `boiler` DISABLE KEYS */;
REPLACE INTO `boiler` (`id`, `sync`, `purge`, `status`, `fired_status`, `name`, `node_id`, `node_child_id`, `hysteresis_time`, `max_operation_time`, `datetime`, `gpio_pin`) VALUES
	(1, 0, 0, 1, 0, 'Gas Boiler', 5, 1, 3, 60, '2019-07-17 16:00:01', 24);
/*!40000 ALTER TABLE `boiler` ENABLE KEYS */;

-- Dumping structure for table pihome.boiler_logs
DROP TABLE IF EXISTS `boiler_logs`;
CREATE TABLE IF NOT EXISTS `boiler_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT 0,
  `purge` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Mark For Deletion',
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
  `sync` tinyint(4) NOT NULL DEFAULT 0,
  `purge` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT 0,
  `zone_id` int(11) DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `temperature` tinyint(4) DEFAULT NULL,
  `minute` tinyint(4) DEFAULT 30,
  `boost_button_id` int(11) DEFAULT NULL,
  `boost_button_child_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_boost_zone` (`zone_id`),
  CONSTRAINT `FK_boost_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.boost: ~6 rows (approximately)
/*!40000 ALTER TABLE `boost` DISABLE KEYS */;
REPLACE INTO `boost` (`id`, `sync`, `purge`, `status`, `zone_id`, `time`, `temperature`, `minute`, `boost_button_id`, `boost_button_child_id`) VALUES
	(8, 1, 0, 0, 33, '2019-07-12 21:21:19', 23, 30, 40, 1),
	(9, 1, 0, 0, 34, '2019-07-09 10:59:11', 23, 30, 40, 3),
	(10, 1, 0, 0, 35, '2019-07-12 15:38:26', 40, 50, 0, 0),
	(12, 1, 0, 0, 35, '2019-07-12 15:17:16', 50, 60, 40, 4),
	(13, 1, 0, 0, 35, '2019-07-12 15:17:18', 60, 80, 40, 5),
	(18, 1, 0, 0, 35, '2019-07-12 15:17:18', 70, 100, 40, 5);
/*!40000 ALTER TABLE `boost` ENABLE KEYS */;

-- Dumping structure for table pihome.crontab
DROP TABLE IF EXISTS `crontab`;
CREATE TABLE IF NOT EXISTS `crontab` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` char(50) DEFAULT '#',
  `min` char(50) DEFAULT '*/1',
  `hour` char(50) DEFAULT '*',
  `day` char(50) DEFAULT '*',
  `month` char(50) DEFAULT '*',
  `weekday` char(50) DEFAULT '*',
  `command` char(50) DEFAULT '',
  `output` char(50) DEFAULT '>/dev/null 2>&1',
  `comments` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='PiHome Smart Heating - Manage Crontab from web interface ';

-- Dumping data for table pihome.crontab: ~0 rows (approximately)
/*!40000 ALTER TABLE `crontab` DISABLE KEYS */;
REPLACE INTO `crontab` (`id`, `status`, `min`, `hour`, `day`, `month`, `weekday`, `command`, `output`, `comments`) VALUES
	(1, '#', '*/1', '*', '*', '*', '*', 'ls -l', '>/dev/null 2>&1', NULL);
/*!40000 ALTER TABLE `crontab` ENABLE KEYS */;

-- Dumping structure for table pihome.email
DROP TABLE IF EXISTS `email`;
CREATE TABLE IF NOT EXISTS `email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT 0,
  `purge` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Mark For Deletion',
  `smtp` char(50) COLLATE utf16_bin DEFAULT NULL,
  `username` char(50) COLLATE utf16_bin DEFAULT NULL,
  `password` char(50) COLLATE utf16_bin DEFAULT NULL,
  `from` char(50) COLLATE utf16_bin DEFAULT NULL,
  `to` char(50) COLLATE utf16_bin DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.email: 0 rows
/*!40000 ALTER TABLE `email` DISABLE KEYS */;
/*!40000 ALTER TABLE `email` ENABLE KEYS */;

-- Dumping structure for table pihome.frost_protection
DROP TABLE IF EXISTS `frost_protection`;
CREATE TABLE IF NOT EXISTS `frost_protection` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT 0,
  `purge` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Mark For Deletion',
  `datetime` timestamp NULL DEFAULT current_timestamp(),
  `temperature` float NOT NULL DEFAULT 5,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- Dumping data for table pihome.frost_protection: ~0 rows (approximately)
/*!40000 ALTER TABLE `frost_protection` DISABLE KEYS */;
REPLACE INTO `frost_protection` (`id`, `sync`, `purge`, `datetime`, `temperature`) VALUES
	(1, 1, 0, '2017-06-16 16:55:48', 4);
/*!40000 ALTER TABLE `frost_protection` ENABLE KEYS */;

-- Dumping structure for table pihome.gateway
DROP TABLE IF EXISTS `gateway`;
CREATE TABLE IF NOT EXISTS `gateway` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `sync` tinyint(4) NOT NULL DEFAULT 0,
  `purge` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Mark For Deletion',
  `type` char(50) COLLATE utf16_bin NOT NULL DEFAULT 'serial' COMMENT 'serial or wifi',
  `location` char(50) COLLATE utf16_bin NOT NULL DEFAULT '/dev/ttyAMA0' COMMENT 'ip address or serial port location i.e. /dev/ttyAMA0',
  `port` char(50) COLLATE utf16_bin NOT NULL DEFAULT '115200' COMMENT 'port number 5003 or baud rate115200 for serial gateway',
  `timout` char(50) COLLATE utf16_bin NOT NULL DEFAULT '3',
  `pid` char(50) COLLATE utf16_bin DEFAULT NULL,
  `pid_running_since` char(50) COLLATE utf16_bin DEFAULT NULL,
  `reboot` tinyint(4) DEFAULT 0,
  `find_gw` tinyint(4) DEFAULT 0,
  `version` char(50) COLLATE utf16_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.gateway: ~0 rows (approximately)
/*!40000 ALTER TABLE `gateway` DISABLE KEYS */;
REPLACE INTO `gateway` (`id`, `status`, `sync`, `purge`, `type`, `location`, `port`, `timout`, `pid`, `pid_running_since`, `reboot`, `find_gw`, `version`) VALUES
	(1, 0, 0, 0, 'wifi', '192.168.99.3', '5003', '3', '3793', '', 0, 1, '2.3.1\n');
/*!40000 ALTER TABLE `gateway` ENABLE KEYS */;

-- Dumping structure for table pihome.gateway_logs
DROP TABLE IF EXISTS `gateway_logs`;
CREATE TABLE IF NOT EXISTS `gateway_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT 0,
  `purge` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Mark For Deletion',
  `type` char(50) COLLATE utf16_bin DEFAULT 'wifi' COMMENT 'serial or wifi',
  `location` char(50) COLLATE utf16_bin DEFAULT '192.168.99.3' COMMENT 'ip address or serial port location i.e. /dev/ttyAMA0',
  `port` char(50) COLLATE utf16_bin DEFAULT '5003' COMMENT 'port number or baud rate for serial gateway',
  `pid` char(50) COLLATE utf16_bin DEFAULT NULL,
  `pid_start_time` char(50) COLLATE utf16_bin DEFAULT NULL,
  `pid_datetime` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.gateway_logs: ~0 rows (approximately)
/*!40000 ALTER TABLE `gateway_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `gateway_logs` ENABLE KEYS */;

-- Dumping structure for table pihome.holidays
DROP TABLE IF EXISTS `holidays`;
CREATE TABLE IF NOT EXISTS `holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT 0,
  `purge` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT NULL,
  `start_date_time` datetime DEFAULT NULL,
  `end_date_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.holidays: ~0 rows (approximately)
/*!40000 ALTER TABLE `holidays` DISABLE KEYS */;
/*!40000 ALTER TABLE `holidays` ENABLE KEYS */;

-- Dumping structure for table pihome.messages_in
DROP TABLE IF EXISTS `messages_in`;
CREATE TABLE IF NOT EXISTS `messages_in` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT 0,
  `purge` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Mark For Deletion',
  `node_id` char(15) COLLATE utf16_bin DEFAULT NULL,
  `child_id` tinyint(4) DEFAULT NULL,
  `sub_type` int(11) DEFAULT NULL,
  `payload` decimal(10,2) DEFAULT NULL,
  `datetime` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.messages_in: ~1 rows (approximately)
/*!40000 ALTER TABLE `messages_in` DISABLE KEYS */;
REPLACE INTO `messages_in` (`id`, `sync`, `purge`, `node_id`, `child_id`, `sub_type`, `payload`, `datetime`) VALUES
	(2, 0, 0, '0', 0, NULL, 54.20, '2019-07-18 20:30:01'),
	(3, 0, 0, '0', 0, NULL, 53.70, '2019-07-18 20:35:02');
/*!40000 ALTER TABLE `messages_in` ENABLE KEYS */;

-- Dumping structure for table pihome.messages_out
DROP TABLE IF EXISTS `messages_out`;
CREATE TABLE IF NOT EXISTS `messages_out` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT 0,
  `purge` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Mark For Deletion',
  `node_id` char(50) COLLATE utf32_bin NOT NULL COMMENT 'Node ID',
  `child_id` int(11) NOT NULL COMMENT 'Child Sensor',
  `sub_type` int(11) NOT NULL COMMENT 'Command Type',
  `ack` int(11) NOT NULL COMMENT 'Ack Req/Resp',
  `type` int(11) NOT NULL COMMENT 'Type',
  `payload` varchar(100) CHARACTER SET utf8 NOT NULL COMMENT 'Payload',
  `sent` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Sent Status 0 No - 1 Yes',
  `datetime` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Current datetime',
  `zone_id` int(11) NOT NULL COMMENT 'Zone ID related to this entery',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf32 COLLATE=utf32_bin;

-- Dumping data for table pihome.messages_out: ~9 rows (approximately)
/*!40000 ALTER TABLE `messages_out` DISABLE KEYS */;
REPLACE INTO `messages_out` (`id`, `sync`, `purge`, `node_id`, `child_id`, `sub_type`, `ack`, `type`, `payload`, `sent`, `datetime`, `zone_id`) VALUES
	(15, 0, 0, '101', 1, 1, 1, 2, '0', 0, '2019-07-17 16:00:01', 33),
	(17, 0, 0, '101', 2, 1, 1, 2, '0', 0, '2019-07-17 16:00:01', 34),
	(19, 0, 0, '101', 3, 1, 1, 2, '0', 0, '2019-07-17 16:00:01', 35),
	(21, 0, 0, '40', 1, 1, 0, 2, '0', 1, '2019-07-12 21:21:02', 33),
	(22, 0, 0, '40', 2, 1, 0, 2, '0', 1, '2019-06-17 16:02:48', 34),
	(23, 0, 0, '40', 3, 1, 0, 2, '0', 1, '2019-06-17 16:02:49', 35),
	(24, 0, 0, '100', 1, 1, 1, 2, '0', 0, '2019-07-17 16:00:01', 0),
	(25, 0, 0, '40', 255, 3, 0, 1, '00:16', 1, '2019-03-20 00:16:38', 0),
	(26, 0, 0, '40', 1, 2, 0, 47, '18:08', 1, '2019-03-20 00:02:24', 0);
/*!40000 ALTER TABLE `messages_out` ENABLE KEYS */;

-- Dumping structure for table pihome.mqtt
DROP TABLE IF EXISTS `mqtt`;
CREATE TABLE `mqtt` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
  	`name` varchar(50) COLLATE utf16_bin NOT NULL DEFAULT '',
  	`ip` varchar(39) COLLATE utf16_bin NOT NULL DEFAULT '127.0.0.1',
  	`port` int(11) NOT NULL DEFAULT 1883,
  	`username` varchar(50) COLLATE utf16_bin NOT NULL DEFAULT '',
  	`password` varchar(50) COLLATE utf16_bin NOT NULL DEFAULT '',
  	`enabled` tinyint(4) NOT NULL DEFAULT 1,
	`type` INT(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
)
COLLATE='utf16_bin'
ENGINE=InnoDB;

-- Dumping data for table pihome.mqtt: ~0 rows (approximately)
/*!40000 ALTER TABLE `mqtt` DISABLE KEYS */;
REPLACE INTO `mqtt` (`id`, `name`, `ip`, `port`, `username`, `password`, `enabled`, `type`) VALUES
	(1, 'Demo', '127.0.0.1', 1883, 'mosquitto', 'mosquitto', 0, 0);
/*!40000 ALTER TABLE `mqtt` ENABLE KEYS */;

-- Dumping structure for table pihome.nodes
DROP TABLE IF EXISTS `nodes`;
CREATE TABLE IF NOT EXISTS `nodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT 0,
  `purge` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Mark For Deletion',
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
  `last_seen` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `notice_interval` int(11) NOT NULL DEFAULT 30,
  `min_voltage` decimal(10,2) DEFAULT NULL,
  `status` char(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT 'Active',
  `ms_version` char(50) COLLATE utf16_bin DEFAULT NULL,
  `sketch_version` char(50) COLLATE utf16_bin DEFAULT NULL,
  `repeater` tinyint(4) DEFAULT NULL COMMENT 'Repeater Feature Enabled=1 or Disable=0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.nodes: ~7 rows (approximately)
/*!40000 ALTER TABLE `nodes` DISABLE KEYS */;
REPLACE INTO `nodes` (`id`, `sync`, `purge`, `node_id`, `child_id_1`, `child_id_2`, `child_id_3`, `child_id_4`, `child_id_5`, `child_id_6`, `child_id_7`, `child_id_8`, `name`, `last_seen`, `notice_interval`, `min_voltage`, `status`, `ms_version`, `sketch_version`, `repeater`) VALUES
	(0, 1, 0, '0', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Zone Controller Relay', '2019-07-11 13:46:21', 0, NULL, NULL, NULL, NULL, NULL),
	(1, 1, 0, '21', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Temperature Sensor', '2019-07-17 15:01:08', 60, NULL, 'Active', '2.3.1\n', '0.31', NULL),
	(2, 1, 0, '20', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Temperature Sensor', '2019-07-17 14:47:08', 45, NULL, 'Active', '2.3.1\n', '0.31', NULL),
	(4, 1, 0, '30', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Temperature Sensor', '2019-07-17 14:48:06', 45, NULL, 'Active', '2.3.1\n', '0.31', NULL),
	(5, 1, 0, '100', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Boiler Relay', '2019-07-18 20:10:44', 45, NULL, 'Active', '2.3.1\n', '0.31', NULL),
	(7, 1, 0, '101', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Zone Controller Relay', '2019-07-18 20:10:47', 45, NULL, 'Active', '2.1.1\n', '1.23', NULL),
	(8, 1, 0, '40', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Button Console', '2019-07-12 00:29:15', 0, NULL, NULL, '2.1.1\n', '1.32', NULL),
	(9, 1, 0, '0', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Boiler Relay', '2019-07-11 13:46:21', 0, NULL, NULL, NULL, NULL, NULL);
/*!40000 ALTER TABLE `nodes` ENABLE KEYS */;

-- Dumping structure for table pihome.nodes_battery
DROP TABLE IF EXISTS `nodes_battery`;
CREATE TABLE IF NOT EXISTS `nodes_battery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT 0,
  `purge` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Mark For Deletion',
  `node_id` int(11) DEFAULT NULL,
  `bat_voltage` decimal(10,2) DEFAULT NULL,
  `bat_level` decimal(10,2) DEFAULT NULL,
  `update` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.nodes_battery: ~0 rows (approximately)
/*!40000 ALTER TABLE `nodes_battery` DISABLE KEYS */;
/*!40000 ALTER TABLE `nodes_battery` ENABLE KEYS */;

-- Dumping structure for table pihome.notice
DROP TABLE IF EXISTS `notice`;
CREATE TABLE IF NOT EXISTS `notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT 0,
  `purge` tinyint(4) NOT NULL DEFAULT 0,
  `datetime` timestamp NULL DEFAULT current_timestamp(),
  `message` varchar(200) COLLATE utf16_bin DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=181 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.notice: ~0 rows (approximately)
/*!40000 ALTER TABLE `notice` DISABLE KEYS */;
/*!40000 ALTER TABLE `notice` ENABLE KEYS */;

-- Dumping structure for table pihome.override
DROP TABLE IF EXISTS `override`;
CREATE TABLE IF NOT EXISTS `override` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT 0,
  `purge` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT 0,
  `zone_id` int(11) DEFAULT NULL,
  `time` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `temperature` tinyint(4) DEFAULT 22,
  PRIMARY KEY (`id`),
  KEY `FK_override_zone` (`zone_id`),
  CONSTRAINT `FK_override_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.override: ~3 rows (approximately)
/*!40000 ALTER TABLE `override` DISABLE KEYS */;
REPLACE INTO `override` (`id`, `sync`, `purge`, `status`, `zone_id`, `time`, `temperature`) VALUES
	(8, 1, 0, 0, 33, '2019-07-12 06:59:53', 24),
	(9, 1, 0, 0, 34, '2019-07-09 10:28:38', 24),
	(10, 1, 0, 0, 35, '2019-06-30 14:39:50', 35);
/*!40000 ALTER TABLE `override` ENABLE KEYS */;

-- Dumping structure for table pihome.piconnect
DROP TABLE IF EXISTS `piconnect`;
CREATE TABLE IF NOT EXISTS `piconnect` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` int(11) NOT NULL DEFAULT 0,
  `protocol` varchar(50) COLLATE utf16_bin DEFAULT NULL,
  `url` varchar(50) COLLATE utf16_bin DEFAULT NULL,
  `script` char(50) COLLATE utf16_bin DEFAULT NULL,
  `api_key` varchar(200) COLLATE utf16_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.piconnect: ~0 rows (approximately)
/*!40000 ALTER TABLE `piconnect` DISABLE KEYS */;
REPLACE INTO `piconnect` (`id`, `status`, `protocol`, `url`, `script`, `api_key`) VALUES
	(1, 0, 'http', 'www.pihome.eu', '/piconnect/mypihome.php', '');
/*!40000 ALTER TABLE `piconnect` ENABLE KEYS */;

-- Dumping structure for table pihome.schedule_daily_time
DROP TABLE IF EXISTS `schedule_daily_time`;
CREATE TABLE IF NOT EXISTS `schedule_daily_time` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT 0,
  `purge` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT NULL,
  `start` time DEFAULT NULL,
  `end` time DEFAULT NULL,
  `WeekDays` smallint(6) NOT NULL DEFAULT 127,
  `nickname` varchar(200) COLLATE utf16_bin DEFAULT NULL
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.schedule_daily_time: ~0 rows (approximately)
/*!40000 ALTER TABLE `schedule_daily_time` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedule_daily_time` ENABLE KEYS */;

-- Dumping structure for table pihome.schedule_daily_time_zone
DROP TABLE IF EXISTS `schedule_daily_time_zone`;
CREATE TABLE IF NOT EXISTS `schedule_daily_time_zone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT 0,
  `purge` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT NULL,
  `schedule_daily_time_id` int(11) DEFAULT NULL,
  `zone_id` int(11) DEFAULT NULL,
  `temperature` float NOT NULL DEFAULT 0,
  `holidays_id` int(11) DEFAULT NULL,
  `coop` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `FK_schedule_daily_time_zone_schedule_daily_time` (`schedule_daily_time_id`),
  KEY `FK_schedule_daily_time_zone_zone` (`zone_id`),
  CONSTRAINT `FK_schedule_daily_time_zone_schedule_daily_time` FOREIGN KEY (`schedule_daily_time_id`) REFERENCES `schedule_daily_time` (`id`),
  CONSTRAINT `FK_schedule_daily_time_zone_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.schedule_daily_time_zone: ~0 rows (approximately)
/*!40000 ALTER TABLE `schedule_daily_time_zone` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedule_daily_time_zone` ENABLE KEYS */;

-- Dumping structure for table pihome.schedule_night_climate_time
DROP TABLE IF EXISTS `schedule_night_climate_time`;
CREATE TABLE IF NOT EXISTS `schedule_night_climate_time` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT 0,
  `purge` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT NULL,
  `start_time` time DEFAULT '21:00:00',
  `end_time` time DEFAULT '06:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.schedule_night_climate_time: ~0 rows (approximately)
/*!40000 ALTER TABLE `schedule_night_climate_time` DISABLE KEYS */;
REPLACE INTO `schedule_night_climate_time` (`id`, `sync`, `purge`, `status`, `start_time`, `end_time`) VALUES
	(1, 1, 0, 0, '22:00:00', '05:30:00');
/*!40000 ALTER TABLE `schedule_night_climate_time` ENABLE KEYS */;

-- Dumping structure for table pihome.schedule_night_climat_zone
DROP TABLE IF EXISTS `schedule_night_climat_zone`;
CREATE TABLE IF NOT EXISTS `schedule_night_climat_zone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT 0,
  `purge` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT NULL,
  `zone_id` int(11) DEFAULT NULL,
  `schedule_night_climate_id` int(11) DEFAULT NULL,
  `min_temperature` float NOT NULL DEFAULT 18,
  `max_temperature` float NOT NULL DEFAULT 21,
  PRIMARY KEY (`id`),
  KEY `FK_schedule_zone_night_climat_zone` (`zone_id`),
  KEY `FK_schedule_zone_night_climat_schedule_night_climate` (`schedule_night_climate_id`),
  CONSTRAINT `FK_schedule_zone_night_climat_schedule_night_climate` FOREIGN KEY (`schedule_night_climate_id`) REFERENCES `schedule_night_climate_time` (`id`),
  CONSTRAINT `FK_schedule_zone_night_climat_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.schedule_night_climat_zone: ~3 rows (approximately)
/*!40000 ALTER TABLE `schedule_night_climat_zone` DISABLE KEYS */;
REPLACE INTO `schedule_night_climat_zone` (`id`, `sync`, `purge`, `status`, `zone_id`, `schedule_night_climate_id`, `min_temperature`, `max_temperature`) VALUES
	(8, 1, 0, 0, 33, 1, 18, 20),
	(9, 1, 0, 1, 34, 1, 18, 20),
	(10, 1, 0, 1, 35, 1, 19, 21);
/*!40000 ALTER TABLE `schedule_night_climat_zone` ENABLE KEYS */;

-- Dumping structure for table pihome.system
DROP TABLE IF EXISTS `system`;
CREATE TABLE IF NOT EXISTS `system` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT 0,
  `purge` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Mark For Deletion',
  `name` varchar(50) COLLATE utf16_bin DEFAULT NULL,
  `version` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `build` varchar(50) COLLATE utf16_bin DEFAULT NULL,
  `update_location` char(250) CHARACTER SET latin1 DEFAULT NULL,
  `update_file` char(100) CHARACTER SET latin1 DEFAULT NULL,
  `update_alias` char(100) CHARACTER SET latin1 DEFAULT NULL,
  `country` char(2) CHARACTER SET latin1 DEFAULT NULL,
  `language` char(10) COLLATE utf16_bin DEFAULT 'en',
  `city` char(100) CHARACTER SET latin1 DEFAULT NULL,
  `zip` char(100) COLLATE utf16_bin DEFAULT NULL,
  `openweather_api` char(100) CHARACTER SET latin1 DEFAULT NULL,
  `backup_email` char(100) COLLATE utf16_bin DEFAULT NULL,
  `ping_home` bit(1) DEFAULT b'1',
  `timezone` varchar(50) COLLATE utf16_bin DEFAULT 'Europe/Dublin',
  `shutdown` tinyint(4) DEFAULT 0,
  `reboot` tinyint(4) DEFAULT 0,
  `c_f` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=C, 1=F',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.system: ~0 rows (approximately)
/*!40000 ALTER TABLE `system` DISABLE KEYS */;
REPLACE INTO `system` (`id`, `sync`, `purge`, `name`, `version`, `build`, `update_location`, `update_file`, `update_alias`, `country`, `language`, `city`, `zip`, `openweather_api`, `backup_email`, `ping_home`, `timezone`, `shutdown`, `reboot`, `c_f`) VALUES
	(2, 1, 0, 'PiHome - Smart Heating Control', '1.67', '210919', 'http://www.pihome.eu/updates/', 'current-release-versions.php', 'pihome', 'IE', 'en', 'Portlaoise', NULL, '', '', b'1', 'Europe/Dublin', 0, 0, 0);
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
  `cpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `account_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `backup` tinyint(4) DEFAULT NULL,
  `users` tinyint(4) DEFAULT NULL,
  `support` tinyint(4) DEFAULT NULL,
  `settings` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- Dumping data for table pihome.user: ~0 rows (approximately)
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
REPLACE INTO `user` (`id`, `account_enable`, `fullname`, `username`, `email`, `password`, `cpdate`, `account_date`, `backup`, `users`, `support`, `settings`) VALUES
	(1, 1, 'Administrator', 'admin', '', '0f5f9ba0136d5a8588b3fc70ec752869', '2019-04-07 19:01:14', '2017-06-13 16:10:31', 1, 1, 1, 1);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;

-- Dumping structure for table pihome.userhistory
DROP TABLE IF EXISTS `userhistory`;
CREATE TABLE IF NOT EXISTS `userhistory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `audit` tinytext DEFAULT NULL,
  `ipaddress` tinytext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=144 DEFAULT CHARSET=latin1;

-- Dumping data for table pihome.userhistory: ~139 rows (approximately)
/*!40000 ALTER TABLE `userhistory` DISABLE KEYS */;
REPLACE INTO `userhistory` (`id`, `username`, `password`, `date`, `audit`, `ipaddress`) VALUES
	(1, 'admin', '0f5f9ba0136d5a8588b3fc70ec752869', '2019-07-18 20:05:40', 'Failed', '192.168.99.4');
/*!40000 ALTER TABLE `userhistory` ENABLE KEYS */;

-- Dumping structure for table pihome.weather
DROP TABLE IF EXISTS `weather`;
CREATE TABLE IF NOT EXISTS `weather` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT 0,
  `location` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `c` tinyint(4) DEFAULT NULL,
  `wind_speed` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `title` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `description` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `sunrise` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `sunset` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `img` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Last weather update',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Dumping data for table pihome.weather: ~0 rows (approximately)
/*!40000 ALTER TABLE `weather` DISABLE KEYS */;
REPLACE INTO `weather` (`id`, `sync`, `location`, `c`, `wind_speed`, `title`, `description`, `sunrise`, `sunset`, `img`, `last_update`) VALUES
	(1, 0, 'Portlaoise', 18, '7', 'Clouds', 'scattered clouds', '1563423906', '1563482717', '03d', '2019-07-18 19:30:02');
/*!40000 ALTER TABLE `weather` ENABLE KEYS */;

-- Dumping structure for table pihome.zone
DROP TABLE IF EXISTS `zone`;
CREATE TABLE IF NOT EXISTS `zone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT 0,
  `purge` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT NULL,
  `index_id` tinyint(4) DEFAULT NULL,
  `name` char(50) COLLATE utf8_bin DEFAULT NULL,
  `type` char(50) COLLATE utf8_bin DEFAULT NULL,
  `model` char(50) COLLATE utf8_bin DEFAULT NULL,
  `graph_it` tinyint(1) NOT NULL DEFAULT 1,
  `max_c` tinyint(4) DEFAULT NULL,
  `max_operation_time` tinyint(4) DEFAULT NULL,
  `hysteresis_time` tinyint(4) DEFAULT NULL,
  `sp_deadband` float NOT NULL DEFAULT 0.5,
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
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Dumping data for table pihome.zone: ~3 rows (approximately)
/*!40000 ALTER TABLE `zone` DISABLE KEYS */;
REPLACE INTO `zone` (`id`, `sync`, `purge`, `status`, `index_id`, `name`, `type`, `model`, `max_c`, `max_operation_time`, `hysteresis_time`, `sp_deadband`, `sensor_id`, `sensor_child_id`, `controler_id`, `controler_child_id`, `boiler_id`, `gpio_pin`) VALUES
	(33, 1, 0, 1, 1, 'Ground Floor', 'Heating', 'DE000F', 25, 60, 3, 0.5, 1, 0, 7, 1, 1, 21),
	(34, 1, 0, 1, 2, 'First Floor', 'Heating', '7D0096', 25, 60, 3, 0.5, 2, 0, 7, 2, 1, 22),
	(35, 1, 0, 1, 5, 'Ch. Hot Water', 'Water', '009604', 70, 90, 3, 0.5, 4, 0, 7, 3, 1, 23);
/*!40000 ALTER TABLE `zone` ENABLE KEYS */;

-- Dumping structure for table pihome.zone_logs
DROP TABLE IF EXISTS `zone_logs`;
CREATE TABLE IF NOT EXISTS `zone_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT 0,
  `purge` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Mark For Deletion',
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
