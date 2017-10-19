<?php
class MigrateToCoreApi extends Migration
{
    public function up()
    {
        // Convert plugin type to core API plugin type
        $query = "UPDATE `plugins`
                  SET `plugintype` = REPLACE(`plugintype`, 'APIPlugin', 'RESTAPIPlugin')
                  WHERE `pluginclassname` = 'RaumaushangAPI'
                    AND FIND_IN_SET('APIPlugin', `plugintype`) > 0";
        DBManager::get()->exec($query);

        // Activate routes
        $permissions = RESTAPI\ConsumerPermissions::get('global');
        $permissions->set('/raumaushang/query', 'get', true, true);
        $permissions->set('/raumaushang/schedule/:resource_id', 'get', true, true);
        $permissions->set('/raumaushang/schedule/:resource_id/:from', 'get', true, true);
        $permissions->set('/raumaushang/schedule/:resource_id/:from/:until', 'get', true, true);
        $permissions->set('/raumaushang/currentschedule/:resource_id', 'get', true, true);
        $permissions->store();
    }

    public function down()
    {
        // Convert plugin type to Rest.IP plugin type
        $query = "UPDATE `plugins`
                  SET `plugintype` = REPLACE(`plugintype`, 'RESTAPIPlugin', 'APIPlugin')
                  WHERE `pluginclassname` = 'RaumaushangAPI'
                    AND FIND_IN_SET('RESTAPIPlugin', `plugintype`) > 0";
        DBManager::get()->exec($query);
    }
}
