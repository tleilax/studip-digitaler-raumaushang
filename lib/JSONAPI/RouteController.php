<?php
namespace Raumaushang\JSONAPI;

use JsonApi\Errors\RecordNotFoundException;
use JsonApi\NonJsonApiController;
use PluginManager;
use Psr\Http\Message\{
    ResponseInterface as Response,
    ServerRequestInterface as Request
};
use Raumaushang\Resources\Objekt;

abstract class RouteController extends NonJsonApiController
{
    public function __invoke(Request $request, Response $response, array $args)
    {
        $response = parent::__invoke($request, $response, $args);

        $info = PluginManager::getInstance()->getPlugin('Raumaushang')->getMetadata();

        return $response
            ->withAddedHeader('X-Plugin-Version', $info['version'])
            ->withAddedHeader('X-Raumaushang-Timestamp', date('c'));
    }

    protected function requireResource(string $resource_id): Objekt
    {
        $resource = Objekt::find($resource_id);

        if (!$resource) {
            throw new RecordNotFoundException('Resource not found');
        }

        return $resource;
    }

    protected function getSchedulesResponse(Response $response, array $schedules): Response
    {
        $response->getBody()->write(json_encode($schedules));
        return $response
            ->withAddedHeader('X-Schedule-Hash', md5(serialize($schedules)))
            ->withAddedHeader('Content-Type', 'application/json');
    }
}
