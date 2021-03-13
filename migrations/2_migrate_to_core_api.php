<?php
class MigrateToCoreApi extends Migration
{
    public function __construct($verbose = false)
    {
        parent::__construct($verbose);

        require_once __DIR__ . '/../lib/RouteMap.php';
    }

    public function up()
    {
        // Convert plugin type to core API plugin type
        $query = "UPDATE `plugins`
                  SET `plugintype` = REPLACE(`plugintype`, 'APIPlugin', 'RESTAPIPlugin')
                  WHERE `pluginclassname` = 'RaumaushangAPI'
                    AND FIND_IN_SET('APIPlugin', `plugintype`) > 0";
        DBManager::get()->exec($query);

        // Activate routes
        RESTAPI\ConsumerPermissions::get('global')->activateRouteMap(new Raumaushang\RouteMap());
    }

    public function down()
    {
        // Convert plugin type to Rest.IP plugin type
        $query = "UPDATE `plugins`
                  SET `plugintype` = REPLACE(`plugintype`, 'RESTAPIPlugin', 'APIPlugin')
                  WHERE `pluginclassname` = 'RaumaushangAPI'
                    AND FIND_IN_SET('RESTAPIPlugin', `plugintype`) > 0";
        DBManager::get()->exec($query);

        // Activate routes
        RESTAPI\ConsumerPermissions::get('global')->deactivateRouteMap(new Raumaushang\RouteMap());
    }
}
