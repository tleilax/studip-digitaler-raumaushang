<?php
namespace Raumaushang;

use DBManager;
use AssignEventList;
use PDO;
use SimpleORMapCollection;
use User;

class Schedule
{
    const TIMEFRAME = 6;

    public static function getByResource(Resources\Objekt $resource, &$begin = null, &$end = null)
    {
        list($begin, $end) = self::getBeginAndEnd($begin, $end);

        $list = new AssignEventList($begin, $end + 1, $resource->id, '', '', true);
        if ($list->numberOfEvents() === 0) {
            return [];
        }

        $events = [];
        $ids    = [];
        foreach ($list->events as $event) {
            if (date('H', $event->getBegin()) >= 22 || date('H', $event->getEnd()) <= 8) {
                continue;
            }

            $events[] = [
                'id'    => $event->getAssignId(),
                'begin' => $event->getBegin(),
                'end'   => $event->getEnd(),
            ];
            $ids[] = $event->getAssignId();
        }

        $query = "SELECT `ra`.`assign_id`,
                         `s`.`veranstaltungsnummer` AS `code`,
                         IFNULL(`s`.`name`, IF(LENGTH(`ra`.`user_free_name`) > 0, IF(`aum`.`user_id` IS NULL,`ra`.`user_free_name` , CONCAT(`ra`.`user_free_name`, ' - ', CONCAT(`aum`.`Vorname`, ' ', `Nachname`))), IF(`aum`.`user_id` IS NULL, '(unbekannt)', CONCAT(`aum`.`Vorname`, ' ', `Nachname`)))) AS `name`,
                         GROUP_CONCAT(`su`.`user_id`ORDER BY `su`.`position` ASC SEPARATOR ',' ) AS `teacher_ids`,
                         `ro`.`name` AS `room`,
                         `s`.`seminar_id` AS `course_id`,
                         `s`.`Beschreibung` AS `description`,
                         `s`.`Seminar_id` AS `course_id`
                  FROM `resources_assign` AS `ra`
                  LEFT JOIN `termine` AS `t` ON (`ra`.`assign_user_id` = `t`.`termin_id`)
                  LEFT JOIN `seminare` AS `s` ON (`s`.`seminar_id` = `t`.`range_id`)
                  JOIN `resources_objects` AS `ro` ON (`ra`.`resource_id` = `ro`.`resource_id`)
                  LEFT JOIN `seminar_user` AS `su` ON (`s`.`seminar_id` = `su`.`seminar_id` AND `su`.`status` = 'dozent')
                  LEFT JOIN `auth_user_md5` AS `aum` ON (`ra`.`assign_user_id` = `aum`.`user_id`)
                  WHERE `ra`.`assign_id` IN (:assign_ids)
                  GROUP BY IFNULL(`su`.`seminar_id`, `ra`.`assign_id`), `t`.`date`, `ro`.`name`
                  ORDER BY `begin`, `name`";
        $statement = DBManager::get('studip-slave')->prepare($query);
        $statement->bindValue(':assign_ids', $ids);
        $statement->execute();
        $result = $statement->fetchGrouped(PDO::FETCH_ASSOC);

        foreach ($events as $index => $event) {
            $data = array_merge($event, $result[$event['id']]);
            if (!$data['name']) {
                if (!$data['user_free_name']) {
                    $data['name'] = $data['user_fullname'];
                } else {
                    $data['name'] = $data['user_free_name'];
                    if ($data['user_fullname']) {
                        $data['name'] .= ' (' . $data['user_fullname'] . ')';
                    }
                }
                $data['name'] = $data['name'] ?: ('(' . _('unbekannt') . ')');
            }
            $events[$index] = new self($data);
        }
        return $events;
    }

    public static function findByBuilding(Resources\Objekt $building, $start = null, $end = null)
    {
        if ($start === null) {
            $start = time();
        }
        if ($end === null) {
            $end = min(
                strtotime('today 23:59:59', $start),
                strtotime('+' . self::TIMEFRAME . ' hours', $start)
            );
        }

        $query = "SELECT `ra`.`begin`, `ra`.`end`,
                         `s`.`veranstaltungsnummer` AS `code`,
                         IFNULL(`s`.`name`, `ra`.`user_free_name`) AS `name`,
                         GROUP_CONCAT(`su`.`user_id` ORDER BY `su`.`position` ASC SEPARATOR ',' ) AS `teacher_ids`,
                         `ro`.`name` AS `room`,
                         `s`.`seminar_id` AS `course_id`,
                         `s`.`Beschreibung` AS `description`,
                         `s`.`Seminar_id` AS `course_id`
                  FROM `resources_assign` AS `ra`
                  LEFT JOIN `termine` AS `t` ON (`ra`.`assign_user_id` = `t`.`termin_id`)
                  LEFT JOIN `seminare` AS `s` ON (`s`.`seminar_id` = `t`.`range_id`)
                  JOIN `resources_objects` AS `ro` ON (`ra`.`resource_id` = `ro`.`resource_id`)
                  LEFT JOIN `seminar_user` AS `su` ON (`s`.`seminar_id` = `su`.`seminar_id` AND `su`.`status` = 'dozent')
                  WHERE `ro`.`parent_id` = :building_id
                    AND `ra`.`end` >= :begin AND `ra`.`begin` <= :end
                  GROUP BY IFNULL(`su`.`seminar_id`, `ra`.`assign_id`), `t`.`date`, `ro`.`name`
                  ORDER BY `begin`, `name`";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':building_id', $building->id);
        $statement->bindValue(':begin', $start);
        $statement->bindValue(':end', $end);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $index => $row) {
            $result[$index] = new self($row);
        }

