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
        ];
    }

    public function routes(&$router)
    {
        $router->get('/raumaushang/query', function () use ($router) {
            $needle = trim(Request::get('q'));

            if (!$needle) {
                $router->halt(400);
            }

            $temp = Raumaushang\Resources\Objekt::find($needle);

            if (!$temp) {
                $router->halt(404, 'No resource found');
            }

            $router->render($temp->id);
        });

        $router->get('/raumaushang/schedule/:resource_id(/:from(/:until))', function ($id, $from = null, $until = null) use ($router) {
            $resource = Raumaushang\Resources\Objekt::find($id);

            if (!$resource) {
                $router->halt(404, 'Resource not found');
            }

            $schedules = array_map(function (Raumaushang\Schedule $schedule) {
                $array = $schedule->toArray();
                $array['duration'] = ceil(($array['end'] - $array['begin']) / (60 * 60));

                return $array;
            }, Raumaushang\Schedule::getByResource($resource, $from, $until));

            usort($schedules, function ($a, $b) {
                return $a['begin'] - $b['begin'];
            });

            if (Request::int('group_by_weekday')) {
                $temp = array_fill(1, 5, []);
                foreach ($schedules as $schedule) {
                    $wday = strftime('%u', $schedule['begin']);
                    $hour = (int)strftime('%H', $schedule['begin']);

                    $temp[$wday][$hour] = $schedule;
                }
                $schedules = $temp;
            }

            $router->render($schedules);
        })->conditions(['resource_id' => '[a-f0-9]{1,32}']);
    }
}
