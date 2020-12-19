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

-- Dumping structure for table pihome.node_id
DROP TABLE IF EXISTS `node_id`;
CREATE TABLE IF NOT EXISTS `node_id` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) DEFAULT NULL,
  `purge` tinyint(4) DEFAULT NULL,
  `node_id` int(11) DEFAULT NULL,
  `sent` tinyint(4) DEFAULT NULL,
  `date_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4;

-- Dumping structure for table pihome.add_on_logs
DROP TABLE IF EXISTS `add_on_logs`;
CREATE TABLE IF NOT EXISTS `add_on_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `start_datetime` timestamp NULL,
  `start_cause` char(50) COLLATE utf16_bin,
  `stop_datetime` timestamp NULL,
  `stop_cause` char(50) COLLATE utf16_bin,
  `expected_end_date_time` timestamp NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping structure for table pihome.add_on_zone_logs
DROP TABLE IF EXISTS `add_on_zone_logs`;
CREATE TABLE IF NOT EXISTS `add_on_zone_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `zone_id` int(11),
  `add_on_log_id` int(11),
  `status` int(11),
  PRIMARY KEY (`id`),
  KEY `FK_add_on_zone_logs_zone` (`zone_id`),
  KEY `FK_add_on_zone_logs_add_on_logs` (`add_on_log_id`),
  CONSTRAINT `FK_add_on_zone_logs_add_on_logs` FOREIGN KEY (`add_on_log_id`) REFERENCES `add_on_logs` (`id`),
  CONSTRAINT `FK_add_on_zone_logs_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.zone_logs: ~0 rows (approximately)
/*!40000 ALTER TABLE `add_on_zone_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `add_on_zone_logs` ENABLE KEYS */;

-- Dumping structure for table pihome.away
DROP TABLE IF EXISTS `away`;
CREATE TABLE IF NOT EXISTS `away` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4),
  `start_datetime` timestamp NULL ON UPDATE current_timestamp(),
  `end_datetime` timestamp NULL,
  `away_button_id` int(11),
  `away_button_child_id` int(11),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.away: 1 rows
/*!40000 ALTER TABLE `away` DISABLE KEYS */;
/*!40000 ALTER TABLE `away` ENABLE KEYS */;

-- Dumping structure for table pihome.boiler
DROP TABLE IF EXISTS `boiler`;
CREATE TABLE IF NOT EXISTS `boiler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4),
  `fired_status` tinyint(4),
  `name` char(50) CHARACTER SET utf16 COLLATE utf16_bin,
  `node_id` int(11),
  `node_child_id` int(11),
  `hysteresis_time` tinyint(4),
  `max_operation_time` SMALLINT(4),
  `overrun` SMALLINT(6) NULL DEFAULT NULL,
  `datetime` timestamp NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK_boiler_zone` (`node_id`),
  CONSTRAINT `FK_boiler_zone` FOREIGN KEY (`node_id`) REFERENCES `nodes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- Dumping data for table pihome.boiler: ~0 rows (approximately)
/*!40000 ALTER TABLE `boiler` DISABLE KEYS */;
/*!40000 ALTER TABLE `boiler` ENABLE KEYS */;

-- Dumping structure for table pihome.boiler_logs
DROP TABLE IF EXISTS `boiler_logs`;
CREATE TABLE IF NOT EXISTS `boiler_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `start_datetime` timestamp NULL,
  `start_cause` char(50) COLLATE utf16_bin,
  `stop_datetime` timestamp NULL,
  `stop_cause` char(50) COLLATE utf16_bin,
  `expected_end_date_time` timestamp NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.boiler_logs: ~0 rows (approximately)
/*!40000 ALTER TABLE `boiler_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `boiler_logs` ENABLE KEYS */;

