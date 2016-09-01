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

    public function current_action($building_id, $page = 0)
    {
        $max = 7;

        $this->building = Objekt::find($building_id);
        $this->dates    = Schedule::findByBuilding($this->building);

        $this->max   = $max;
        $this->page  = $page;
        $this->total = count($this->dates);
        $this->dates = array_slice($this->dates, $page * $max, $max);

        $this->addOwnLayout('layout-current-view.php', [
            'assets/current-view.less',
            'assets/current-view.js',
        ]);
    }

    public function room_action($room_id)
    {
        $manifest = $this->plugin->getMetadata();
        
        $this->addOwnLayout('layout-room-view.php', [
            'assets/room-view.less?v=' . $manifest['version'],
            'assets/room-view.js?v=' . $manifest['version'],
        ]);

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

    private function addOwnLayout($name, array $assets = [])
    {
        $js = $css = [];
        foreach ($assets as $asset) {
            $asset = '/' . ltrim($asset, '/');

            $path = parse_url($asset, PHP_URL_PATH);
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            if ($extension === 'js') {
                $js[] = $asset;
            } elseif ($extension === 'less') {
                $this->plugin->addLESS(substr($asset, 0, strpos($asset, $path) + strlen($path)));
                $css[] = str_replace('.less', '.css', $asset);
            } elseif ($extension === 'css') {
                $css[] = $asset;
            }
        }

        $layout = $this->get_template_factory()->open($name);
        $layout->plugin_scripts = $js;
        $layout->plugin_styles  = $css;
        $layout->plugin_base    = $this->plugin->getPluginURL();
        $layout->plugin_version = $this->plugin->getMetadata()['version'];
        $this->set_layout($layout);
    }

    public function absolute_uri($uri, $parameters = [], $ignore_bound = false)
    {
        $old_base = URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
        $url = URLHelper::getURL($uri, $parameters, $ignore_bound);
        URLHelper::setBaseURL($old_base);

        return $url;
    }

    public function absolute_link($uri, $parameters = [], $ignore_bound = false)
    {
        $old_base = URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
        $url = URLHelper::getLink($uri, $parameters, $ignore_bound);
        URLHelper::setBaseURL($old_base);

        return $url;
    }
}
