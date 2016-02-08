<?php
namespace Raumaushang;

use SimpleORMap;

class ResourceCategory extends SimpleORMap
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'resources_categories';

        parent::configure($config);
    }
}