-- Dumping structure for table pihome.boost
DROP TABLE IF EXISTS `boost`;
CREATE TABLE IF NOT EXISTS `boost` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4),
  `zone_id` int(11),
  `time` timestamp NOT NULL ON UPDATE current_timestamp(),
  `temperature` tinyint(4),
  `minute` tinyint(4),
  `boost_button_id` int(11),
  `boost_button_child_id` int(11),
  PRIMARY KEY (`id`),
  KEY `FK_boost_zone` (`zone_id`),
  CONSTRAINT `FK_boost_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.boost: ~6 rows (approximately)
/*!40000 ALTER TABLE `boost` DISABLE KEYS */;
/*!40000 ALTER TABLE `boost` ENABLE KEYS */;

-- Dumping structure for table pihome.crontab
DROP TABLE IF EXISTS `crontab`;
CREATE TABLE IF NOT EXISTS `crontab` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` char(50),
  `min` char(50),
  `hour` char(50),
  `day` char(50),
  `month` char(50),
  `weekday` char(50),
  `command` char(50),
  `output` char(50),
  `comments` varchar(50),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='PiHome Smart Heating - Manage Crontab from web interface ';

-- Dumping data for table pihome.crontab: ~0 rows (approximately)
/*!40000 ALTER TABLE `crontab` DISABLE KEYS */;
/*!40000 ALTER TABLE `crontab` ENABLE KEYS */;

-- Dumping structure for table pihome.email
DROP TABLE IF EXISTS `email`;
CREATE TABLE IF NOT EXISTS `email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `smtp` char(50) COLLATE utf16_bin,
  `username` char(50) COLLATE utf16_bin,
  `password` char(50) COLLATE utf16_bin,
  `from` char(50) COLLATE utf16_bin,
  `to` char(50) COLLATE utf16_bin,
  `status` tinyint(4),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.email: 0 rows
/*!40000 ALTER TABLE `email` DISABLE KEYS */;
/*!40000 ALTER TABLE `email` ENABLE KEYS */;

-- Dumping structure for table pihome.frost_protection
DROP TABLE IF EXISTS `frost_protection`;
CREATE TABLE IF NOT EXISTS `frost_protection` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `datetime` timestamp NULL,
  `temperature` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- Dumping data for table pihome.frost_protection: ~0 rows (approximately)
/*!40000 ALTER TABLE `frost_protection` DISABLE KEYS */;
/*!40000 ALTER TABLE `frost_protection` ENABLE KEYS */;

-- Dumping structure for table pihome.gateway
DROP TABLE IF EXISTS `gateway`;
CREATE TABLE IF NOT EXISTS `gateway` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(4) NOT NULL,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `type` char(50) COLLATE utf16_bin NOT NULL COMMENT 'serial or wifi',
  `location` char(50) COLLATE utf16_bin NOT NULL COMMENT 'ip address or serial port location i.e. /dev/ttyAMA0',
  `port` char(50) COLLATE utf16_bin NOT NULL COMMENT 'port number 5003 or baud rate115200 for serial gateway',
  `timout` char(50) COLLATE utf16_bin NOT NULL,
  `pid` char(50) COLLATE utf16_bin,
  `pid_running_since` char(50) COLLATE utf16_bin,
  `reboot` tinyint(4),
  `find_gw` tinyint(4),
  `version` char(50) COLLATE utf16_bin,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.gateway: ~0 rows (approximately)
/*!40000 ALTER TABLE `gateway` DISABLE KEYS */;
/*!40000 ALTER TABLE `gateway` ENABLE KEYS */;

-- Dumping structure for table pihome.gateway_logs
DROP TABLE IF EXISTS `gateway_logs`;
CREATE TABLE IF NOT EXISTS `gateway_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `type` char(50) COLLATE utf16_bin COMMENT 'serial or wifi',
  `location` char(50) COLLATE utf16_bin COMMENT 'ip address or serial port location i.e. /dev/ttyAMA0',
  `port` char(50) COLLATE utf16_bin COMMENT 'port number or baud rate for serial gateway',
  `pid` char(50) COLLATE utf16_bin,
  `pid_start_time` char(50) COLLATE utf16_bin,
  `pid_datetime` timestamp NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.gateway_logs: ~0 rows (approximately)
/*!40000 ALTER TABLE `gateway_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `gateway_logs` ENABLE KEYS */;

