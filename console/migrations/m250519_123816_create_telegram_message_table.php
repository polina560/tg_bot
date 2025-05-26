<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%telegram_message}}`.
 */
class m250519_123816_create_telegram_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp()
    {
        $this->createTable('{{%telegram_message}}', [
            'id' => $this->primaryKey(),
            'type' => $this->integer()->notNull()->comment('Тип состояния'),
            'text' => $this->text()->notNull()->comment('Текст сообщения'),
            'serial_number' => $this->integer()->comment('Порядковый номер'),
            'key' => $this->string()->comment('Команда для вызова'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown()
    {
        $this->dropTable('{{%telegram_message}}');
    }
}
