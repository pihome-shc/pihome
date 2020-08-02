ALTER TABLE `zone` DROP FOREIGN KEY IF EXISTS `FK_zone_nodes`;
ALTER TABLE `zone` DROP FOREIGN KEY IF EXISTS `FK_zone_type`;
ALTER TABLE `zone` DROP FOREIGN KEY IF EXISTS `FK_zone_boiler`;
ALTER TABLE `zone` DROP COLUMN IF EXISTS `type`;
ALTER TABLE `zone` DROP COLUMN IF EXISTS `model`;
ALTER TABLE `zone` DROP COLUMN IF EXISTS `max_c`;
ALTER TABLE `zone` DROP COLUMN IF EXISTS `max_operation_time`;
ALTER TABLE `zone` DROP COLUMN IF EXISTS `hysteresis_time`;
ALTER TABLE `zone` DROP COLUMN IF EXISTS `sp_deadband`;
ALTER TABLE `zone` DROP COLUMN IF EXISTS `sensor_id`;
ALTER TABLE `zone` DROP COLUMN IF EXISTS `sensor_child_id`;
ALTER TABLE `zone` DROP COLUMN IF EXISTS `boiler_id`;
ALTER TABLE `zone` DROP COLUMN IF EXISTS `gpio_pin`;
ALTER TABLE `zone` CHANGE COLUMN IF EXISTS `zone_status` `zone_state` tinyint(4);
ALTER TABLE `zone` ADD COLUMN IF NOT EXISTS `zone_state` tinyint(4);
ALTER TABLE `zone` ADD COLUMN `type_id` int(11);
ALTER TABLE `zone` ADD CONSTRAINT `FK_zone_type_id` FOREIGN KEY (`type_id`) REFERENCES `zone_type` (`id`);

-- Dumping structure for table pihome.zone_sensor
DROP TABLE IF EXISTS `zone_sensors`;
CREATE TABLE IF NOT EXISTS `zone_sensors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `zone_id` int(11),
  `max_c` tinyint(4),
  `max_operation_time` tinyint(4),
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

-- Zones View version 2
Drop View if exists zone_view;
CREATE VIEW zone_view AS
select zone.status, zone.zone_state, zone.sync, zone.id, zone.index_id, zone.name, ztype.type, ztype.category, zone.graph_it, zs.max_c, zs.max_operation_time, zs.hysteresis_time,
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
