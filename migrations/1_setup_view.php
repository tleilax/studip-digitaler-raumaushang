<?php
class SetupView extends Migration
{
    public function description()
    {
        return 'Creates the view for room information.';
    }

    public function up()
    {
        $query = "CREATE OR REPLACE VIEW `RoomInformation` AS
                  SELECT `s`.`VeranstaltungsNummer` AS `VID`,
                         `s`.`Name` AS `NAME`,
                         GROUP_CONCAT(DISTINCT `au`.`Nachname` SEPARATOR ' / ') AS `DOZENT`,
                         `ro`.`name` AS `RAUM`,
                         FROM_UNIXTIME(`t`.`date`) AS `START`,
                         FROM_UNIXTIME(`t`.`end_time`) AS `ENDE`,
                         `ro`.`resource_id` AS `RID`
                  FROM `termine` AS `t`
                  JOIN `resources_assign` AS `ra` ON (`ra`.`assign_user_id` = `t`.`termin_id`)
                  JOIN `seminare` AS `s` ON (`s`.`Seminar_id` = `t`.`range_id`)
                  JOIN `resources_objects` AS `ro` ON (`ra`.`resource_id` = `ro`.`resource_id`)
                  JOIN `seminar_user` AS `su` ON (`s`.`Seminar_id` = `su`.`Seminar_id` AND `su`.`status` = 'dozent')
                  JOIN `auth_user_md5` AS `au` ON (`su`.`user_id` = `au`.`user_id`)
                  WHERE FROM_UNIXTIME(`t`.`date`) BETWEEN NOW() - INTERVAL 2 DAY AND NOW() + INTERVAL 7 DAY
                    AND `ro`.`level` = 2
                  GROUP BY `su`.`Seminar_id` , `t`.`date` , `ro`.`name`

                  UNION

                  SELECT NULL AS `VID`,
                         `ra`.`user_free_name` AS `NAME`,
                         NULL AS `DOZENT`,
                         `ro`.`name` AS `RAUM`,
                         FROM_UNIXTIME(`ra`.`begin`) AS `START`,
                         FROM_UNIXTIME(`ra`.`end`) AS `ENDE`,
                         `ro`.`resource_id` AS `RID`
                  FROM `resources_assign` AS `ra`
                  JOIN `resources_objects` `ro` ON (`ra`.`resource_id` = `ro`.`resource_id`)
                  WHERE FROM_UNIXTIME(`ra`.`begin`) BETWEEN NOW() - INTERVAL 2 DAY AND NOW() + INTERVAL 7 DAY
                    AND `ro`.`level` = 2
                    AND `ra`.`assign_user_id` IS NULL";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "DROP VIEW IF EXISTS `RoomInformation`";
        DBManager::get()->exec($query);
    }
}
