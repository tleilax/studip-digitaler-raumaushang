<?php
use Raumaushang\Resources\Objekt;
use Raumaushang\Schedule;

class SchedulesController extends Raumaushang\Controller
{
    public function before_filter(&$action, &$args)
    {
        if (!method_exists($this, $action . '_action')) {
            $object = $action;
            if (count($args) > 0) {
                $object .= ' ' . array_shift($args);
            }
            $temp = Objekt::find($object);

            if ($temp === null) {
                $this->does_not_understand($action, []);
            } else {
                $action = $temp->level == 1 ? 'building' : 'room';
                array_unshift($args, $temp->id);
            }
        }

        parent::before_filter($action, $args);
    }

    public function index_action()
    {
        $this->resources = Objekt::findBySQL(
            "JOIN resource_categories ON resources.category_id = resource_categories.id
             WHERE class_name = 'Building'
             ORDER BY resources.name ASC"
        );
    }

    public function building_action($building_id)
    {
        $this->building  = $this->requireObject($building_id);
        $this->resources = Objekt::findByParent_id($this->building->id, 'ORDER BY name ASC');
    }

    public function current_action($building_id, $page = 0)
    {
        $max = 7;

        $this->building = $this->requireObject($building_id);
        $this->dates    = Schedule::findByBuilding($this->building);

        $this->max   = $max;
        $this->page  = $page;
        $this->total = count($this->dates);
        $this->dates = array_slice($this->dates, $page * $max, $max);

        $assets = ['assets/current-view.less'];
        if ($this->debug) {
            $assets = array_merge($assets, $this->getJSAssets('current'));
        } else {
            $assets[] = 'assets/current-view-all.min.js';
        }
        $this->addOwnLayout('layout-current-view.php', $assets);
    }

    public function room_action($room_id)
    {
        $this->id   = $room_id;
        $this->room = $this->requireObject($room_id);

        if ($this->room->show_weekend) {
            $this->config['display_days'] = range(1, 7);
        }

        $properties = [];
        foreach (['Arbeitsplätze', 'Sitzplätze', 'Beamer', 'Tafel'] as $key) {
            $state = $this->room->getProperty($key);
            if ($state !== null) {
                $properties[$key] = $state;
            }
        }
        $this->properties = $properties;

        $assets = ['assets/room-view.less'];
        if ($this->debug) {
            $assets = array_merge($assets, $this->getJSAssets('room'));
        } else {
            $assets[] = 'assets/room-view-all.min.js';
        }

        $this->opencast = (bool) PluginEngine::getPlugin('opencast')
                       && $this->room->getProperty('OCCA#Opencast Capture Agent');

        $this->addOwnLayout('layout-room-view.php', $assets);
    }

    private function requireObject($object_id)
    {
        $object = Objekt::find($object_id);

        if (!$object) {
            throw new Trails_UnknownAction("Unknown object with id '{$object_id}'");
        }

        return $object;
    }
}
