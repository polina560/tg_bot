<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%_telegram_message_button}}`.
 */
class m250519_131308_create_telegram_message_button_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp()
    {
        $this->createTable('{{%telegram_message_button}}', [
            'id' => $this->primaryKey(),
            'telegram_message_id' => $this->integer()->notNull()->comment('ID сообщения'),
            'text' => $this->string()->comment('Текст на кнопке'),
            'btn_name' => $this->string()->notNull()->comment('Имя кнопки'),
            'serial_number' => $this->integer()->comment('Порядковый номер')
        ]);
        $this->addForeignKey(
            'fk_telegram_message_btn',
            '{{%telegram_message_button}}',
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
        $this->dropTable('{{%telegram_message_button}}');
    }
}
