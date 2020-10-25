/*
   _____    _   _    _
  |  __ \  (_) | |  | |
  | |__) |  _  | |__| |   ___    _ __ ___     ___
  |  ___/  | | |  __  |  / _ \  | |_  \_ \   / _ \
  | |      | | | |  | | | (_) | | | | | | | |  __/
  |_|      |_| |_|  |_|  \___/  |_| |_| |_|  \___|

     S M A R T   H E A T I N G   C O N T R O L

*************************************************************************"
* PiHome is Raspberry Pi based Central Heating Control systems. It runs *"
* from web interface and it comes with ABSOLUTELY NO WARRANTY, to the   *"
* extent permitted by applicable law. I take no responsibility for any  *"
* loss or damage to you or your property.                               *"
* DO NOT MAKE ANY CHANGES TO YOUR HEATING SYSTEM UNTILL UNLESS YOU KNOW *"
* WHAT YOU ARE DOING                                                    *"
*************************************************************************"
*/

-- You Must create following View Talbes in MySQL for PiHome Smart Heating to work

-- Schedule List with zone details view table version 1.x
Drop View if exists schedule_daily_time_zone_view;
CREATE VIEW schedule_daily_time_zone_view AS
select ss.id as time_id, ss.status as time_status, sstart.start, send.end, sWeekDays.WeekDays,
sdtz.sync as tz_sync, sdtz.id as tz_id, sdtz.status as tz_status,
sdtz.zone_id, zone.index_id, zone.name as zone_name, ztype.`type`, ztype.category, temperature, holidays_id , coop, ss.sch_name, sdtz.sunset, sdtz.sunset_offset, zs.max_c
from schedule_daily_time_zone sdtz
join schedule_daily_time ss on sdtz.schedule_daily_time_id = ss.id
join schedule_daily_time sstart on sdtz.schedule_daily_time_id = sstart.id
join schedule_daily_time send on sdtz.schedule_daily_time_id = send.id
join schedule_daily_time sWeekDays on sdtz.schedule_daily_time_id = sWeekDays.id
join zone on sdtz.zone_id = zone.id
join zone zt on sdtz.zone_id = zt.id
LEFT join zone_sensors zs on zone.id = zs.zone_id
join zone_type ztype on zone.type_id = ztype.id
where sdtz.`purge` = '0' order by zone.index_id;

Drop View if exists zone_view;
CREATE VIEW zone_view AS
select zone.status, zone.zone_state, zone.sync, zone.id, zone.index_id, zone.name, ztype.type, ztype.category, zone.graph_it, zs.max_c, max_operation_time, zs.hysteresis_time,
zs.sp_deadband, sid.node_id as sensors_id, zs.sensor_child_id,
ctype.`type` AS controller_type, cid.node_id as controler_id, zc.controler_child_id,
IFNULL(lasts.last_seen, lasts_2.last_seen) as last_seen, IFNULL(msv.ms_version, msv_2.ms_version) as ms_version, IFNULL(skv.sketch_version, skv_2.sketch_version) as sketch_version
from zone
LEFT join zone_sensors zs on zone.id = zs.zone_id 
LEFT join zone_controllers zc on zone.id = zc.zone_id 
join zone_type ztype on zone.type_id = ztype.id
LEFT join nodes sid on zs.sensor_id = sid.id
join nodes ctype on zc.controler_id = ctype.id
join nodes cid on zc.controler_id = cid.id
LEFT join nodes lasts on zs.sensor_id = lasts.id
LEFT join nodes lasts_2 on zc.controler_id = lasts_2.id
LEFT join nodes msv on zs.sensor_id = msv.id
LEFT join nodes msv_2 on zc.controler_id = msv_2.id
LEFT join nodes skv on zs.sensor_id = skv.id
LEFT join nodes skv_2 on zc.controler_id = skv_2.id
where zone.`purge` = '0';

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

-- Boiler View
Drop View if exists boiler_view;
CREATE VIEW boiler_view AS
select boiler.status, boiler.sync, boiler.`purge`, boiler.fired_status, boiler.name, ctype.`type` AS controller_type, nodes.node_id, boiler.node_child_id, boiler.hysteresis_time, boiler.max_operation_time, boiler.overrun
from boiler
join nodes on boiler.node_id = nodes.id
join nodes ctype on boiler.node_id = ctype.id
where boiler.`purge` = '0';

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
ztype.`type`, ztype.category, zone.status as zone_status, nctz.min_temperature, nctz.max_temperature, zs.max_c
from schedule_night_climat_zone nctz
join schedule_night_climate_time snct on nctz.schedule_night_climate_id = snct.id
join schedule_night_climate_time enct on nctz.schedule_night_climate_id = enct.id
join schedule_night_climate_time tnct on nctz.schedule_night_climate_id = tnct.id
join zone on nctz.zone_id = zone.id
join zone zt on nctz.zone_id = zt.id
LEFT join zone_sensors zs on zone.id = zs.zone_id
join zone_type ztype on zone.type_id = ztype.id
where nctz.`purge` = '0' order by zone.index_id;

-- Messages_in View for Graps
Drop View if exists messages_in_view_24h;
CREATE VIEW messages_in_view_24h AS
select node_id, child_id, datetime, payload
from messages_in
where datetime > DATE_SUB( NOW(), INTERVAL 24 HOUR);

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
