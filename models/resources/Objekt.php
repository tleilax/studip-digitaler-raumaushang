<?php
namespace Raumaushang\Resources;

use SimpleORMap;

class Objekt extends SimpleORMap
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'resources_objects';

        $config['has_one']['parent'] = array(
            'class_name'  => 'Raumaushang\\Resources\\Objekt',
            'foreign_key' => 'parent_id',
        );

        $config['has_one']['root'] = array(
            'class_name'  => 'Raumaushang\\Resources\\Objekt',
            'foreign_key' => 'root_id',
        );

        $config['has_one']['category'] = array(
            'class_name'        => 'Raumaushang\\Resources\\Category',
            'foreign_key'       => 'category_id',
            'assoc_foreign_key' => 'category_id',
        );

        parent::configure($config);
    }

    public static function find($id)
    {
        $record = parent::find($id);
        if ($record === null && strlen($id) !== 32) {
            $sql_chunk = "LOWER(name) LIKE CONCAT(LOWER(:needle), '%') AND level IN (1,2)
                          ORDER BY LOWER(name) = LOWER(:needle) DESC";
            $record = self::findOneBySQL($sql_chunk, [':needle' => $id]) ?: null;
        }
        return $record;
    }

    public function setValue($field, $value)
    {
        if ($field === 'description' && $value === 'Dieses Objekt wurde neu erstellt. Es wurden noch keine Eigenschaften zugewiesen.') {
            $value = '';
        }
        return parent::setValue($field, $value);
    }
}
