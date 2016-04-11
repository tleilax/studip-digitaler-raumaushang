<?php
use Raumaushang\Resources\Category;
use Raumaushang\Resources\Objekt;
use Raumaushang\Schedule;

class SchedulesController extends PluginController
{
    public function index_action()
    {
        $this->resources = Objekt::findByCategory_id(Category::ID_BUILDING, 'ORDER BY name ASC');
    }

    public function building_action($building_id)
    {
        $this->building  = Objekt::find($building_id);
        $this->resources = Objekt::findByParent_id($building_id, 'ORDER BY name ASC');
        $this->schedule  = Schedule::getByParent($this->building);
    }

    public function room_action($room_id, $begin = null, $end = null)
    {
        $this->room     = Objekt::find($room_id);
        $this->schedule = Schedule::getByResource($this->room, $begin, $end);
    }
}
