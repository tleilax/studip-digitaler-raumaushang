<?php
class ConfigForQrcodes extends Migration
{
    public function up()
    {
        $query = "INSERT IGNORE INTO `config` (
                    `config_id`, `field`, `value`, `is_default`, `type`,
                    `range`, `section`, `description`, `comment`,
                    `mkdate`, `chdate`
                  ) VALUES (
                      MD5(:id), :id, '1', 1, 'boolean',
                      'global', 'global', :description, '',
                      UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  )";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':id', 'RAUMAUSHANG_SHOW_QRCODES');
        $statement->bindValue(':description', 'Raumaushänge: Zeige QR-Codes an');
        $statement->execute();
    }

    public function down()
    {
        $query = "DELETE FROM `config`
                  WHERE `field` = 'RAUMAUSHANG_SHOW_QRCODES'";
        DBManager::get()->exec($query);
    }
}
