<?php
class ConfigForQrcodes extends Migration
{
    public function up()
    {
        $query = "INSERT IGNORE INTO `config` (
                    `field`, `value`, `type`,
                    `range`, `section`, `description`
                  ) VALUES (
                    :id, '1','boolean',
                      'global', 'global', :description
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