-- Dumping structure for table pihome.holidays
DROP TABLE IF EXISTS `holidays`;
CREATE TABLE IF NOT EXISTS `holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4),
  `start_date_time` datetime,
  `end_date_time` datetime,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.holidays: ~0 rows (approximately)
/*!40000 ALTER TABLE `holidays` DISABLE KEYS */;
/*!40000 ALTER TABLE `holidays` ENABLE KEYS */;

-- Dumping structure for table pihome.http_messages
DROP TABLE IF EXISTS `http_messages`;
CREATE TABLE IF NOT EXISTS `http_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `zone_name` char(50) COLLATE utf16_bin,
  `node_id` char(50) COLLATE utf16_bin,
  `message_type` char(50) COLLATE utf16_bin,
  `command` char(50) COLLATE utf16_bin,
  `parameter` char(50) COLLATE utf16_bin,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping structure for table pihome.messages_in
DROP TABLE IF EXISTS `messages_in`;
CREATE TABLE IF NOT EXISTS `messages_in` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `node_id` char(15) COLLATE utf16_bin,
  `child_id` tinyint(4),
  `sub_type` int(11),
  `payload` decimal(10,2),
  `datetime` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.messages_in: ~1 rows (approximately)
/*!40000 ALTER TABLE `messages_in` DISABLE KEYS */;
/*!40000 ALTER TABLE `messages_in` ENABLE KEYS */;

-- Dumping structure for table pihome.messages_out
DROP TABLE IF EXISTS `messages_out`;
CREATE TABLE IF NOT EXISTS `messages_out` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `node_id` char(50) COLLATE utf32_bin NOT NULL COMMENT 'Node ID',
  `child_id` int(11) NOT NULL COMMENT 'Child Sensor',
  `sub_type` int(11) NOT NULL COMMENT 'Command Type',
  `ack` int(11) NOT NULL COMMENT 'Ack Req/Resp',
  `type` int(11) NOT NULL COMMENT 'Type',
  `payload` varchar(100) CHARACTER SET utf8 NOT NULL COMMENT 'Payload',
  `sent` tinyint(1) NOT NULL COMMENT 'Sent Status 0 No - 1 Yes',
  `datetime` timestamp NOT NULL ON UPDATE current_timestamp() COMMENT 'Current datetime',
  `zone_id` int(11) NOT NULL COMMENT 'Zone ID related to this entery',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf32 COLLATE=utf32_bin;

-- Dumping data for table pihome.messages_out: ~9 rows (approximately)
/*!40000 ALTER TABLE `messages_out` DISABLE KEYS */;
/*!40000 ALTER TABLE `messages_out` ENABLE KEYS */;

-- Dumping structure for table pihome.mqtt
DROP TABLE IF EXISTS `mqtt`;
CREATE TABLE `mqtt` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
  	`name` varchar(50) COLLATE utf16_bin NOT NULL,
  	`ip` varchar(39) COLLATE utf16_bin NOT NULL,
  	`port` int(11) NOT NULL,
  	`username` varchar(50) COLLATE utf16_bin NOT NULL,
  	`password` varchar(50) COLLATE utf16_bin NOT NULL,
  	`enabled` tinyint(4) NOT NULL,
	`type` INT(11) NOT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf16_bin'
ENGINE=InnoDB;

-- Dumping data for table pihome.mqtt: ~0 rows (approximately)
/*!40000 ALTER TABLE `mqtt` DISABLE KEYS */;
/*!40000 ALTER TABLE `mqtt` ENABLE KEYS */;

-- Dumping structure for table pihome.network_settings
DROP TABLE IF EXISTS `network_settings`;
CREATE TABLE IF NOT EXISTS `network_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `primary_interface` tinyint(4),
  `ap_mode` tinyint(1),
  `interface_num` tinyint(4),
  `interface_type` char(50) COLLATE utf16_bin,
  `mac_address` char(50) COLLATE utf16_bin,
  `hostname` char(50) COLLATE utf16_bin,
  `ip_address` char(50) COLLATE utf16_bin,
  `gateway_address` char(50) COLLATE utf16_bin,
  `net_mask` char(50) COLLATE utf16_bin,
  `dns1_address` char(50) COLLATE utf16_bin,
  `dns2_address` char(50) COLLATE utf16_bin,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.network_settings: ~0 rows (approximately)
/*!40000 ALTER TABLE `network_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `network_settings` ENABLE KEYS */;

