<?php
namespace Raumaushang;

use SimpleORMap;

class ResourceObject extends SimpleORMap
{
    const RESOURCE_CATEGORY_ID_BUILDING = '3cbcc99c39476b8e2c8eef5381687461';

    protected static function configure($config = array())
    {
        $config['db_table'] = 'resources_objects';

        $config['has_one']['parent'] = array(
            'class_name'  => 'Raumaushang\\ResourceObject',
            'foreign_key' => 'parent_id',
        );

        $config['has_one']['category'] = array(
            'class_name'        => 'Raumaushang\\ResourceCategory',
            'foreign_key'       => 'category_id',
            'assoc_foreign_key' => 'category_id',
        );

        parent::configure($config);
    }
}
