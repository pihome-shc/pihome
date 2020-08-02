# Process to Migrate the Database to Version 1.76

## Changes
The 'zone' table has been split in to two tables, 'zone' and 'zone_sensors'.
The 'zone' table contains zone and contoller identification information.
The 'zone_sensors' table' contains any sensor related information linked to a particular zone. Category 2 type zones will have no entries in the 'zone_sensors' table.
The database views have been ammended to reflect the new table structures.

## Migration Process
Execute the 'migrate_db.php' file found in the directory 'MySQL_Database', this will update the database structure and migrate the existing 'zone' table data, it will also update the views as required.
Update the other files in the normal manner.

## NOTE - DO NOT UPDATE THE DATABASE BY EXECUTING 'update_db.php'.
