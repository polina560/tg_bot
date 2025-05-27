<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%dialog_state}}`.
 */
class m250527_131110_create_dialog_state_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp()
    {
        $this->createTable('{{%dialog_state}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'chat_id' => $this->integer()->comment('ID Диалога'),
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
