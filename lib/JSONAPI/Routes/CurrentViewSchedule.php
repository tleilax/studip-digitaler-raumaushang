<?php
namespace Raumaushang\JSONAPI\Routes;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Raumaushang\JSONAPI\RouteController;
use Raumaushang\Schedule;

final class CurrentViewSchedule extends RouteController
{
    public function invoke(Request $request, Response $response, array $args): Response
    {
        $resource = $this->requireResource($args['id']);

        $schedules = Schedule::findByBuilding($resource);
        foreach ($schedules as $index => $schedule) {
            $array = $schedule->toArray(true);

            // Convert unix timestamps to ISO8601 format
            $array['begin'] = date('c', $array['begin']);
            $array['end']   = date('c', $array['end']);

            $schedules[$index] = $array;
        }

        return $this->getSchedulesResponse($response, $schedules);
    }
}
