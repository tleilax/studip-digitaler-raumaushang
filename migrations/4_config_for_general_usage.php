<?php
return new class extends Migration
{
    protected function up()
    {
        Config::get()->create('RAUMAUSHANG_SHOW_FREE_BOOKINGS', [
            'value'       => false,
            'type'        => 'boolean',
            'range'       => 'global',
            'section'     => 'global',
            'description' => 'Raumaushänge: Zeige auch freie Raumbuchungen an',
        ]);

        Config::get()->create('RAUMAUSHANG_HELP_OVERLAY', [
            'value'       => '',
            'type'        => 'string',
            'range'       => 'global',
            'section'     => 'global',
            'description' => 'Raumaushänge: HTML für die Hilfeseite (leer lassen für Default)',
        ]);
    }

    protected function down()
    {
        Config::get()->delete('RAUMAUSHANG_SHOW_FREE_BOOKINGS');
    }
};
