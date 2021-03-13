<?php
namespace Raumaushang\Resources;

use DBManager;
use PDO;
use SimpleORMap;

class Objekt extends \Resource
{
    const PROPERTY_WEEKEND = 'Raumaushang: Ganze Woche anzeigen';

    protected static function configure($config = [])
    {
        $config['additional_fields']['show_weekend'] = [
            'get' => function (Objekt $object, $field) {
                return $object->getProperty(self::PROPERTY_WEEKEND) === 'ja';
            },
            'set' => false,
        ];

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
