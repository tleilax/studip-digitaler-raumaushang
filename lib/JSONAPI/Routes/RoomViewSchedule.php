<?php
namespace Raumaushang\JSONAPI\Routes;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Raumaushang\JSONAPI\RouteController;
use Raumaushang\Schedule;

final class RoomViewSchedule extends RouteController
{
    public function invoke(Request $request, Response $response, array $args): Response
    {
        $resource = $this->requireResource($args['id']);

        $from = \Request::int('from', strtotime('monday this week  0:00:00'));
        if ($resource->show_weekend) {
            $until    = \Request::int('until', strtotime('sunday this week 23:59:59', $from));
            $max_days = 7;
        } else {
            $until = \Request::int('until', strtotime('friday this week 23:59:59', $from));
            $max_days = 5;
        }

        $schedules = Schedule::getByResource($resource, $from, $until);
        $schedules = Schedule::decorate($schedules, $from, $max_days);

        // Convert unix timestamps to ISO8601 format
        foreach ($schedules as &$schedule) {
            $schedule['timestamp'] = date('c', $schedule['timestamp']);
            foreach ($schedule['slots'] as &$slot) {
                foreach (['begin', 'end'] as $key) {
                    if (isset($slot[$key])) {
                        $slot[$key] = date('c', $slot[$key]);
                    }
                }
            }
        }

        return $this->getSchedulesResponse($response, $schedules);
    }
}
