<?php
namespace Raumaushang;

use DBManager;
use PDO;
use SimpleORMapCollection;
use User;

class Schedule
{
    public static function getByResource(Resources\Objekt $resource, $begin = null, $end = null)
    {
        list($begin, $end) = self::getBeginAndEnd($begin, $end);

        $query = "SELECT `s`.`veranstaltungsnummer` AS `code`,
                         `s`.`name`,
                         GROUP_CONCAT(`su`.`user_id` SEPARATOR ',') AS `teacher_ids`,
                         `ro`.`name` AS `room`,
                         `t`.`date` AS `begin`,
                         `t`.`end_time` AS `end`,
                         `ra`.`resource_id`,
                         UNIX_TIMESTAMP() BETWEEN `t`.`date` AND `t`.`end_time` AS `is_current`,
                         `s`.`seminar_id` AS `course_id`,
                         `s`.`Beschreibung` AS `description`
                  FROM `termine` AS `t`
                  JOIN `resources_assign` AS `ra` ON (`ra`.`assign_user_id` = `t`.`termin_id`)
                  JOIN `seminare` AS `s` ON (`s`.`seminar_id` = `t`.`range_id`)
                  JOIN `resources_objects` AS `ro` ON (`ra`.`resource_id` = `ro`.`resource_id`)
                  JOIN `seminar_user` AS `su` ON (`s`.`seminar_id` = `su`.`seminar_id` AND `su`.`status` = 'dozent')
                  WHERE `ro`.`level` = 2
                    AND `ra`.`resource_id` = :resource_id
                    AND `t`.`date` >= :start
                    AND `t`.`end_time` <= :ende
                  GROUP BY `su`.`seminar_id`, `t`.`date`, `ro`.`name`
                  ORDER BY `begin`, `name`";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':resource_id', $resource->id);
        $statement->bindValue(':start', $begin);
        $statement->bindValue(':ende', $end);
        $statement->execute();
//        var_dump($statement->fetchAll(PDO::FETCH_ASSOC));die;
        return $statement->fetchAll(PDO::FETCH_CLASS, 'Raumaushang\\Schedule');
    }

    public static function getByParent(Resources\Objekt $resource, $begin = null, $end = null)
    {
        list($begin, $end) = self::getBeginAndEnd($begin, $end);

        $query = "SELECT `ri`.`VID` AS `code`,
                         `ri`.`NAME` AS `name`,
                         `ri`.`DOZENT` AS `teachers`,
                         `ri`.`RAUM` AS `room`,
                         UNIX_TIMESTAMP(`ri`.`START`) AS `begin`,
                         UNIX_TIMESTAMP(`ri`.`ENDE`) AS `end`,
                         NOW() BETWEEN `ri`.`START` AND `ENDE` AS `is_current`,
                         `ri`.`RID` AS `resource_id`
                  FROM `RoomInformation` AS `ri`
                  JOIN `resources_objects` AS `ro` ON `ro`.`resource_id` = `ri`.`RID`
                  WHERE `ro`.`parent_id` = :resource_id
                    AND `START` >= FROM_UNIXTIME(:start)
                    AND `ENDE` <= FROM_UNIXTIME(:ende)
                  ORDER BY `begin`, `name`";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':resource_id', $resource->id);
        $statement->bindValue(':start', $begin);
        $statement->bindValue(':ende', $end);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_CLASS, 'Raumaushang\\Schedule');

    }

    protected static function getBeginAndEnd($begin, $end)
    {
        if ($begin === null) {
            $begin = strtotime('monday this week 0:00:00');
        }
        if ($end === null) {
            $end = strtotime('friday this week 23:59:59', $begin);
        }

        return array($begin, $end);
    }

    protected $name;
    protected $code;
    protected $teacher_ids;
    protected $teachers;
    protected $room;
    protected $begin;
    protected $end;
    protected $is_current;
    protected $resource_id;
    protected $resource;
    protected $course_id;
    protected $description;

    public function __construct()
    {
        $this->resource = Resources\Objekt::find($this->resource_id);
        $this->teachers = SimpleORMapCollection::createFromArray(User::findMany($this->teacher_ids));
    }

    public function __isset($offset)
    {
        return property_exists($this, $offset);
    }

    public function __get($offset)
    {
        return $this->$offset;
    }

    public function __set($offset, $value)
    {
        $this->$offset = $value;
    }

    public function __unset($offset)
    {
        $this->$offset = null;
    }

    public function toArray()
    {
        return [
            'id'          => md5(implode('|', [$this->code, $this->name, $this->begin, $this->end])),
            'name'        => $this->name,
            'code'        => $this->code,
            'teachers'    => array_map(function (User $teacher) {
                $array = $teacher->toArray('username vorname nachname title_front title_rear visible');
                foreach (array_keys($GLOBALS['NAME_FORMAT_DESC']) as $type) {
                    $array['name_' . $type] = $teacher->getFullName($type);
                }
                return $array;
            }, $this->teachers->getArrayCopy()),
#            'room'        => $this->room,
            'begin'       => $this->begin,
            'end'         => $this->end,
#            'is_current'  => (bool)$this->is_current,
            'resource'    => $this->resource->toArray(),
            'course_id'   => $this->course_id,
            'modules'     => $this->getModules($this->course_id),
            'description' => trim($this->description) ?: null,
        ];
    }

    protected function getModules($course_id)
    {
        $query = "SELECT DISTINCT CONCAT_WS(' ', `modulschluessel`, `modultitel`)
                  FROM `seminare` AS `s`
                  -- Get semester
                  JOIN `semester_data` AS `sd` ON (`s`.`start_time` BETWEEN `sd`.`beginn` AND `sd`.`ende`)
                  -- Get modules
                  JOIN `seminar_sem_tree` AS `sst` ON (`s`.`seminar_id` = `sst`.`seminar_id`)
                  JOIN `sem_tree` AS `st` ON (`sst`.`sem_tree_id` = `st`.`sem_tree_id` AND `st`.`TYPE` = 5)
                  JOIN `mod_zuordnung` AS `mz` ON (`st`.`sem_tree_id` = `mz`.`sem_tree_id`)
                  JOIN `module` AS `m` ON (`m`.`modul_abst_id` = `mz`.`modul_abst_id` AND `m`.`semester` = `sd`.`semester_id`)
                  JOIN `modul_deskriptor` AS `md` ON (`m`.`desk_id` = `md`.`desk_id`)
                  WHERE `s`.`seminar_id` = :course_id
                  ORDER BY `modulschluessel`, `modultitel`";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':course_id', $course_id);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }
}
