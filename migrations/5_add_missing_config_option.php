<?php
final class AddMissingConfigOption extends Migration
{
    protected function up()
    {
        Config::get()->create('RAUMAUSHANG_AUTH', [
            'value'       => json_encode([]),
            'type'        => 'array',
            'range'       => 'global',
            'section'     => 'global',
            'description' => 'Raumaushänge: Zugangsdaten für die API',

        ]);

        $query = "UPDATE `config`
                  SET `section` = 'Raumaushang'
                  WHERE `field` LIKE 'RAUMAUSHANG_%'";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        Config::get()->delete('RAUMAUSHANG_AUTH');
    }
}