-- Dumping structure for table pihome.nodes
DROP TABLE IF EXISTS `nodes`;
CREATE TABLE IF NOT EXISTS `nodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `type` CHAR(50) NOT NULL COLLATE 'utf8_bin',
  `node_id` char(50) COLLATE utf16_bin NOT NULL,
  `max_child_id` int(11) NOT NULL,
  `name` char(50) CHARACTER SET utf8 COLLATE utf8_bin,
  `last_seen` timestamp NULL ON UPDATE current_timestamp(),
  `notice_interval` int(11) NOT NULL,
  `min_value` int(11),
  `status` char(50) CHARACTER SET utf8 COLLATE utf8_bin,
  `ms_version` char(50) COLLATE utf16_bin,
  `sketch_version` char(50) COLLATE utf16_bin,
  `repeater` tinyint(4) COMMENT 'Repeater Feature Enabled=1 or Disable=0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.nodes: ~7 rows (approximately)
/*!40000 ALTER TABLE `nodes` DISABLE KEYS */;
/*!40000 ALTER TABLE `nodes` ENABLE KEYS */;

-- Dumping structure for table pihome.nodes_battery
DROP TABLE IF EXISTS `nodes_battery`;
CREATE TABLE IF NOT EXISTS `nodes_battery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `node_id` int(11),
  `bat_voltage` decimal(10,2),
  `bat_level` decimal(10,2),
  `update` timestamp NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.nodes_battery: ~0 rows (approximately)
/*!40000 ALTER TABLE `nodes_battery` DISABLE KEYS */;
/*!40000 ALTER TABLE `nodes_battery` ENABLE KEYS */;

-- Dumping structure for table pihome.notice
DROP TABLE IF EXISTS `notice`;
CREATE TABLE IF NOT EXISTS `notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL,
  `datetime` timestamp NULL,
  `message` varchar(200) COLLATE utf16_bin,
  `status` tinyint(4),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=181 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.notice: ~0 rows (approximately)
/*!40000 ALTER TABLE `notice` DISABLE KEYS */;
/*!40000 ALTER TABLE `notice` ENABLE KEYS */;

-- Dumping structure for table pihome.override
DROP TABLE IF EXISTS `override`;
CREATE TABLE IF NOT EXISTS `override` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4),
  `zone_id` int(11),
  `time` timestamp NULL ON UPDATE current_timestamp(),
  `temperature` tinyint(4),
  PRIMARY KEY (`id`),
  KEY `FK_override_zone` (`zone_id`),
  CONSTRAINT `FK_override_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.override: ~3 rows (approximately)
/*!40000 ALTER TABLE `override` DISABLE KEYS */;
/*!40000 ALTER TABLE `override` ENABLE KEYS */;

