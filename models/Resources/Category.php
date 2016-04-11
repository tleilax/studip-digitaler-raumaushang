<?php
namespace Raumaushang\Resources;

use SimpleORMap;

class Category extends SimpleORMap
{
    const ID_BUILDING = '3cbcc99c39476b8e2c8eef5381687461';

    protected static function configure($config = array())
    {
        $config['db_table'] = 'resources_categories';

        $config['has_many']['objects'] = array(
            'class_name'        => 'Raumaushang\\Resources\\Objekt',
            'assoc_foreign_key' => 'category_id',
        );

        parent::configure($config);
    }
}
