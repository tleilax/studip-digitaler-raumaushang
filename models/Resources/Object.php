<?php
namespace Raumaushang\Resources;

use SimpleORMap;

class Object extends SimpleORMap
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'resources_objects';

        $config['has_one']['parent'] = array(
            'class_name'  => 'Raumaushang\\Resources\\Object',
            'foreign_key' => 'parent_id',
        );

        $config['has_one']['root'] = array(
            'class_name'  => 'Raumaushang\\Resources\\Object',
            'foreign_key' => 'root_id',
        );

        $config['has_one']['category'] = array(
            'class_name'        => 'Raumaushang\\Resources\\Category',
            'foreign_key'       => 'category_id',
            'assoc_foreign_key' => 'category_id',
        );

        parent::configure($config);
    }
}
