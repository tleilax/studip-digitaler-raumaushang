<?php
require_once __DIR__ . '/bootstrap.php';

/**
 * RaumaushangAPI
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @version 2.0
 */
class RaumaushangAPI extends StudIPPlugin implements RESTAPIPlugin
{
    public function getRouteMaps()
    {
        return [
            new Raumaushang\RouteMap(),
        ];
    }
}
