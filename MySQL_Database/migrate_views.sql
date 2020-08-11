-- Dumping structure for table pihome.add_on_logs
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

-- Dumping structure for table pihome.http_messages
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

-- Dumping structure for table pihome.zone_current_state
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

-- Dumping structure for table pihome.add_on_zone_logs
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

-- Zones View version 2
Drop View if exists zone_view;
CREATE VIEW zone_view AS
select zone.status, zone.zone_state, zone.sync, zone.id, zone.index_id, zone.name, ztype.type, ztype.category, zone.graph_it, zs.max_c, max_operation_time, zs.hysteresis_time,
zs.sp_deadband, sid.node_id as sensors_id, zs.sensor_child_id,
ctype.`type` AS controller_type, cid.node_id as controler_id, zone.controler_child_id,
IFNULL(lasts.last_seen, lasts_2.last_seen) as last_seen, IFNULL(msv.ms_version, msv_2.ms_version) as ms_version, IFNULL(skv.sketch_version, skv_2.sketch_version) as sketch_version
from zone
LEFT join zone_sensors zs on zone.id = zs.zone_id 
join zone_type ztype on zone.type_id = ztype.id
LEFT join nodes sid on zs.sensor_id = sid.id
join nodes ctype on zone.controler_id = ctype.id
join nodes cid on zone.controler_id = cid.id
LEFT join nodes lasts on zs.sensor_id = lasts.id
LEFT join nodes lasts_2 on zone.controler_id = lasts_2.id
LEFT join nodes msv on zs.sensor_id = msv.id
LEFT join nodes msv_2 on zone.controler_id = msv_2.id
LEFT join nodes skv on zs.sensor_id = skv.id
LEFT join nodes skv_2 on zone.controler_id = skv_2.id
where zone.`purge` = '0';

-- Schedule List with zone details view table version 1.x
Drop View if exists schedule_daily_time_zone_view;
CREATE VIEW schedule_daily_time_zone_view AS
select ss.id as time_id, ss.status as time_status, sstart.start, send.end, sWeekDays.WeekDays,
sdtz.sync as tz_sync, sdtz.id as tz_id, sdtz.status as tz_status,
sdtz.zone_id, zone.index_id, zone.name as zone_name, ztype.`type`, ztype.category, temperature, holidays_id , coop, ss.sch_name
from schedule_daily_time_zone sdtz
join schedule_daily_time ss on sdtz.schedule_daily_time_id = ss.id
join schedule_daily_time sstart on sdtz.schedule_daily_time_id = sstart.id
join schedule_daily_time send on sdtz.schedule_daily_time_id = send.id
join schedule_daily_time sWeekDays on sdtz.schedule_daily_time_id = sWeekDays.id
join zone on sdtz.zone_id = zone.id
join zone zt on sdtz.zone_id = zt.id
join zone_type ztype on zone.type_id = ztype.id
where sdtz.`purge` = '0' order by zone.index_id;

-- Boost View
Drop View if exists boost_view;
CREATE VIEW boost_view AS
select boost.id, boost.`status`, boost.sync, boost.zone_id, zone_idx.index_id, zone_type.category, zone.name, boost.temperature, boost.minute, boost_button_id, boost_button_child_id
from boost
join zone on boost.zone_id = zone.id
join zone zone_idx on boost.zone_id = zone_idx.id
join zone_type on zone_type.id = zone.type_id;

-- Override View
Drop View if exists override_view;
CREATE VIEW override_view AS
select override.`status`, override.sync, override.purge, override.zone_id, zone_idx.index_id, zone_type.category, zone.name, override.time, override.temperature
from override
join zone on override.zone_id = zone.id
join zone zone_idx on override.zone_id = zone_idx.id
join zone_type on zone_type.id = zone.type_id;

-- Schedule List with zone details view table version 1.x
Drop View if exists schedule_night_climat_zone_view;
CREATE VIEW schedule_night_climat_zone_view AS
select tnct.id as time_id, tnct.status as time_status, snct.start_time as start, enct.end_time as end, snct.WeekDays, 
nctz.sync as tz_sync, nctz.id as tz_id, nctz.status as tz_status, nctz.zone_id, zone.index_id, zone.name as zone_name, 
ztype.`type`, ztype.category, zone.status as zone_status, nctz.min_temperature, nctz.max_temperature
from schedule_night_climat_zone nctz
join schedule_night_climate_time snct on nctz.schedule_night_climate_id = snct.id
join schedule_night_climate_time enct on nctz.schedule_night_climate_id = enct.id
join schedule_night_climate_time tnct on nctz.schedule_night_climate_id = tnct.id
join zone on nctz.zone_id = zone.id
join zone zt on nctz.zone_id = zt.id
join zone_type ztype on zone.type_id = ztype.id
where nctz.`purge` = '0' order by zone.index_id;

-- Add-On Logs views
Drop View if exists add_on_log_view;
CREATE VIEW add_on_log_view AS
select add_on_zone_logs.id, add_on_zone_logs.sync, add_on_zone_logs.zone_id, ztype.type,
add_on_zone_logs.add_on_log_id, aost.start_datetime, aoet.stop_datetime, aoext.expected_end_date_time, add_on_zone_logs.status
from add_on_zone_logs
join zone zt on add_on_zone_logs.zone_id = zt.id
join add_on_logs aost on add_on_zone_logs.add_on_log_id = aost.id
join add_on_logs aoet on add_on_zone_logs.add_on_log_id = aoet.id
join add_on_logs aoext on add_on_zone_logs.add_on_log_id = aoext.id
join zone_type ztype on zt.type_id = ztype.id
order by id asc;

-- Zone Logs views
Drop View if exists zone_log_view;
CREATE VIEW zone_log_view AS
select zone_logs.id, zone_logs.sync, zone_logs.zone_id, ztype.type,
zone_logs.boiler_log_id, blst.start_datetime, blet.stop_datetime, blext.expected_end_date_time, zone_logs.status
from zone_logs
join zone zt on zone_logs.zone_id = zt.id
join boiler_logs blst on zone_logs.boiler_log_id = blst.id
join boiler_logs blet on zone_logs.boiler_log_id = blet.id
join boiler_logs blext on zone_logs.boiler_log_id = blext.id
join zone_type ztype on zt.type_id = ztype.id
order by id asc;
