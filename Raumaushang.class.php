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
    }
    
    public function perform($unconsumed_path)
    {
        $this->addStylesheet('assets/style.less');
        PageLayout::addScript($this->getPluginURL() . '/assets/application.js');

        URLHelper::removeLinkParam('cid');

        parent::perform($unconsumed_path);
    }

    protected function url_for($to, $params = array())
    {
        return PluginEngine::getURL($this, $params, $to);
    }
}
