<?php
namespace Raumaushang;

use DBManager;
use PDO;

class Schedule
{
    public static function getByResource(Resources\Objekt $resource, $begin = null, $end = null)
    {
        list($begin, $end) = self::getBeginAndEnd($begin, $end);
        
        $query = "SELECT `VID` AS `code`,
                         `NAME` AS `name`,
                         `DOZENT` AS `teachers`,
                         `RAUM` AS `room`,
                         UNIX_TIMESTAMP(`START`) AS `begin`,
                         UNIX_TIMESTAMP(`ENDE`) AS `end`,
                         NOW() BETWEEN `START` AND `ENDE` AS `is_current`,
                         `RID` AS `resource_id`
                  FROM `RoomInformation`
                  WHERE `RID` = :resource_id
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
            $begin = strtotime('today 0:00:00');
        }
        if ($end === null) {
            $end = strtotime('23:59:59', $begin);
        }

        return array($begin, $end);
    }

    protected $name;
    protected $code;
    protected $teachers;
    protected $room;
    protected $begin;
    protected $end;
    protected $is_current;
    protected $resource_id;
    protected $resource;

    public function __construct()
    {
        $this->resource = Resources\Objekt::find($this->resource_id);
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
}
