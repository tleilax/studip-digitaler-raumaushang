<?php
class ConfigForQrcodes extends Migration
{
    public function up()
    {
        Config::get()->create('RAUMAUSHANG_SHOW_QRCODES', [
            'value'       => true,
            'type'        => 'boolean',
            'range'       => 'global',
            'section'     => 'global',
            'description' => 'Raumaushänge: Zeige QR-Codes an',
        ]);
    }

    public function down()
    {
        Config::get()->delete('RAUMAUSHANG_SHOW_QRCODES');
    }
}