-- Dumping structure for table pihome.piconnect
DROP TABLE IF EXISTS `piconnect`;
CREATE TABLE `piconnect` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `sync` tinyint(4) NOT NULL DEFAULT 0,
  `protocol` varchar(50) COLLATE utf16_bin DEFAULT NULL,
  `url` varchar(50) COLLATE utf16_bin DEFAULT NULL,
  `script` char(50) COLLATE utf16_bin DEFAULT NULL,
  `api_key` varchar(200) COLLATE utf16_bin DEFAULT NULL,
  `version` char(50) COLLATE utf16_bin DEFAULT NULL,
  `build` char(50) COLLATE utf16_bin DEFAULT NULL,
  `connect_datetime` datetime DEFAULT NULL,
  `delay` int(11) DEFAULT NULL,
  `away` bit(1) DEFAULT NULL,
  `boiler` bit(1) DEFAULT NULL,
  `boiler_logs` bit(1) DEFAULT NULL,
  `boost` bit(1) DEFAULT NULL,
  `email` bit(1) DEFAULT NULL,
  `frost_protection` bit(1) DEFAULT NULL,
  `gateway` bit(1) DEFAULT NULL,
  `gateway_log` bit(1) DEFAULT NULL,
  `holidays` bit(1) DEFAULT NULL,
  `messages_in` bit(1) DEFAULT NULL,
  `messages_out` bit(1) DEFAULT NULL,
  `mqtt` bit(1) DEFAULT NULL,
  `nodes` bit(1) DEFAULT NULL,
  `nodes_battery` bit(1) DEFAULT NULL,
  `notice` bit(1) DEFAULT NULL,
  `override` bit(1) DEFAULT NULL,
  `piconnect_logs` bit(1) DEFAULT NULL,
  `schedule` bit(1) DEFAULT NULL,
  `system` bit(1) DEFAULT NULL,
  `weather` bit(1) DEFAULT NULL,
  `zone` bit(1) DEFAULT NULL,
  `zone_logs` bit(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

-- Dumping data for table pihome.piconnect: ~0 rows (approximately)
/*!40000 ALTER TABLE `piconnect` DISABLE KEYS */;
/*!40000 ALTER TABLE `piconnect` ENABLE KEYS */;

-- Dumping structure for table pihome.piconnect_logs
DROP TABLE IF EXISTS `piconnect_logs`;
CREATE TABLE IF NOT EXISTS `piconnect_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` int(11) DEFAULT NULL,
  `picurl` char(200) CHARACTER SET utf8mb4 DEFAULT NULL,
  `content_type` char(200) CHARACTER SET utf8mb4 DEFAULT NULL,
  `http_code` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `header_size` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `request_size` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `filetime` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `ssl_verify_result` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `redirect_count` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `total_time` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `connect_time` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `pretransfer_time` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `size_upload` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `size_download` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `speed_download` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `speed_upload` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `download_content_length` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `upload_content_length` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `starttransfer_time` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `primary_port` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `local_port` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `start_time` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `end_time` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `n_tables` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  `records` char(50) CHARACTER SET utf8mb4 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


-- Dumping structure for table pihome.schedule_daily_time
DROP TABLE IF EXISTS `schedule_daily_time`;
CREATE TABLE IF NOT EXISTS `schedule_daily_time` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4),
  `start` time,
  `end` time,
  `WeekDays` smallint(6) NOT NULL,
  `sch_name` varchar(200) COLLATE utf16_bin,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.schedule_daily_time: ~0 rows (approximately)
/*!40000 ALTER TABLE `schedule_daily_time` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedule_daily_time` ENABLE KEYS */;

-- Dumping structure for table pihome.schedule_daily_time_zone
DROP TABLE IF EXISTS `schedule_daily_time_zone`;
CREATE TABLE IF NOT EXISTS `schedule_daily_time_zone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4),
  `schedule_daily_time_id` int(11),
  `zone_id` int(11),
  `temperature` float NOT NULL,
  `holidays_id` int(11),
  `coop` tinyint(4) NOT NULL,
  `sunset` tinyint(1),
  `sunset_offset` int(11),
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
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4),
  `start_time` time,
  `end_time` time,
  `WeekDays` smallint(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.schedule_night_climate_time: ~0 rows (approximately)
/*!40000 ALTER TABLE `schedule_night_climate_time` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedule_night_climate_time` ENABLE KEYS */;

-- Dumping structure for table pihome.schedule_night_climat_zone
DROP TABLE IF EXISTS `schedule_night_climat_zone`;
CREATE TABLE IF NOT EXISTS `schedule_night_climat_zone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4),
  `zone_id` int(11),
  `schedule_night_climate_id` int(11),
  `min_temperature` float NOT NULL,
  `max_temperature` float NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_schedule_zone_night_climat_zone` (`zone_id`),
  KEY `FK_schedule_zone_night_climat_schedule_night_climate` (`schedule_night_climate_id`),
  CONSTRAINT `FK_schedule_zone_night_climat_schedule_night_climate` FOREIGN KEY (`schedule_night_climate_id`) REFERENCES `schedule_night_climate_time` (`id`),
  CONSTRAINT `FK_schedule_zone_night_climat_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.schedule_night_climat_zone: ~3 rows (approximately)
/*!40000 ALTER TABLE `schedule_night_climat_zone` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedule_night_climat_zone` ENABLE KEYS */;

