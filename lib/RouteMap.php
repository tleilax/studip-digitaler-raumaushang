<?php
namespace Raumaushang;

use PluginManager;
use Request;
use RESTAPI\RouteMap as GlobalRouteMap;

/**
 * @condition :resource_id ^[a-f0-9]{1,32}$
 */
class RouteMap extends GlobalRoutemap
{
    public function before()
    {
        $info = PluginManager::getInstance()->getPlugin('Raumaushang')->getMetadata();

        $this->headers([
            'X-Plugin-Version'        => $info['version'],
            'X-Raumaushang-Timestamp' => date('c'),
        ]);
    }

    /**
     * Search for a resource
     *
     * @get /raumaushang/query
     */
    public function searchResource()
    {
        $needle = trim(Request::get('q'));

        if (!$needle) {
            $this->halt(400);
        }

        $object = Resources\Objekt::find($needle);

        if (!$object) {
            $this->notFound('No resource found');
        }

        return $object->id;
    }

    /**
     * Returns the schedule of a given resource for a given time range.
     *
     * @get /raumaushang/schedule/:resource_id
     * @get /raumaushang/schedule/:resource_id/:from
     * @get /raumaushang/schedule/:resource_id/:from/:until
     */
    public function getSchedule($id, $from = null, $until = null)
    {
        $resource = Resources\Objekt::find($id);

        if (!$resource) {
            $this->notFound('Resource not found');
        }

        $from = $from ?: strtotime('monday this week  0:00:00');
        if ($resource->show_weekend) {
            $until    = $until ?: strtotime('sunday this week 23:59:59', $from);
            $max_days = 7;
        } else {
            $until = $until ?: strtotime('friday this week 23:59:59', $from);
            $max_days = 5;
        }

        $schedules = Schedule::getByResource($resource, $from, $until);
        $schedules = Schedule::decorate($schedules, $from, $max_days);

        // Convert unix timestamps to ISO8601 format
        foreach ($schedules as &$schedule) {
            $schedule['timestamp'] = date('c', $schedule['timestamp']);
            foreach ($schedule['slots'] as &$slot) {
                $slot['begin'] = date('c', $slot['begin']);
                $slot['end']   = date('c', $slot['end']);
            }
        }

        $this->headers([
            'X-Schedule-Hash' => md5(serialize($schedules)),
        ]);

        return $schedules;
    }

    /**
     * Returns the schedule of a given resource for the current day.
     *
     * @get /raumaushang/currentschedule/:resource_id
     */
    public function getCurrentSchedule($id)
    {
        $resource = Resources\Objekt::find($id);

        if (!$resource) {
            $this->notFound('Resource not found');
        }

        $schedules = Schedule::findByBuilding($resource);
        foreach ($schedules as $index => $schedule) {
            $array = $schedule->toArray(true);

            // Convert unix timestamps to ISO8601 format
            $array['begin'] = date('c', $array['begin']);
            $array['end']   = date('c', $array['end']);

            $schedules[$index] = $array;
        }

        $this->headers([
            'X-Schedule-Hash' => md5(serialize($schedules)),
        ]);

        return $schedules;
    }
}
