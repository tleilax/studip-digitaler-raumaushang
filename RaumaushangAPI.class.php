<?php
require_once __DIR__ . '/bootstrap.php';

/**
 * RaumaushangAPI
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @version 2.0
 */
class RaumaushangAPI extends StudIPPlugin implements
    \JsonApi\Contracts\JsonApiPlugin
{
    public function registerAuthenticatedRoutes(\Slim\Routing\RouteCollectorProxy $group)
    {
        $group->group('/raumaushang', function (\Slim\Routing\RouteCollectorProxy $group) {
            $group->get('/current-view/{id}', \Raumaushang\JSONAPI\Routes\CurrentViewSchedule::class);
            $group->get('/room-view/{id}', \Raumaushang\JSONAPI\Routes\RoomViewSchedule::class);
        });
    }

    public function registerUnauthenticatedRoutes(\Slim\Routing\RouteCollectorProxy $group)
    {
    }

    public function registerSchemas(): array
    {
        return [];
    }
}
