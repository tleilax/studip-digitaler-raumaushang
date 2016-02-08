<?php
use Raumaushang\ResourceObject;

class SchedulesController extends PluginController
{
    public function index_action()
    {
        $this->resources = ResourceObject::findByCategory_id(ResourceObject::RESOURCE_CATEGORY_ID_BUILDING, 'ORDER BY name ASC');
    }

    public function building_action($building_id)
    {
        $this->building  = ResourceObject::find($building_id);
        $this->resources = ResourceObject::findByParent_id($building_id, 'ORDER BY name ASC');
    }

    public function schedule_action($room_id, $timestamp = null, $duration = null)
    {
        $this->room = ResourceObject::find($room_id);
    }
}