        return $result;
    }

    public static function decorate(array $schedules, $from, $max_days = 5)
    {
        $schedules = array_map(function (Schedule $schedule) {
            $array = $schedule->toArray();
            $array['slot'] = (int)date('H', $array['begin']);
            $array['duration'] = ceil(($array['end'] - $array['begin']) / (60 * 60 / 4));
            $array['fraction'] = floor(date('i', $array['begin']) / 15);
            $array['is_holiday'] = false;

            return $array;
        }, $schedules);

        usort($schedules, function ($a, $b) {
            return $a['begin'] - $b['begin'];
        });

        $temp = [];
        for ($i = 1; $i <= $max_days; $i += 1) {
            $temp[$i] = [
                'timestamp' => strtotime('+' . ($i - 1) . ' days 0:00:00', $from),
                'slots'     => [],
            ];
        }
        foreach ($schedules as $schedule) {
            $wday = strftime('%u', $schedule['begin']);
            $hour = (int)strftime('%H', $schedule['begin']);

            $temp[$wday]['slots'][] = $schedule;
        }

        // Check for holiday and fill empty slots per day
        for ($day = 1; $day <= $max_days; $day += 1) {
            $holiday = holiday($temp[$day]['timestamp']);
            if ($holiday !== false && $holiday['col'] == 3) {
                // Step 1: Populate slots array with all slots unset
                $slots = array_fill(8, 14, array_fill_keys([0, 1, 2, 3], false));

                // Step 2: Set the occupied slots
                foreach ($temp[$day]['slots'] as $item) {
                    $slot     = $item['slot'];
                    $fraction = $item['fraction'];

                    for ($i = 1; $i <= $item['duration']; $i += 1) {
                        $slots[$slot][$fraction] = true;

                        $fraction += 1;
                        if ($fraction > 3) {
                            $slot += 1;
                            $fraction = 0;
                        }
                    }
                }

                // Step 3: Aggregate the occupied slots
                $chunks   = [];
                $start    = null;
                $fraction = 0;
                $duration = 0;

                foreach ($slots as $slot => $fractions) {
                    foreach ($fractions as $frac => $value) {
                        if ($value === true && $start !== null) {
                            $chunks[] = compact('start', 'fraction', 'duration');
                            $start = null;
                            $fraction = 0;
                            $duration = 0;
                        } elseif ($value === false && $start === null) {
                            $start = $slot;
                            $fraction = $frac;
                            $duration = 1;
                        } elseif ($value === false) {
                            $duration += 1;
                        }
                    }
                }

                if ($start !== null) {
                    $chunks[] = compact('start', 'fraction', 'duration');
                }

                // Step 4: Write the new blocks back
                foreach (array_reverse($chunks) as $chunk) {
                    array_unshift($temp[$day]['slots'], [
                        'id'         => md5(serialize($holiday) . serialize($chunk)),
                        'slot'       => $chunk['start'],
                        'fraction'   => $chunk['fraction'],
                        'code'       => '',
                        'name'       => $holiday['name'],
                        'duration'   => $chunk['duration'],
                        'teachers'   => [],
                        'modules'    => [],
                        'is_holiday' => true,
                    ]);
                }
            }
        }

        return $temp;
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
    protected $course_id;
    protected $description;

    public function __construct(array $data = null)
    {
        if ($data !== null) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }

        $this->teachers = SimpleORMapCollection::createFromArray(User::findMany(explode(',', $this->teacher_ids)));
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

    public function toArray($minimal = false)
    {
        $result = [
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
            'begin'       => $this->begin,
            'end'         => $this->end,
            'course_id'   => $this->course_id,
        ];

        if (!$minimal) {
            $result['modules']     = $this->getModules($this->course_id);
            $result['description'] = trim($this->description) ?: null;
        } else {
            $result['teachers'] = array_map(function ($teacher) {
                return $teacher['nachname'];
            }, $result['teachers']);
            $result['room'] = $this->room;
        }

        return $result;
    }

    protected function getModules($course_id)
    {
        $query = "SELECT DISTINCT CONCAT_WS(' ', `code`, `bezeichnung`)
                  FROM `seminare` AS `s`
                  JOIN `mvv_lvgruppe_seminar` USING (`seminar_id`)
                  JOIN `mvv_lvgruppe` USING (`lvgruppe_id`)
                  JOIN `mvv_lvgruppe_modulteil` USING (`lvgruppe_id`)
                  JOIN `mvv_modulteil` USING (`modulteil_id`)
                  JOIN `mvv_modul` USING (`modul_id`)
                  JOIN `mvv_modul_deskriptor` USING (`modul_id`)
                  WHERE `s`.`seminar_id` = :course_id
                    AND `sprache` = 'de'
                  ORDER BY `code`, `bezeichnung`";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':course_id', $course_id);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }
}
