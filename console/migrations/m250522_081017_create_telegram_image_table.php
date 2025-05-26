<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%telegram_image}}`.
 */
class m250522_081017_create_telegram_image_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp()
    {
        $this->createTable('{{%telegram_image}}', [
            'id' => $this->primaryKey(),
            'image' => $this->string(),
            'key' => $this->string(),
            'serial_number' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown()
    {
        $this->dropTable('{{%telegram_image}}');
    }
}
