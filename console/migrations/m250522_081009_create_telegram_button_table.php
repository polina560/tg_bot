<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%telegram_button}}`.
 */
class m250522_081009_create_telegram_button_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp()
    {
        $this->createTable('{{%telegram_button}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(),
            'key' => $this->string(),
            'serial_number' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown()
    {
        $this->dropTable('{{%telegram_button}}');
    }
}
