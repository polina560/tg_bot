<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%dialog_state}}`.
 */
class m250519_124241_create_dialog_state_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp()
    {
        $this->createTable('{{%dialog_state}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull(),
            'first_name' => $this->string()->comment('Имя пользователя'),
            'last_name' => $this->string()->comment('Фамилия пользователя'),
            'dialog_id' => $this->integer()->notNull()->comment('ID Диалога'),
            'last_msg_id' => $this->integer()->comment('ID последнего сообщения'),
            'last_msg_time' => $this->integer()->comment('Время последнего сообщения'),
            'quantity_reminder_msg' => $this->integer()->defaultValue(0)->comment('Кол-во напоминалок'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown()
    {
        $this->dropTable('{{%dialog_state}}');
    }
}
