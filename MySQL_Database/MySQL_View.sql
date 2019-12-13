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
sdtz.zone_id, zone.index_id, zone.name as zone_name, zt.`type`, temperature, holidays_id , coop, ss.nickname as schedule_nickname
from schedule_daily_time_zone sdtz
join schedule_daily_time ss on sdtz.schedule_daily_time_id = ss.id
join schedule_daily_time sstart on sdtz.schedule_daily_time_id = sstart.id
join schedule_daily_time send on sdtz.schedule_daily_time_id = send.id
join schedule_daily_time sWeekDays on sdtz.schedule_daily_time_id = sWeekDays.id
join zone on sdtz.zone_id = zone.id
join zone zt on sdtz.zone_id = zt.id
where sdtz.`purge` = '0' order by zone.index_id;

-- Zone View version 2
Drop View if exists zone_view;
CREATE VIEW zone_view AS
select zone.status, zone.sync, zone.id, zone.index_id, zone.name, zone.type, zone.graph_it, zone.max_c, zone.max_operation_time, zone.hysteresis_time,
zone.sp_deadband, sid.node_id as sensors_id, zone.sensor_child_id,
cid.node_id as controler_id, zone.controler_child_id, zone.gpio_pin,
lasts.last_seen, msv.ms_version, skv.sketch_version
from zone
join nodes sid on zone.sensor_id = sid.id
join nodes cid on zone.controler_id = cid.id
join nodes lasts on zone.sensor_id = lasts.id
join nodes msv on zone.sensor_id = msv.id
join nodes skv on zone.sensor_id = skv.id
where zone.`purge` = '0';

-- Boiler View
Drop View if exists boiler_view;
CREATE VIEW boiler_view AS
select boiler.status, boiler.sync, boiler.`purge`, boiler.fired_status, boiler.name, nodes.node_id, boiler.node_child_id, boiler.hysteresis_time, boiler.max_operation_time, boiler.gpio_pin
from boiler
join nodes on boiler.node_id = nodes.id
where boiler.`purge` = '0';


-- Boost View
Drop View if exists boost_view;
CREATE VIEW boost_view AS
select boost.id, boost.`status`, boost.sync, boost.zone_id, zone_idx.index_id, zone.name, boost.temperature, boost.minute
from boost
join zone on boost.zone_id = zone.id
join zone zone_idx on boost.zone_id = zone_idx.id;


-- Override View
Drop View if exists override_view;
CREATE VIEW override_view AS
select override.`status`, override.sync, override.zone_id, zone_idx.index_id, zone.name, override.time, override.temperature
from override
join zone on override.zone_id = zone.id
join zone zone_idx on override.zone_id = zone_idx.id;

-- Schedule List with zone details view table version 1.x
Drop View if exists schedule_night_climat_zone_view;
CREATE VIEW schedule_night_climat_zone_view AS
select tnct.status as t_status, ncz.status as z_status, ncz.sync, ncz.zone_id, snct.start_time, enct.end_time, ncz.min_temperature, ncz.max_temperature
from schedule_night_climat_zone ncz
join schedule_night_climate_time snct on ncz.schedule_night_climate_id = snct.id
join schedule_night_climate_time enct on ncz.schedule_night_climate_id = enct.id
join schedule_night_climate_time tnct on ncz.schedule_night_climate_id = tnct.id;


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
join zone ztype on zone_logs.zone_id = ztype.id
join boiler_logs blst on zone_logs.boiler_log_id = blst.id
join boiler_logs blet on zone_logs.boiler_log_id = blet.id
join boiler_logs blext on zone_logs.boiler_log_id = blext.id
order by id asc;
