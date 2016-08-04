<?php
require_once __DIR__ . '/bootstrap.php';

/**
 * Raumaushang.class.php
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @version 0.1
 */
class Raumaushang extends StudIPPlugin implements SystemPlugin
{
    public function __construct()
    {
        parent::__construct();

        if (is_object($GLOBALS['user']) && $GLOBALS['user']->perms === 'root') {
            $navigation = new Navigation(_('Raumaushang'), $this->url_for('schedules/index'));
            $navigation->setImage('icons/white/timetable.svg');
            $navigation->setActiveImage('icons/black/timetable.svg');
            Navigation::addItem('/resources/raumaushang', $navigation);
        }
    }

    public function perform($unconsumed_path)
    {
        if (Navigation::hasItem('/resources/raumaushang')) {
            Navigation::activateItem('/resources/raumaushang');
        }

        $this->addLESS('assets/style.less');
        $this->addJS('assets/application.js');

        URLHelper::removeLinkParam('cid');

        parent::perform($unconsumed_path);
    }

    protected function url_for($to, $params = array())
    {
        return PluginEngine::getURL($this, $params, $to);
    }

    public function addJS($asset)
    {
        PageLayout::addScript($this->getPluginURL() . '/' . ltrim($asset, '/'));
    }

    // This is ugly but I can't help it.
    public function addLESS($asset)
    {
        $less_file = $this->getPluginPath() . '/' . ltrim($asset, '/');
        $css_file  = str_replace('.less', '.css', $less_file);

	    $recompile = false;
        if (file_exists($css_file)) {
            $last_compiled = filemtime($css_file);
            if (filemtime($less_file) > $last_compiled) {
                $recompile = true;
            } else {
                $less_files = glob(dirname($less_file) . '/less/*.less');
                foreach ($less_files as $file) {
                    $recompile = $recompile || filemtime($file) > $last_compiled;
                }
            }
        } else {
            $recompile = true;
        }

        if ($recompile) {
            $path   = dirname($less_file);
            $lines  = array_map('trim', file($less_file));
            $parsed = '';
            foreach ($lines as $line) {
                if (preg_match('/^@import "(.*)";/', $line, $match)) {
                    $include_file = $path . '/' . $match[1];
                    $line = trim(file_get_contents($include_file));
                }
                $parsed .= $line . "\n";
            }

            $temp_less = md5(uniqid('less-file', true)) . '.less';
            $temp_file = $path . '/' . $temp_less;
            file_put_contents($temp_file, $parsed);

            parent::addStylesheet(dirname($asset) . '/' . $temp_less);
            PageLayout::removeStylesheet($this->getPluginURL() . '/' . str_replace('.less', '.css', $asset));

            unlink($temp_file);
            rename(str_replace('.less', '.css', $temp_file), $path . '/' . basename($asset, '.less') . '.css');
        }

        PageLayout::addStylesheet($this->getPluginURL() . '/' . ltrim(str_replace('.less', '.css', $asset), '/'));
    }
}
