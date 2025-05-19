<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%telegram_message_image}}`.
 */
class m250519_131258_create_telegram_message_image_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp()
    {
        $this->createTable('{{%telegram_message_image}}', [
            'id' => $this->primaryKey(),
            'telegram_message_id' => $this->integer()->notNull()->comment('ID сообщения'),
            'serial_number' => $this->integer()->comment('Порядковый номер'),
        ]);
        $this->addForeignKey(
            'fk_telegram_message_image',
            '{{%telegram_message_image}}',
            'telegram_message_id',
            '{{%telegram_message}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown()
    {
        $this->dropTable('{{%telegram_message_image}}');
    }
}
