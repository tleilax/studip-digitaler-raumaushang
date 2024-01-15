<?php
require_once __DIR__ . '/bootstrap.php';

/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 */
class Raumaushang extends StudIPPlugin implements SystemPlugin
{
    public function __construct()
    {
        parent::__construct();

        if (is_object($GLOBALS['user']) && $GLOBALS['user']->perms === 'root') {
            $navigation = new Navigation(
                _('Raumaushang'),
                PluginEngine::getURL($this, [], 'schedules/index')
            );
            $navigation->setImage(Icon::create('timetable', Icon::ROLE_INFO_ALT));
            $navigation->setActiveImage(Icon::create('timetable', Icon::ROLE_INFO));
            Navigation::addItem('/resources/raumaushang', $navigation);

            $navigation->addSubNavigation('overview', new Navigation(
                _('Übersicht'),
                PluginEngine::getURL($this, [], 'schedules/index')
            ));
            $navigation->addSubNavigation('admin', new Navigation(
                _('Verwaltung'),
                PluginEngine::getURL($this, [], 'admin')
            ));
        }
    }

    public function perform($unconsumed_path)
    {
        $this->addStylesheet('assets/common.css');

        URLHelper::removeLinkParam('cid');

        parent::perform($unconsumed_path);
    }
}
