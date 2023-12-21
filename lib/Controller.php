<?php
namespace Raumaushang;

use Config;
use PluginController;
use URLHelper;
use Studip;

/**
 * @property \Raumaushang $plugin
 */
class Controller extends PluginController
{
    protected $allow_nobody = true;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->config = [
            'display_days'  => range(1, 5),
            'display_slots' => range(8, 21),
            'auth' => Config::get()->RAUMAUSHANG_AUTH ?: ['username' => 'api@raumaushang', 'password' => 'raumaushang'],
        ];

        $this->debug = Studip\ENV !== 'production';
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

    protected function addOwnLayout($name, array $assets = [])
    {
        $js = $css = [];
        foreach ($assets as $asset) {
            $asset = '/' . ltrim($asset, '/');

            $path = parse_url($asset, PHP_URL_PATH);
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            if ($extension === 'js') {
                $js[] = $asset;
            } elseif ($extension === 'scss') {
                $css[] = $this->plugin->addStylesheet($asset);
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

    protected function getJSAssets($index)
    {
        $config = json_decode(file_get_contents(__DIR__ . '/../assets.json'), true);
        if (!isset($config[$index])) {
            throw new Exception('Unknown asset index "' . $index . '"');
        }
        return $config[$index];
    }
}
