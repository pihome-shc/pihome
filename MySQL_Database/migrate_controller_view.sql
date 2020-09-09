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

