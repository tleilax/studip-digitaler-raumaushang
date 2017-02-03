<?php
/**
 * RaumaushangAPI
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @version 1.0
 */

class RaumaushangAPI extends StudIPPlugin implements APIPlugin
{
    public function describeRoutes()
    {
        return [
            '/raumaushang/query' => _('Ressource finden'),
            '/raumaushang/schedule/:resource_id(/:from(/:until))' => _('Belegung abrufen'),
            '/raumaushang/currentschedule/:resource_id' => _('Tagesaktuelle Belegung abrufen'),
        ];
    }

    public static function before()
    {
        $info = PluginManager::getInstance()->getPlugin(static::class)->getMetadata();

        header('X-Plugin-Version: ' . $info['version']);
        header('X-Raumaushang-Timestamp: ' . date('c'));
    }

    public function routes(&$router)
    {
        $router->get('/raumaushang/query', function () use ($router) {
            $needle = trim(Request::get('q'));

            if (!$needle) {
                $router->halt(400);
            }

            $object = Raumaushang\Resources\Objekt::find($needle);

            if (!$object) {
                $router->halt(404, 'No resource found');
            }

            $router->render($object->id);
        });

        $router->get('/raumaushang/schedule/:resource_id(/:from(/:until))', function ($id, $from = null, $until = null) use ($router) {
            $resource = Raumaushang\Resources\Objekt::find($id);

            if (!$resource) {
                $router->halt(404, 'Resource not found');
            }

            $from = $from ?: strtotime('monday this week  0:00:00');
            if ($resource->show_weekend) {
                $until    = $until ?: strtotime('sunday this week 23:59:59', $from);
                $max_days = 7;
            } else {
                $until = $until ?: strtotime('friday this week 23:59:59', $from);
                $max_days = 5;
            }

            $schedules = Raumaushang\Schedule::getByResource($resource, $from, $until);
            $schedules = Raumaushang\Schedule::decorate($schedules, $from, $max_days);

            // Convert unix timestamps to ISO8601 format
            foreach ($schedules as &$schedule) {
                $schedule['timestamp'] = date('c', $schedule['timestamp']);
                foreach ($schedule['slots'] as &$slot) {
                    $slot['begin'] = date('c', $slot['begin']);
                    $slot['end']   = date('c', $slot['end']);
                }
            }

            header('X-Schedule-Hash: ' . md5(serialize($schedules)));

            $router->render($schedules);
        })->conditions(['resource_id' => '[a-f0-9]{1,32}']);

        $router->get('/raumaushang/currentschedule/:resource_id', function ($id) use ($router) {
            $resource = Raumaushang\Resources\Objekt::find($id);

            if (!$resource) {
                $router->halt(404, 'Resource not found');
            }

            $schedules = Raumaushang\Schedule::findByBuilding($resource);
            foreach ($schedules as $index => $schedule) {
                $array = $schedule->toArray(true);

                // Convert unix timestamps to ISO8601 format
                $array['begin'] = date('c', $array['begin']);
                $array['end']   = date('c', $array['end']);

                $schedules[$index] = $array;
            }

            header('X-Schedule-Hash: ' . md5(serialize($schedules)));

            $router->render($schedules);
        })->conditions(['resource_id' => '[a-f0-9]{1,32}']);
    }
}
