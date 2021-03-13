<?php
class CreateResourceProperties extends Migration
{
    const PROPERTY_NAME = 'Raumaushang: Ganze Woche anzeigen';

    public function description()
    {
        return 'Creates neccessary resources properties and links them';
    }

    public function up()
    {
        // Get or create property
        $query = "SELECT `property_id`
                  FROM `resource_property_definitions`
                  WHERE `name` = :name";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':name', self::PROPERTY_NAME);
        $statement->execute();
        $property_id = $statement->fetchColumn();

        if (!$property_id) {
            $property_id = md5(uniqid('resource-property', true));

            $query = "INSERT INTO `resource_property_definitions`
                        (`property_id`, `name`, `description`, `type`, `options`, `system`, `display_name`, `mkdate`, `chdate`)
                      VALUES (:id, :name, '', :type, :options, 0, :name, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())";
            $statement = DBManager::get()->prepare($query);
            $statement->bindValue(':id', $property_id);
            $statement->bindValue(':name', self::PROPERTY_NAME);
            $statement->bindValue(':type', 'select');
            $statement->bindValue(':options', 'nein;ja');
            $statement->execute();
        }

        // Get all ids for rooms
        $query = "SELECT `id`
                  FROM `resource_categories`
                  WHERE `class_name` = 'Room'";
        $category_ids = DBManager::get()->query($query)->fetchAll(PDO::FETCH_COLUMN);

        // Connect property with room
        $query = "INSERT IGNORE INTO `resource_category_properties`
                    (`category_id`, `property_id`, `requestable`, `system`, `form_text`, `mkdate`, `chdate`)
                  VALUES (:category_id, :property_id, 0, 0, NULL, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':property_id', $property_id);

        foreach ($category_ids as $category_id) {
            $statement->bindValue(':category_id', $category_id);
            $statement->execute();
        }
    }

    public function down()
    {
        // Remove resources property
        $query = "DELETE FROM `resource_property_definitions`
                  WHERE `name` = :name";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':name', self::PROPERTY_NAME);
        $statement->execute();

        // Garbage collect categories/properties connections
        $query = "DELETE FROM `resource_category_properties`
                  WHERE `property_id` NOT IN (
                      SELECT `property_id`
                      FROM `resource_property_definitions`
                  )";
        DBManager::get()->exec($query);

        // Garbage collect objects/properties connections
        $query = "DELETE FROM `resources_objects_properties`
                  WHERE `property_id` NOT IN (
                      SELECT `property_id`
                      FROM `resources_properties`
                  )";
        DBManager::get()->exec($query);
    }
}
