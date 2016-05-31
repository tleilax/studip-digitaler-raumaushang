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

        $this->debug = Studip\ENV !== 'production';
    }

    public function index_action()
    {
        $this->resources = Objekt::findByCategory_id(Category::ID_BUILDING, 'ORDER BY name ASC');
    }

    public function building_action($building_id)
    {
        $this->building  = Objekt::find($building_id);
        $this->resources = Objekt::findByParent_id($this->building->id, 'ORDER BY name ASC');
    }

    public function room_action($room_id)
    {
        $this->addOwnLayout();

        $this->id       = $room_id;
        $this->room     = Objekt::find($room_id);

        $properties = [];
        $temp = $this->room->getProperties();
        foreach (['Arbeitsplätze', 'Sitzplätze', 'Beamer', 'Tafel'] as $key) {
            if (array_key_exists($key, $temp)) {
                $properties[$key] = $temp[$key];
            }
        }
        $this->properties = $properties;
    }

    private function addOwnLayout()
    {
        $layout = $this->get_template_factory()->open('layout.php');
        $layout->plugin_scripts = [
            $this->plugin->getPluginURL() . '/assets/mustache-2.2.1' . ($this->debug ? '' : '.min') . '.js',
            $this->plugin->getPluginURL() . '/assets/jquery.event.move.js',
            $this->plugin->getPluginURL() . '/assets/jquery.event.swipe.js',
            $this->plugin->getPluginURL() . '/assets/qrcode' . ($this->debug ? '' : '.min') . '.js',

            $this->plugin->getPluginURL() . '/assets/date.format.js',
            $this->plugin->getPluginURL() . '/assets/countdown.js',
            $this->plugin->getPluginURL() . '/assets/application.js',
        ];
        $layout->plugin_styles = [
            $this->plugin->getPluginURL() . '/assets/style.css',
        ];
        $this->set_layout($layout);
    }

    public function absolute_uri($uri, $parameters = [], $ignore_bound = false)
    {
        $old_base = URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
        $url = URLHelper::getURL($uri, $parameters, $ignore_bound);
        URLHelper::setBaseURL($old_base);

        return $url;
    }
}