-- Dumping structure for table pihome.system
DROP TABLE IF EXISTS `system`;
CREATE TABLE IF NOT EXISTS `system` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `name` varchar(50) COLLATE utf16_bin,
  `version` varchar(50) CHARACTER SET latin1,
  `build` varchar(50) COLLATE utf16_bin,
  `update_location` char(250) CHARACTER SET latin1,
  `update_file` char(100) CHARACTER SET latin1,
  `update_alias` char(100) CHARACTER SET latin1,
  `country` char(2) CHARACTER SET latin1,
  `language` char(10) COLLATE utf16_bin,
  `city` char(100) CHARACTER SET latin1,
  `zip` char(100) COLLATE utf16_bin,
  `openweather_api` char(100) CHARACTER SET latin1,
  `backup_email` char(100) COLLATE utf16_bin,
  `ping_home` bit(1),
  `timezone` varchar(50) COLLATE utf16_bin,
  `shutdown` tinyint(4),
  `reboot` tinyint(4),
  `c_f` tinyint(4) NOT NULL COMMENT '0=C, 1=F',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.system: ~0 rows (approximately)
/*!40000 ALTER TABLE `system` DISABLE KEYS */;
/*!40000 ALTER TABLE `system` ENABLE KEYS */;

-- Dumping structure for table pihome.user
DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_enable` tinyint(1),
  `fullname` varchar(100) NOT NULL,
  `username` varchar(25) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `cpdate` timestamp NOT NULL ON UPDATE current_timestamp(),
  `account_date` timestamp NOT NULL,
  `backup` tinyint(4),
  `users` tinyint(4),
  `support` tinyint(4),
  `settings` tinyint(4),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- Dumping data for table pihome.user: ~0 rows (approximately)
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
/*!40000 ALTER TABLE `user` ENABLE KEYS */;

-- Dumping structure for table pihome.userhistory
DROP TABLE IF EXISTS `userhistory`;
CREATE TABLE IF NOT EXISTS `userhistory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50),
  `password` varchar(50),
  `date` datetime,
  `audit` tinytext,
  `ipaddress` tinytext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=144 DEFAULT CHARSET=latin1;

-- Dumping data for table pihome.userhistory: ~139 rows (approximately)
/*!40000 ALTER TABLE `userhistory` DISABLE KEYS */;
/*!40000 ALTER TABLE `userhistory` ENABLE KEYS */;

-- Dumping structure for table pihome.weather
DROP TABLE IF EXISTS `weather`;
CREATE TABLE IF NOT EXISTS `weather` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `location` varchar(50) COLLATE utf8_bin,
  `c` tinyint(4),
  `wind_speed` varchar(50) COLLATE utf8_bin,
  `title` varchar(50) COLLATE utf8_bin,
  `description` varchar(50) COLLATE utf8_bin,
  `sunrise` varchar(50) COLLATE utf8_bin,
  `sunset` varchar(50) COLLATE utf8_bin,
  `img` varchar(50) COLLATE utf8_bin,
  `last_update` timestamp NOT NULL ON UPDATE current_timestamp() COMMENT 'Last weather update',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Dumping data for table pihome.weather: ~0 rows (approximately)
/*!40000 ALTER TABLE `weather` DISABLE KEYS */;
/*!40000 ALTER TABLE `weather` ENABLE KEYS */;

