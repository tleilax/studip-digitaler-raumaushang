<?php
use Raumaushang\Resources\Category;
use Raumaushang\Resources\Objekt;
use Raumaushang\Schedule;

class SchedulesController extends PluginController
{
    protected $allow_nobody = true;

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

        $this->config = [
            'display_days' => [1, 2, 3, 4, 5],
            'auth' => Config::get()->RAUMAUSHANG_AUTH ?: ['username' => 'api@raumaushang', 'password' => 'raumaushang'],
        ];

    }

    public function index_action()
    {
        $this->resources = Objekt::findByCategory_id(Category::ID_BUILDING, 'ORDER BY name ASC');
    }

    public function building_action($building_id)
    {
        $this->building  = Objekt::find($building_id);
        $this->resources = Objekt::findByParent_id($this->building->id, 'ORDER BY name ASC');
        $this->schedule  = Schedule::getByParent($this->building);
    }

    public function room_action($room_id, $begin = null, $end = null)
    {
        $this->addOwnLayout();

        $this->id       = $room_id;
        $this->room     = Objekt::find($room_id);
        $this->schedule = Schedule::getByResource($this->room, $begin, $end);
    }

    private function addOwnLayout()
    {
        $layout = $this->get_template_factory()->open('layout.php');
        $layout->plugin_scripts = [
            $this->plugin->getPluginURL() . '/assets/mustache-2.2.1' . (Studip\ENV === 'production' ? '.min' : '') . '.js',
            $this->plugin->getPluginURL() . '/assets/jquery.event.move.js',
            $this->plugin->getPluginURL() . '/assets/jquery.event.swipe.js',
            $this->plugin->getPluginURL() . '/assets/date.format.js',
            $this->plugin->getPluginURL() . '/assets/countdown.js',
            $this->plugin->getPluginURL() . '/assets/application.js',
        ];
        $layout->plugin_styles = [
            $this->plugin->getPluginURL() . '/assets/style.css',
        ];
        $this->set_layout($layout);
    }
}