-- Dumping structure for table pihome.zone
DROP TABLE IF EXISTS `zone`;
CREATE TABLE IF NOT EXISTS `zone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4),
  `zone_state` tinyint(4),
  `index_id` tinyint(4),
  `name` char(50) COLLATE utf8_bin,
  `type_id` int(11),
  `graph_it` tinyint(1) NOT NULL,
  `max_operation_time` SMALLINT(4),
  PRIMARY KEY (`id`),
  KEY `FK_zone_type_id` (`type_id`),
  CONSTRAINT `FK_zone_type_id` FOREIGN KEY (`type_id`) REFERENCES `zone_type` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Dumping data for table pihome.zone: ~3 rows (approximately)
/*!40000 ALTER TABLE `zone` DISABLE KEYS */;
/*!40000 ALTER TABLE `zone` ENABLE KEYS */;

-- Dumping structure for table pihome.zone_controllers
DROP TABLE IF EXISTS `zone_controllers`;
CREATE TABLE IF NOT EXISTS `zone_controllers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `state` tinyint(4),
  `current_state` tinyint(4),
  `zone_id` int(11),
  `controler_id` int(11),
  `controler_child_id` int(11),
  PRIMARY KEY (`id`),
  KEY `FK_zone_controllers_nodes` (`controler_id`),
  KEY `FK_zone_controllers_zone` (`zone_id`),
  CONSTRAINT `FK_zone_controllers_nodes` FOREIGN KEY (`controler_id`) REFERENCES `nodes` (`id`),
  CONSTRAINT `FK_zone_controllers_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Dumping structure for table pihome.zone_current_state
DROP TABLE IF EXISTS `zone_current_state`;
CREATE TABLE IF NOT EXISTS `zone_current_state` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL,
  `zone_id` int(11) NOT NULL,
  `mode` int(11),
  `status` tinyint(1),
  `temp_reading` decimal(4,1),
  `temp_target` decimal(4,1),
  `temp_cut_in` decimal(4,1),
  `temp_cut_out` decimal(4,1),
  `controler_fault` int(1),
  `controler_seen_time` timestamp NULL,
  `sensor_fault` int(1),
  `sensor_seen_time` timestamp NULL,
  `sensor_reading_time` timestamp NULL,
  `overrun` tinyint(1),
 PRIMARY KEY (`id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- Dumping data for table pihome.zone_current_state: 8 rows
/*!40000 ALTER TABLE `zone_current_state` DISABLE KEYS */;
/*!40000 ALTER TABLE `zone_current_state` ENABLE KEYS */;

-- Dumping structure for table pihome.zone_graphs
DROP TABLE IF EXISTS `zone_graphs`;
CREATE TABLE IF NOT EXISTS `zone_graphs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `zone_id` int(11),
  `name` char(50) COLLATE utf8_bin,
  `type` char(50) COLLATE utf8_bin,
  `category` int(11),
  `node_id` char(15) COLLATE utf16_bin,
  `child_id` tinyint(4),
  `sub_type` int(11),
  `payload` decimal(10,2),
  `datetime` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping structure for table pihome.zone_logs
DROP TABLE IF EXISTS `zone_logs`;
CREATE TABLE IF NOT EXISTS `zone_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `zone_id` int(11),
  `boiler_log_id` int(11),
  `status` int(11),
  PRIMARY KEY (`id`),
  KEY `FK_zone_logs_zone` (`zone_id`),
  KEY `FK_zone_logs_boiler_logs` (`boiler_log_id`),
  CONSTRAINT `FK_zone_logs_boiler_logs` FOREIGN KEY (`boiler_log_id`) REFERENCES `boiler_logs` (`id`),
  CONSTRAINT `FK_zone_logs_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

-- Dumping data for table pihome.zone_logs: ~0 rows (approximately)
/*!40000 ALTER TABLE `zone_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `zone_logs` ENABLE KEYS */;

-- Dumping structure for table pihome.zone_sensor
DROP TABLE IF EXISTS `zone_sensors`;
CREATE TABLE IF NOT EXISTS `zone_sensors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `zone_id` int(11),
  `max_c` tinyint(4),
  `hysteresis_time` tinyint(4),
  `sp_deadband` float NOT NULL,
  `sensor_id` int(11),
  `sensor_child_id` int(11),
  PRIMARY KEY (`id`),
  KEY `FK_zone_sensors_nodes` (`sensor_id`),
  KEY `FK_zone_sensors_zone` (`zone_id`),
  CONSTRAINT `FK_zone_sensors_nodes` FOREIGN KEY (`sensor_id`) REFERENCES `nodes` (`id`),
  CONSTRAINT `FK_zone_sensors_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- Dumping structure for table pihome.zone_type
DROP TABLE IF EXISTS `zone_type`;
CREATE TABLE IF NOT EXISTS `zone_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `type` char(50) COLLATE utf8_bin,
  `category` int(11),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
